package handlers

import (
	"log"
	"net/http"
	"sutanraya-backend/config"
	"sutanraya-backend/models"

	"github.com/gin-gonic/gin"
)

func AddToQueue(c *gin.Context) {
	var input struct {
		Targets []struct {
			Phone string `json:"phone"`
			Name  string `json:"name"`
		} `json:"targets"`
		Message string `json:"message"`
	}

	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"status": "error", "message": "Invalid request"})
		return
	}

	if len(input.Targets) == 0 || input.Message == "" {
		c.JSON(http.StatusBadRequest, gin.H{"status": "error", "message": "Targets or message empty"})
		return
	}

	// Begin transaction
	tx, err := config.DB.Begin()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"status": "error", "message": "Database error"})
		return
	}

	stmt, err := tx.Prepare("INSERT INTO broadcast_queue (phone, name, message, status, attempts, created_at, updated_at) VALUES (?, ?, ?, 'pending', 0, NOW(), NOW())")
	if err != nil {
		tx.Rollback()
		c.JSON(http.StatusInternalServerError, gin.H{"status": "error", "message": "Database error"})
		return
	}
	defer stmt.Close()

	count := 0
	for _, t := range input.Targets {
		if _, err := stmt.Exec(t.Phone, t.Name, input.Message); err == nil {
			count++
		} else {
			log.Println("Error inserting queue:", err)
		}
	}

	tx.Commit()
	c.JSON(http.StatusOK, gin.H{"status": "success", "count": count})
}

func GetQueueStats(c *gin.Context) {
	stats := make(map[string]int)

	rows, err := config.DB.Query("SELECT status, COUNT(*) as count FROM broadcast_queue GROUP BY status")
	if err == nil {
		defer rows.Close()
		for rows.Next() {
			var status string
			var count int
			rows.Scan(&status, &count)
			stats[status] = count
		}
	}

	var items []models.BroadcastQueue
	rows2, err := config.DB.Query("SELECT id, phone, name, message, status, attempts FROM broadcast_queue ORDER BY FIELD(status, 'processing', 'pending', 'failed', 'sent'), id ASC LIMIT 50")
	if err == nil {
		defer rows2.Close()
		for rows2.Next() {
			var item models.BroadcastQueue
			rows2.Scan(&item.ID, &item.Phone, &item.Name, &item.Message, &item.Status, &item.Attempts)
			items = append(items, item)
		}
	}

	c.JSON(http.StatusOK, gin.H{"stats": stats, "items": items})
}

func GetNextQueueItem(c *gin.Context) {
	// Simple locking via UPDATE usually better than SELECT FOR UPDATE in MySQL for simple queues without heavy concurrency
	// Or START TRANSACTION -> SELECT FOR UPDATE -> UPDATE -> COMMIT
	
	// We'll follow the PHP logic: Transaction to lock row.
	tx, err := config.DB.Begin()
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"status": "error", "message": "DB error"})
		return
	}
	defer tx.Rollback() // Safety rollback if not committed

	var item models.BroadcastQueue
	// Select pending item
	err = tx.QueryRow("SELECT id, phone, name, message FROM broadcast_queue WHERE status = 'pending' ORDER BY id ASC LIMIT 1 FOR UPDATE").Scan(&item.ID, &item.Phone, &item.Name, &item.Message)
	
	if err != nil {
		// Empty queue or error
		c.JSON(http.StatusOK, gin.H{"status": "empty"}) // Not an error per se
		return
	}

	// Mark as processing
	_, err = tx.Exec("UPDATE broadcast_queue SET status = 'processing', updated_at = NOW() WHERE id = ?", item.ID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"status": "error", "message": "Update failed"})
		return
	}

	tx.Commit()
	c.JSON(http.StatusOK, gin.H{"status": "found", "item": item})
}

func UpdateQueueStatus(c *gin.Context) {
	var input struct {
		ID     int    `json:"id"`
		Status string `json:"status"`
	}
	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"status": "error"})
		return
	}

	_, err := config.DB.Exec("UPDATE broadcast_queue SET status = ?, updated_at = NOW() WHERE id = ?", input.Status, input.ID)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"status": "error"})
		return
	}

	c.JSON(http.StatusOK, gin.H{"status": "success"})
}

func GetQueueList(c *gin.Context) {
	rows, err := config.DB.Query("SELECT id, name, phone, message, status FROM broadcast_queue WHERE status = 'pending' ORDER BY id ASC")
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	defer rows.Close()

	var queue []gin.H
	for rows.Next() {
		var id int
		var name, phone, message, status string
		rows.Scan(&id, &name, &phone, &message, &status)
		queue = append(queue, gin.H{
			"id":      id,
			"name":    name,
			"phone":   phone,
			"message": message,
			"status":  status,
		})
	}

	c.JSON(http.StatusOK, gin.H{"queue": queue})
}

func DeleteFromQueue(c *gin.Context) {
	var input struct {
		ID        int  `json:"id"`
		DeleteAll bool `json:"delete_all"`
	}

	if err := c.ShouldBindJSON(&input); err != nil {
		c.JSON(http.StatusBadRequest, gin.H{"error": err.Error()})
		return
	}

	if input.DeleteAll {
		// Delete ALL pending items
		if _, err := config.DB.Exec("DELETE FROM broadcast_queue WHERE status = 'pending'"); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		c.JSON(http.StatusOK, gin.H{"status": "success", "message": "All pending items deleted"})
	} else {
		// Delete Single Item
		if _, err := config.DB.Exec("DELETE FROM broadcast_queue WHERE id = ?", input.ID); err != nil {
			c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
			return
		}
		c.JSON(http.StatusOK, gin.H{"status": "success", "message": "Item deleted"})
	}
}
