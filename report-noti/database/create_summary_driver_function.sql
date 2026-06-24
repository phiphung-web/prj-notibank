CREATE DEFINER=`root`@`localhost` PROCEDURE `driverSummaryUpdate`()
BEGIN 
	UPDATE driver as DR
	INNER JOIN 
	(
        SELECT  
            D.id, count(*) as total_bided_trip1,
            SUM(IF(B.status  = 'SUCCESS',1, 0)) AS driver_success,
            SUM(IF(B.status  = 'REFUND',1, 0)) AS driver_refund,
            SUM(IF(B.status  = 'SUCCESS',B.price, 0)) AS driver_revenue
        from driver as D 
        INNER JOIN bid as B on D.id = B.driver_id 
        LEFT JOIN pay_transaction as P on D.id = P.driver_id
        GROUP BY D.id) As D1
    ON DR.id = D1.id
    SET DR.total_trip_bid = D1.total_bided_trip1,
        DR.total_complete = D1.driver_success,
        DR.total_cancel = D1.driver_refund,
        DR.total_revenue = D1.driver_revenue;
END