-- Query untuk mengubah status semua perjalanan (Trip) yang masih berjalan menjadi selesai (Tiba)
-- Serta mengembalikan status Armada (Fleet), Supir (Driver), dan Penumpang (Bookings) ke status tersedia/standby

-- 1. Update semua Trip yang statusnya 'On Trip' menjadi 'Tiba'
UPDATE trips SET status = 'Tiba' WHERE status = 'On Trip';

-- 2. Update semua Armada yang statusnya 'On Trip' menjadi 'Tersedia'
UPDATE fleet SET status = 'Tersedia' WHERE status = 'On Trip';

-- 3. Update semua Driver yang statusnya 'Jalan' menjadi 'Standby'
UPDATE drivers SET status = 'Standby' WHERE status = 'Jalan';

-- 4. Update semua Booking yang statusnya 'On Trip' menjadi 'Tiba'
UPDATE bookings SET status = 'Tiba' WHERE status = 'On Trip';
