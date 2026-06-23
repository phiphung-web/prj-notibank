CREATE DEFINER=`root`@`%` PROCEDURE `customer_summary_update`()
BEGIN

	UPDATE customer as CU
	 INNER JOIN 
	 (SELECT  TR.customer_phone, count(*) as total_trip,
	        SUM(IF(TR.status  IN ('DONE', 'COMPLETE'),1, 0)) AS total_completed,
          SUM(IF(TR.status  = 'CANCEL',1, 0)) AS total_cancel,
					SUM(IF(TR.status  IN ('DONE', 'COMPLETE'),price_customer, 0)) AS total_paid,
					MAX(Tr.created_on) As lastest_trip
	  from trip as TR 
		WHERE TR.`status` <> 'PENDING'
				  GROUP BY TR.customer_phone) As D1
					
					ON CU.phone = D1.customer_phone
					SET CU.total_trip = D1.total_trip,
					CU.total_complete = D1.total_completed,
					CU.total_cancel = D1.total_cancel,
					CU.total_paid = D1.total_paid,
					CU.lastest_trip = D1.lastest_trip
				  WHERE D1.total_completed > 0;

END