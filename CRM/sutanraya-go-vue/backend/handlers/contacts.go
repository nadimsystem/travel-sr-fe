package handlers

import (
	"log"
	"net/http"
	"sutanraya-backend/config"
	"sutanraya-backend/models"

	"github.com/gin-gonic/gin"
)

func GetContacts(c *gin.Context) {
	// SQL matching api.php get_contacts
	sql := `
		SELECT 
			passengerPhone as phone, 
			SUBSTRING_INDEX(GROUP_CONCAT(passengerName ORDER BY date DESC, time DESC SEPARATOR '|||'), '|||', 1) as name, 
			COUNT(id) as totalTrips, 
			SUM(seatCount) as totalSeats,
			SUM(totalPrice * seatCount) as totalRevenue, 
			MAX(date) as lastTrip,
			MAX(id) as lastBookingId,
			GROUP_CONCAT(DISTINCT routeName SEPARATOR ', ') as historyRoutes,
			GROUP_CONCAT(DISTINCT passengerType SEPARATOR ', ') as historyTypes
		FROM bookings 
		WHERE status != 'Cancelled' AND passengerPhone != '' 
		GROUP BY passengerPhone 
		ORDER BY lastTrip DESC
	`

	rows, err := config.DB.Query(sql)
	if err != nil {
		log.Println("Error querying contacts:", err)
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	defer rows.Close()

	var contacts []models.Contact
	for rows.Next() {
		var c models.Contact
		// Scan matches the SQL order needed. 
		// Note: GROUP_CONCAT returns []byte in Go MySQL driver usually, need to handle string conversion or scan to string works if driver configured?
		// We'll scan to string/interface helpers if needed, but let's try direct scan.
		// Safe way for nullable fields usually needed but here we have aggregates mostly.
		var historyRoutes, historyTypes []uint8 // GROUP_CONCAT returns bytes usually
		
		err := rows.Scan(
			&c.Phone, &c.Name, &c.TotalTrips, &c.TotalSeats, &c.TotalRevenue, 
			&c.LastTrip, &c.LastBookingID, &historyRoutes, &historyTypes,
		)
		if err != nil {
			log.Println("Error scanning contact:", err)
			continue
		}
		c.HistoryRoutes = string(historyRoutes)
		c.HistoryTypes = string(historyTypes)
		contacts = append(contacts, c)
	}

	c.JSON(http.StatusOK, gin.H{"contacts": contacts})
}

func GetCustomerDetail(c *gin.Context) {
	phone := c.Query("phone")
	if phone == "" {
		c.JSON(http.StatusBadRequest, gin.H{"error": "Phone required"})
		return
	}

	// SQL matching api.php get_customer_detail
	sql := `
		SELECT id, date, time, routeName, seatCount, totalPrice, status, seatNumbers 
		FROM bookings 
		WHERE passengerPhone = ? 
		ORDER BY date DESC
	`

	rows, err := config.DB.Query(sql, phone)
	if err != nil {
		c.JSON(http.StatusInternalServerError, gin.H{"error": err.Error()})
		return
	}
	defer rows.Close()

	var history []models.BookingHistory
	for rows.Next() {
		var h models.BookingHistory
		rows.Scan(&h.ID, &h.Date, &h.Time, &h.RouteName, &h.SeatCount, &h.TotalPrice, &h.Status, &h.SeatNumbers)
		history = append(history, h)
	}

	c.JSON(http.StatusOK, gin.H{"history": history})
}
