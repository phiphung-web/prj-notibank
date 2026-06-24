	CREATE DEFINER=`root`@`localhost` PROCEDURE `summaryReport`( IN fromDate Date, toDate Date )
	BEGIN
		
		

		IF( fromDate IS NULL OR fromDate = '0000-00-00' ) THEN
				
				SET fromDate = STR_TO_DATE( '2023-01-01', '%Y-%m-%dd' );
			
		END IF;
		IF( toDate IS NULL OR toDate = '0000-00-00' ) THEN
				
				SET toDate = Date(
				NOW());
			
		END IF;
		DELETE 
		FROM
			summary_report 
		WHERE
			dt_date >= DATE_SUB( CURDATE(), INTERVAL 1 DAY );
		SELECT
			DATE_SUB( CURDATE(), INTERVAL 1 DAY );
		INSERT INTO summary_report (
			dt_date,
			total_booking,
			total_booking_cancel,
			total_booking_waiting,
			total_booking_create,
			total_booking_confirm,
			total_trip,
			total_trip_cancel,
			total_trip_complete,
			total_trip_done,
			total_trip_pending,
			total_trip_create,
			mail_source,
			mail_source_success,
			call_back,
			call_back_success,
			call_source,
			call_source_success,
			facebook_source,
			comeback_source,
			agency_source,
			zalo_oa_source,
			customer_price,
			driver_price,
			revenue
			) (
			SELECT
				Date( A.created_on ) AS dt_date,
				( SELECT count(*) FROM booking WHERE Date( booking.created_on ) = Date( A.created_on ) ) AS total_booking,
				( SELECT count(*) FROM booking WHERE Date( booking.created_on ) = Date( A.created_on ) AND booking.STATUS = 'REJECT' ) AS total_booking_cancel,
				( SELECT count(*) FROM booking WHERE Date( booking.created_on ) = Date( A.created_on ) AND booking.STATUS = 'WAITING' ) AS total_booking_waiting,
				( SELECT count(*) FROM booking WHERE Date( booking.created_on ) = Date( A.created_on ) AND booking.STATUS = 'CREATE' ) AS total_booking_create,
				( SELECT count(*) FROM booking WHERE Date( booking.created_on ) = Date( A.created_on ) AND booking.STATUS = 'CONFIRM' ) AS total_booking_confirm,
				SUM( IF ( A.STATUS <> 'PENDING', 1, 0 ) ) AS total_trip,
				SUM( IF ( A.STATUS = 'CANCEL', 1, 0 ) ) AS total_trip_cancel,
				SUM(
				IF
				( A.STATUS = 'COMPLETE' AND B.STATUS = 'SUCCESS', 1, 0 )) AS total_trip_complete,
				SUM(
				IF
				( A.STATUS = 'DONE' AND B.STATUS = 'SUCCESS', 1, 0 )) AS total_trip_done,
				SUM(
				IF
				( A.STATUS IN ( 'OPEN', 'CREATE' ), 1, 0 )) AS total_trip_create,
				SUM(
				IF
				( A.STATUS = 'PENDING', 1, 0 )) AS total_trip_pending,
				SUM( IF ( C.type = 6 AND A.call_back_id = 0, 1, 0 ) ) AS mail_source,
				SUM( IF ( C.type = 6 AND A.call_back_id = 0 and A.STATUS IN ( 'DONE', 'COMPLETE' ) AND B.STATUS = 'SUCCESS', 1, 0 ) ) AS mail_source_success,
				SUM( IF ( C.type = 6 AND A.call_back_id > 0, 1, 0 ) ) AS call_back,
				SUM( IF ( C.type = 6 AND A.call_back_id > 0 AND A.STATUS IN ( 'DONE', 'COMPLETE' ) AND B.STATUS = 'SUCCESS', 1, 0 ) ) AS call_back_success,
				SUM( IF ( C.type = 7, 1, 0 ) ) AS call_source,
				SUM( IF ( C.type = 7 and A.STATUS IN ( 'DONE', 'COMPLETE' ) AND B.STATUS = 'SUCCESS', 1, 0 ) ) AS call_source_success,
				SUM( IF ( C.type = 2, 1, 0 ) ) AS facebook_source,
				SUM( IF ( C.type = 8, 1, 0 ) ) AS comeback_source,
				SUM( IF ( C.type = 5, 1, 0 ) ) AS agency_source,
				SUM( IF ( C.type = 4, 1, 0 ) ) AS zalo_oa_source,
				SUM(
				IF
				(( A.STATUS IN ( 'DONE', 'COMPLETE' ) AND B.STATUS = 'SUCCESS' ), A.price_customer, 0 )) AS customer_price,
				SUM(
				IF
				(( A.STATUS IN ( 'DONE', 'COMPLETE' ) AND B.STATUS = 'SUCCESS' ), B.price, 0 )) AS driver_price,
				SUM(
				IF
				(( A.STATUS IN ( 'DONE', 'COMPLETE' ) AND B.STATUS = 'SUCCESS' ), ( A.price_customer - B.price ), 0 )) AS revenue 
			FROM
				trip A
				LEFT JOIN ( SELECT * FROM bid GROUP BY trip_id ) AS B ON A.id = B.trip_id
				INNER JOIN source_trip AS C ON A.source = C.id 
			WHERE
				Date( A.created_on ) BETWEEN fromDate 
				AND toDate 
				AND Date( A.created_on ) NOT IN (
				SELECT
					dt_date 
				FROM
					summary_report 
				WHERE
				dt_date < DATE_SUB( CURDATE(), INTERVAL 1 DAY )) 
			GROUP BY
				dt_date 
			ORDER BY
				dt_date ASC 
			);

	END