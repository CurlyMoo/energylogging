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
  `watt` double(17,0) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

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
