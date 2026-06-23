CREATE DEFINER=`root`@`localhost` PROCEDURE `driverUpdateRank`()
BEGIN
	UPDATE driver as DR
	SET  
	DR.total_recharge = (SELECT SUM(P.money) FROM pay_transaction As P WHERE DR.id = P.driver_id),
	DR.status = check_status(DR.id);
END