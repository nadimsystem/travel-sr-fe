package models

type User struct {
	ID       int    `json:"id"`
	Username string `json:"username"`
	Name     string `json:"name"`
	Password string `json:"-"`
}

type Contact struct {
	Phone         string  `json:"phone"`
	Name          string  `json:"name"`
	TotalTrips    int     `json:"totalTrips"`
	TotalSeats    int     `json:"totalSeats"`
	TotalRevenue  float64 `json:"totalRevenue"`
	LastTrip      string  `json:"lastTrip"` // Date string
	LastBookingID int     `json:"lastBookingId"`
	HistoryRoutes string  `json:"historyRoutes"`
	HistoryTypes  string  `json:"historyTypes"`
}

type BookingHistory struct {
	ID          int     `json:"id"`
	Date        string  `json:"date"`
	Time        string  `json:"time"`
	RouteName   string  `json:"routeName"`
	SeatCount   int     `json:"seatCount"`
	TotalPrice  float64 `json:"totalPrice"`
	Status      string  `json:"status"`
	SeatNumbers string  `json:"seatNumbers"`
}

type BroadcastQueue struct {
	ID        int       `json:"id"`
	Phone     string    `json:"phone"`
	Name      string    `json:"name"`
	Message   string    `json:"message"`
	Status    string    `json:"status"`
	Attempts  int       `json:"attempts"` // created_at, updated_at in DB
}
