SELECT
    COUNT(trip.id) AS tong,
    SUM(CASE WHEN trip.`status` IN ('DONE', 'COMPLETE') THEN 1 ELSE 0 END) AS thanh_cong,
    SUM(CASE WHEN trip.`status` IN ('CANCEL',  'OPEN',  'CREATE',  'EXPIRE') THEN 1 ELSE 0 END) AS that_bai,
    SUM(CASE WHEN source_trip.type = 6 THEN 1 ELSE 0 END) AS nguon_mail ,
		SUM(CASE WHEN source_trip.type = 7 THEN 1 ELSE 0 END) AS nguon_call ,
		SUM(CASE WHEN source_trip.type = 4 THEN 1 ELSE 0 END) AS nguon_zalo ,
		SUM(CASE WHEN source_trip.type = 2 THEN 1 ELSE 0 END) AS nguon_fb ,
		SUM(CASE WHEN source_trip.type = 5 THEN 1 ELSE 0 END) AS nguon_dai_ly ,
		SUM(CASE WHEN source_trip.type = 8 THEN 1 ELSE 0 END) AS nguon_khach_quay_dau ,
		SUM(CASE WHEN source_trip.type = 9 THEN 1 ELSE 0 END) AS nguon_khach_quay_dau_cskh 
FROM
    trip
    INNER JOIN source_trip ON trip.source = source_trip.id 
WHERE
    trip.`status` NOT IN ('PENDING', 'EXPIRE')
    AND trip.pickup_time BETWEEN '2024-01-01' AND '2024-01-31';
