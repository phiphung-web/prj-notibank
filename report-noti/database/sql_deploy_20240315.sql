

CREATE TABLE `increase_price` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type_of_car` INT,
    `minute_before` INT DEFAULT 0,
    `price_increase` INT DEFAULT 0
);

CREATE TABLE `config_auto_sale` (
    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `type_of_car` INT,
    `minute_before` INT DEFAULT 0,
    `schedule` INT DEFAULT 0,
    `price` INT DEFAULT 0
);

ALTER TABLE `request_call_back`
ADD COLUMN `source_trip` TINYINT NOT NULL DEFAULT 0 AFTER `type_reject`;

ALTER TABLE `car`
ADD COLUMN `car_year` INT NOT NULL DEFAULT 0;

ALTER TABLE `trip`
ADD COLUMN `customer_property` TINYINT NOT NULL DEFAULT 0;

ALTER TABLE `booking`
ADD COLUMN `customer_property` TINYINT NOT NULL DEFAULT 0;

UPDATE trip SET trip.source_trip = 14, trip.customer_property = 3 WHERE trip.source_trip = 9;
UPDATE trip SET trip.source_trip = 14, trip.customer_property = 2 WHERE trip.source_trip = 8;
UPDATE booking SET booking.type = 14, booking.customer_property = 3 WHERE booking.type = 9;
UPDATE booking SET booking.type = 14, booking.customer_property = 2 WHERE booking.type = 8;