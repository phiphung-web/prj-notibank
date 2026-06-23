CREATE DEFINER=`root`@`localhost` FUNCTION `check_status` ( driver_id INTEGER ) RETURNS VARCHAR ( 10 ) CHARSET utf8 BEGIN
	DECLARE
		number_bid_3 INTEGER;
	DECLARE
		number_paid_3 INTEGER;
	DECLARE
		number_bid_6 INTEGER;
	DECLARE
		number_paid_6 INTEGER;
	DECLARE
		number_count INTEGER;
	SELECT
		count(*) INTO number_bid_3 
	FROM
		bid AS B 
	WHERE
		B.driver_id = driver_id 
		AND Date( B.created_on ) >= DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 3 MONTH;
	SELECT
		count(*) INTO number_paid_3 
	FROM
		pay_transaction AS P 
	WHERE
		P.driver_id = driver_id 
		AND Date( P.created_on ) >= DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 3 MONTH;
	SELECT
		count(*) INTO number_bid_6 
	FROM
		bid AS B 
	WHERE
		B.driver_id = driver_id 
		AND Date( B.created_on ) BETWEEN ( DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 3 MONTH ) 
		AND ( DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 6 MONTH );
	SELECT
		count(*) INTO number_paid_6 
	FROM
		pay_transaction AS P 
	WHERE
		P.driver_id = driver_id 
		AND Date( P.created_on ) BETWEEN ( DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 3 MONTH ) 
		AND ( DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 6 MONTH );
	SELECT
		count(*) INTO number_count 
	FROM
		driver D 
	WHERE
		D.id = driver_id 
		AND Date( D.created_on ) >= DATE_FORMAT( CURRENT_DATE (), '%Y-%m-01' ) - INTERVAL 3 MONTH;
	IF
		number_count > 0 THEN
			RETURN 0;
		
	END IF;
	IF
		( number_bid_3 > 0 OR number_paid_3 > 0 ) THEN
			RETURN 1;
		
		ELSEIF ( number_bid_6 > 0 OR number_paid_6 > 0 ) THEN
		RETURN 2;
		ELSE RETURN 3;
		
	END IF;

END