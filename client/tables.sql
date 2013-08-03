CREATE TABLE IF NOT EXISTS device_types (
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
)  ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1;

INSERT INTO device_types VALUES (DEFAULT, "electricity");
INSERT INTO device_types VALUES (DEFAULT, "gas");

INSERT INTO rate_types VALUES (DEFAULT, "both");
INSERT INTO rate_types VALUES (DEFAULT, "low");
INSERT INTO rate_types VALUES (DEFAULT, "high");

CREATE VIEW electricity AS
SELECT
	HOUR(`datetime`) AS `hour`,
	(unix_timestamp(concat(cast(`datetime` as date),' ',sec_to_time(((time_to_sec(`datetime`) DIV 900) * 900)))) + (2 * 3600)) AS `datetime`,round(((max(`consumption`.`usage`) - min(`consumption`.`usage`)) * 1000),0) AS `usage`,
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
ORDER BY
	DATE(`datetime`),
	HOUR(`datetime`),
	FLOOR((minute(`datetime`) / 15));
	
CREATE VIEW gas AS 
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
ORDER BY 
	DATE(`datetime`),
	HOUR(`datetime`);
