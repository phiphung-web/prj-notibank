CREATE EVENT
IF
  NOT EXISTS summary_report_event ON SCHEDULE EVERY 6 HOUR STARTS CURRENT_TIMESTAMP DO
BEGIN
    
    SET @fromDate = DATE_SUB( CURDATE(), INTERVAL 1 DAY );
  
  SET @toDate = CURDATE();
CALL summaryReport ( @fromDate, @toDate );
END;