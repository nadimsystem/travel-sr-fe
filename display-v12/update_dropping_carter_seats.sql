-- Update script to set passenger count to 8 for Dropping and Carter bookings

UPDATE bookings 
SET 
    seatCount = 8, 
    seatCapacity = 8,
    seatNumbers = 'Full Unit',
    passengerType = 'Umum'
WHERE 
    (serviceType = 'Dropping' OR serviceType = 'Carter') 
    AND (seatCount IS NULL OR seatCount < 8 OR passengerType != 'Umum');

-- Optional: Verify the update
-- SELECT id, serviceType, seatCount, seatNumbers FROM bookings WHERE serviceType IN ('Dropping', 'Carter');
