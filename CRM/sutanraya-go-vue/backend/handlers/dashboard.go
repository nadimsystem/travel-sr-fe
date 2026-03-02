package handlers

import (
	"net/http"
	"sutanraya-backend/config"
	"time"

	"github.com/gin-gonic/gin"
)

func GetDashboardStats(c *gin.Context) {
	// 1. Total Customers
	var totalCustomers int
	config.DB.QueryRow("SELECT COUNT(DISTINCT passengerPhone) FROM bookings WHERE status != 'Cancelled' AND passengerPhone != ''").Scan(&totalCustomers)

	// 2. Total Revenue
	var totalRevenue float64
	config.DB.QueryRow("SELECT COALESCE(SUM(totalPrice * seatCount), 0) FROM bookings WHERE status != 'Cancelled'").Scan(&totalRevenue)

	// 3. New Customers This Month
	thisMonth := time.Now().Format("2006-01")
	var newCustomers int
	config.DB.QueryRow(`
		SELECT COUNT(*) FROM (
			SELECT passengerPhone, MIN(date) as firstTrip 
			FROM bookings 
			WHERE status != 'Cancelled' AND passengerPhone != '' 
			GROUP BY passengerPhone 
			HAVING DATE_FORMAT(firstTrip, '%Y-%m') = ?
		) as new_cust
	`, thisMonth).Scan(&newCustomers)

	// 4. Repeat Rate
	var repeatCustomers int
	config.DB.QueryRow(`
		SELECT COUNT(*) FROM (
			SELECT passengerPhone 
			FROM bookings 
			WHERE status != 'Cancelled' AND passengerPhone != '' 
			GROUP BY passengerPhone 
			HAVING COUNT(id) > 1
		) as repeat_cust
	`).Scan(&repeatCustomers)

	var repeatRate float64
	if totalCustomers > 0 {
		rate := float64(repeatCustomers) / float64(totalCustomers) * 100
		// Manual rounding to 1 decimal place
		repeatRate = float64(int(rate*10+0.5)) / 10
	}

	c.JSON(http.StatusOK, gin.H{
		"totalCustomers": totalCustomers,
		"totalRevenue":   totalRevenue,
		"newCustomers":   newCustomers,
		"repeatRate":     repeatRate,
	})
}
