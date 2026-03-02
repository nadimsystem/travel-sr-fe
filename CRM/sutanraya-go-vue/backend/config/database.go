package config

import (
	"database/sql"
	"log"

	_ "github.com/go-sql-driver/mysql"
)

var DB *sql.DB

func ConnectDatabase() {
	// Configuration from base.php
	// $host = 'localhost';
	// $user = 'root';
	// $pass = '';
	// $db   = 'sutanraya_v11';

	dsn := "root:@tcp(localhost:3306)/sutanraya_v11?charset=utf8mb4&parseTime=True&loc=Local"

	var err error
	DB, err = sql.Open("mysql", dsn)
	if err != nil {
		log.Fatal("Failed to connect to database:", err)
	}

	if err = DB.Ping(); err != nil {
		log.Fatal("Failed to ping database:", err)
	}

	log.Println("Database connected successfully")
}
