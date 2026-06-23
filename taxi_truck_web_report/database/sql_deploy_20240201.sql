ALTER TABLE trip
ADD COLUMN source_trip TINYINT(2) DEFAULT 0;

ALTER TABLE trip_group
ADD COLUMN vehicle_type VARCHAR(255),
ADD COLUMN license_plates VARCHAR(255);

ALTER TABLE driver
ADD COLUMN parent_id BIGINT DEFAULT 0 AFTER id;

ALTER TABLE trip
MODIFY COLUMN round_trip TINYINT DEFAULT 0;

ALTER TABLE booking
MODIFY COLUMN round_trip TINYINT DEFAULT 0;

CREATE TABLE log_request (
    id BIGINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    driver_id BIGINT,
    status INT,
    message VARCHAR(255),
    acceptedOn DATETIME
);

ALTER TABLE driver
ADD COLUMN latitude DOUBLE DEFAULT 0 AFTER parent_id;

ALTER TABLE driver
ADD COLUMN longitude DOUBLE DEFAULT 0 AFTER latitude;

ALTER TABLE bid
ADD COLUMN driver_sub_id BIGINT DEFAULT 0 AFTER driver_id;

ALTER TABLE driver
ADD COLUMN location TEXT AFTER longitude;

CREATE TABLE calculation_formula (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    type_of_car INT,
    time_start TIME,
    time_end TIME,
    schedule INT DEFAULT 0,
    price_closer_than_km INT DEFAULT 0,
    price_over_km INT DEFAULT 0,
    km INT DEFAULT 0,
    surcharge INT DEFAULT 0,
    price_wait INT DEFAULT 0,
    description VARCHAR(255)
);

ALTER TABLE trip
ADD COLUMN is_auto_price BOOLEAN DEFAULT FALSE AFTER buynow;

CREATE TABLE location (
    id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    place_id INT DEFAULT 0,
    latitude DOUBLE NOT NULL,
    longitude DOUBLE NOT NULL,
    display_name TEXT
);

-- Xóa các cột khỏi bảng driver
ALTER TABLE driver
DROP COLUMN email,
DROP COLUMN birthday,
DROP COLUMN address_contact,
DROP COLUMN address_permanent,
DROP COLUMN identity_card,
DROP COLUMN identity_card_created_on,
DROP COLUMN identity_card_created_at,
DROP COLUMN album_id_card,
DROP COLUMN album_driving_license;

-- Xóa các cột khỏi bảng car
ALTER TABLE car
DROP COLUMN album_vehicle_certificate,
DROP COLUMN album_car;

ALTER TABLE summary_report
ADD COLUMN source_trip TEXT;

-- Xóa các cột khỏi bảng summary_report
ALTER TABLE summary_report
DROP COLUMN mail_source,
DROP COLUMN call_source,
DROP COLUMN comeback_source,
DROP COLUMN agency_source,
DROP COLUMN zalo_oa_source,
DROP COLUMN mail_source_success,
DROP COLUMN call_source_success,
DROP COLUMN call_back,
DROP COLUMN call_back_success,
DROP COLUMN facebook_source;

UPDATE trip
SET trip.source_trip = (
	SELECT
		type
	FROM
		source_trip
WHERE
	trip.source = source_trip.id);

ALTER TABLE trip MODIFY vip_enable tinyint DEFAULT 0;
ALTER TABLE trip MODIFY count int DEFAULT 0;
ALTER TABLE bid MODIFY driver_sub_id int DEFAULT 0;
ALTER TABLE car MODIFY seats tinyint DEFAULT 0;

-- Lấy ra driver_sub trùng lặp
SELECT trip_id, COUNT(trip_id) AS count_trip
FROM driver_sub
GROUP BY trip_id
HAVING COUNT(trip_id) > 1;

-- Lấy ra driver_sub không tồn tại trip
SELECT driver_sub.*
FROM driver_sub
WHERE NOT EXISTS (
    SELECT 1
    FROM trip
    WHERE trip.id = driver_sub.trip_id
);

-- Xóa driver_sub có trip không tồn tại
DELETE driver_sub
FROM driver_sub
LEFT JOIN trip ON driver_sub.trip_id = trip.id
WHERE trip.id IS NULL;

ALTER TABLE driver_sub
ADD CONSTRAINT fk_trip_id_driver_sub
FOREIGN KEY (trip_id)
REFERENCES trip(id)
ON DELETE CASCADE
ON UPDATE CASCADE;

ALTER TABLE customer_service
ADD CONSTRAINT fk_trip_id_customer_service
FOREIGN KEY (trip_id)
REFERENCES trip(id)
ON DELETE CASCADE
ON UPDATE CASCADE;
