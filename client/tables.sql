TABLE IF NOT EXISTS device_types (
	type_id tinyint(1) unsigned UNIQUE AUTO_INCREMENT,
	name varchar(11) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS devices (
	dev_id tinyint(1) unsigned UNIQUE AUTO_INCREMENT,
	name char(34) NOT NULL,
	type_id tinyint(1) unsigned NOT NULL REFERENCES device_types (type_id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS rate_types (
	rate_id tinyint(1) unsigned UNIQUE AUTO_INCREMENT,
	name varchar(25) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

CREATE TABLE IF NOT EXISTS consumption (
	log_id bigint unsigned UNIQUE AUTO_INCREMENT,
	dev_id tinyint(1) unsigned NOT NULL REFERENCES devices(dev_id) ON DELETE SET NULL ON UPDATE CASCADE,
	rate_id tinyint(1) unsigned NOT NULL REFERENCES rate_types(rate_id) ON DELETE SET NULL ON UPDATE CASCADE,
	`usage` float(10,3) unsigned NOT NULL,
	`datetime` timestamp NOT NULL,
	direction tinyint(1) unsigned
) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

INSERT INTO device_types VALUES (DEFAULT, "electricity");
INSERT INTO device_types VALUES (DEFAULT, "gas");

INSERT INTO rate_types VALUES (DEFAULT, "both");
INSERT INTO rate_types VALUES (DEFAULT, "low");
INSERT INTO rate_types VALUES (DEFAULT, "high");

CREATE TABLE IF NOT EXISTS `electricity_buffer` (
  `hour` int(2) NOT NULL,
  `datetime` int(11) NOT NULL,
  `usage` double(17,0) NOT NULL,
  `max` float(10,3) unsigned NOT NULL,
  `min` float(10,3) unsigned NOT NULL,
  `rate` tinyint(1) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `electricity` (
  `hour` int(2) DEFAULT NULL,
  `datetime` int(11) DEFAULT NULL,
  `watt` double(17,0) DEFAULT NULL,
  `prev_hour` int(2) DEFAULT NULL,
  `prev_max` float(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

CREATE TABLE IF NOT EXISTS `gas_buffer` (
  `hour` int(2) DEFAULT NULL,
  `datetime` int(11) DEFAULT NULL,
  `usage` double(17,0) DEFAULT NULL,
  `max` float(10,3) unsigned,
  `min` float(10,3) unsigned,
  `rate` tinyint(1) unsigned NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;
	
CREATE TABLE IF NOT EXISTS `gas` (
  `datetime` int(11) DEFAULT NULL,
  `hour` int(2) DEFAULT NULL,
  `m3` double(21,4) DEFAULT NULL,
  `prev_hour` int(2) DEFAULT NULL,
  `prev_max` float(10,3) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

INSERT INTO electricity_buffer (`hour`, `datetime`, `usage`, `max`, `min`, `rate`) 
SELECT
	HOUR(`datetime`) AS `hour`,
	(unix_timestamp(concat(cast(`datetime` as date),' ',sec_to_time(((time_to_sec(`datetime`) DIV 900) * 900)))) + (2 * 3600)) AS `datetime`,
	round(((max(`consumption`.`usage`) - min(`consumption`.`usage`)) * 1000),0) AS `usage`,
	max(`usage`) AS `max`,
	min(`usage`) AS `min`,
	`rate_id` AS `rate` 
FROM
	`consumption` 
WHERE
	((`rate_id` = 2) OR (`rate_id` = 3))
GROUP BY
	`rate_id`,
	DATE(`datetime`),
	HOUR(`datetime`),
	FLOOR((MINUTE(`datetime`) / 15)) 
HAVING
	((`usage` > 0) and (`usage` < 1000))
AND
	(`datetime` > (SELECT max(`datetime`) FROM electricity_buffer) OR (SELECT count(*) FROM electricity_buffer) = 0)
ORDER BY
	DATE(`datetime`),
	HOUR(`datetime`),
	FLOOR((minute(`datetime`) / 15));

INSERT INTO electricity (`hour`, `datetime`, `watt`, `prev_max`, `prev_hour`) 
SELECT 
	`hour`, 
	`datetime`, 
	if(@lastHour = `hour`, 
		ROUND(((`max` - `min`) + (`min` - @lastMax))*1000),
		ROUND(((`max` - `min`))*1000)
	) AS watt,
	@lastHour := `hour` AS prev_hour,
	@lastMax := `max` AS prev_max
FROM 
	electricity_buffer t1,
	(SELECT @lastMax := 0, @lastHour := 0) SQLVars
HAVING
	(`datetime` > (SELECT max(`datetime`) FROM electricity) OR (SELECT count(*) FROM electricity) = 0);
	
INSERT INTO gas_buffer (`hour`, `datetime`, `usage`, `max`, `min`, `rate`)
SELECT 
	HOUR((`datetime` + INTERVAL 1 HOUR)) AS `hour`,
	(unix_timestamp((DATE_FORMAT(`datetime`, '%Y-%m-%d %H:00:00') + INTERVAL IF((MINUTE(`datetime`) < 30), 0, 1) HOUR)) + (1 * 3600)) AS `datetime`, ROUND((MAX(`usage`) - MIN(`usage`)), 0) AS `usage`,
	MAX(`usage`) AS `max`, 
	MIN(`usage`) AS `min`,
	`rate_id` AS `rate` 
FROM
	`consumption` 
WHERE 
	(`rate_id` = 1) 
GROUP BY
	`rate_id`,
	DATE(`datetime`),
	HOUR(`datetime`)
HAVING
	(`datetime` > (SELECT max(`datetime`) FROM gas_buffer) OR (SELECT count(*) FROM gas_buffer) = 0)
ORDER BY 
	DATE(`datetime`),
	HOUR(`datetime`);
	
INSERT INTO gas (`hour`, `datetime`, `m3`, `prev_max`, `prev_hour`) 
SELECT
	`hour`,
	`datetime`,
	(ROUND((if(@lastMax = 0,
		0,
		`min`-@lastMax
	)*1000))/1000) AS m3,
	@lastHour := `hour` AS prev_hour,
	if(`max` > 0, @lastMax := `max`, @lastMax = @lastMax) AS prev_max
FROM 
	gas_buffer t1,
	(SELECT @lastMax := 0, @lastHour := 0) SQLVars
