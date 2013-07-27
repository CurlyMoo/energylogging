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
	STRFTIME("%H", `datetime`) AS hour,
	(STRFTIME("%s",CAST(`datetime` AS date) || ' ' || `datetime`) + (2 * 3600)) AS `datetime`,
	round(((max(`usage`)-min(`usage`)) * 1000),0) AS `watt`,
	max(`usage`) AS `max`,
	min(`usage`) AS `min`,
	rate_id AS rate 
FROM 
	consumption 
WHERE 
	((rate_id = 2) OR (rate_id = 3)) 
GROUP BY
	rate_id,
	DATE(`datetime`),
	STRFTIME("%H", `datetime`),
	CAST((STRFTIME("%m", `datetime`) / 15) AS INTEGER) 
HAVING
	((watt > 0) and (watt < 1000)) 
ORDER BY
	DATE(`datetime`),
	STRFTIME("%H", `datetime`),
	CAST((STRFTIME("%m", `datetime`) / 15) AS INTEGER);
	
CREATE VIEW gas AS 
SELECT 
	HOUR((`datetime` + interval 1 hour)) AS `hour`,
	(unix_timestamp(
		(date_format(`datetime`, '%Y-%m-%d %H:00:00') 
		+ interval if((minute(`datetime`) < 30), 0, 1) hour)) + 
		(1 * 3600)) AS `datetime`, round((max(`usage`) - 
		min(`usage`)), 0) AS `watt`,
	max(`usage`) AS `max`,
	min(`usage`) AS `min`,
	rate_id AS `rate` 
FROM 
	consumption
WHERE
	rate_id = 1
GROUP BY
	rate_id,
	DATE(`datetime`),
	HOUR(`datetime`) 
ORDER BY
	DATE(`datetime`),
	HOUR(`datetime`);