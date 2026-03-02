package main

import (
	"log"
	"sutanraya-backend/config"
	"sutanraya-backend/handlers"

	"github.com/gin-gonic/gin"
)

func main() {
	// Initialize Database
	config.ConnectDatabase()

	r := gin.Default()

	// CORS Middleware
	r.Use(func(c *gin.Context) {
		c.Writer.Header().Set("Access-Control-Allow-Origin", "http://localhost:5173")
		c.Writer.Header().Set("Access-Control-Allow-Credentials", "true")
		c.Writer.Header().Set("Access-Control-Allow-Headers", "Content-Type, Content-Length, Accept-Encoding, X-CSRF-Token, Authorization, accept, origin, Cache-Control, X-Requested-With")
		c.Writer.Header().Set("Access-Control-Allow-Methods", "POST, OPTIONS, GET, PUT")

		if c.Request.Method == "OPTIONS" {
			c.AbortWithStatus(204)
			return
		}

		c.Next()
	})

	// Routes
	api := r.Group("/api")
	{
		api.GET("/ping", func(c *gin.Context) {
			c.JSON(200, gin.H{
				"message": "pong",
			})
		})

		// Dashboard Stats
		api.GET("/dashboard/stats", handlers.GetDashboardStats)
		api.GET("/analytics", handlers.GetAnalytics)
		
		// Auth
		api.POST("/login", handlers.Login)

		// Contacts
		api.GET("/contacts", handlers.GetContacts)
		api.GET("/customer/detail", handlers.GetCustomerDetail)


		// Broadcast
		api.POST("/queue/add", handlers.AddToQueue)
		api.GET("/queue/stats", handlers.GetQueueStats)
		api.GET("/queue/next", handlers.GetNextQueueItem)
		api.POST("/queue/update", handlers.UpdateQueueStatus)
		api.GET("/queue/list", handlers.GetQueueList)
		api.POST("/queue/delete", handlers.DeleteFromQueue)
	}

	log.Println("Server running on port 8080")
	r.Run(":8080")
}
