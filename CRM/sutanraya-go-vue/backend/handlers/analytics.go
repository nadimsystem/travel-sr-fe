package handlers

import (
	"fmt"
	"net/http"
	"sutanraya-backend/config"
	"time"

	"github.com/gin-gonic/gin"
)

func GetAnalytics(c *gin.Context) {
	analytics := gin.H{
		"champion_revenue": nil,
		"champion_trips":   nil,
		"champion_seats":   nil,
		"demographics":     []gin.H{},
		"growth":           []gin.H{},
	}

	// 1. CHAMPIONS
	// Top Spender
	var crName, crPhone string
	var crTotal float64
	// Use SUBSTRING_INDEX to get the most recent name
	queryRevenue := `
		SELECT 
			SUBSTRING_INDEX(GROUP_CONCAT(passengerName ORDER BY date DESC, time DESC SEPARATOR '|||'), '|||', 1) as name, 
			passengerPhone, 
			SUM(totalPrice * seatCount) as total 
		FROM bookings 
		WHERE status != 'Cancelled' AND passengerName != '' 
		GROUP BY passengerPhone 
		ORDER BY total DESC LIMIT 1
	`
	if err := config.DB.QueryRow(queryRevenue).Scan(&crName, &crPhone, &crTotal); err == nil {
		analytics["champion_revenue"] = gin.H{"name": crName, "phone": crPhone, "total": crTotal}
	}

	// Most Trips
	var ctName, ctPhone string
	var ctTotal int
	queryTrips := `
		SELECT 
			SUBSTRING_INDEX(GROUP_CONCAT(passengerName ORDER BY date DESC, time DESC SEPARATOR '|||'), '|||', 1) as name, 
			passengerPhone, 
			COUNT(id) as total 
		FROM bookings 
		WHERE status != 'Cancelled' AND passengerName != '' 
		GROUP BY passengerPhone 
		ORDER BY total DESC LIMIT 1
	`
	if err := config.DB.QueryRow(queryTrips).Scan(&ctName, &ctPhone, &ctTotal); err == nil {
		analytics["champion_trips"] = gin.H{"name": ctName, "phone": ctPhone, "total": ctTotal}
	}

	// Most Seats
	var csName, csPhone string
	var csTotal int
	querySeats := `
		SELECT 
			SUBSTRING_INDEX(GROUP_CONCAT(passengerName ORDER BY date DESC, time DESC SEPARATOR '|||'), '|||', 1) as name, 
			passengerPhone, 
			SUM(seatCount) as total 
		FROM bookings 
		WHERE status != 'Cancelled' AND passengerName != '' 
		GROUP BY passengerPhone 
		ORDER BY total DESC LIMIT 1
	`
	if err := config.DB.QueryRow(querySeats).Scan(&csName, &csPhone, &csTotal); err == nil {
		analytics["champion_seats"] = gin.H{"name": csName, "phone": csPhone, "total": csTotal}
	}

	// 2. DEMOGRAPHICS (Passenger Type)
	rows, err := config.DB.Query("SELECT passengerType, COUNT(id) as count FROM bookings WHERE status != 'Cancelled' GROUP BY passengerType")
	if err == nil {
		defer rows.Close()
		var demos []gin.H
		for rows.Next() {
			var pType string
			var count int
			rows.Scan(&pType, &count)
			demos = append(demos, gin.H{"passengerType": pType, "count": count})
		}
		analytics["demographics"] = demos
	}

	// 3. MONTHLY GROWTH (New Customers)
	var growth []gin.H
	for i := 5; i >= 0; i-- {
		t := time.Now().AddDate(0, -i, 0)
		month := t.Format("2006-01")
		monthLabel := t.Format("Jan 2006")
		
		var count int
		sql := fmt.Sprintf(`SELECT COUNT(*) FROM (
			SELECT passengerPhone, MIN(date) as firstTrip 
			FROM bookings 
			WHERE status != 'Cancelled' AND passengerPhone != '' 
			GROUP BY passengerPhone 
			HAVING DATE_FORMAT(firstTrip, '%%Y-%%m') = '%s'
		) as new_cust`, month)

		config.DB.QueryRow(sql).Scan(&count)
		growth = append(growth, gin.H{"month": monthLabel, "count": count})
	}
	analytics["growth"] = growth

	c.JSON(http.StatusOK, analytics)
}
