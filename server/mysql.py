#!/usr/bin/python
import MySQLdb as mdb
import sys
import os

con = None

con = mdb.connect('X.X.X.X', '*username*', '*password*', '*database*');

os.rename('/cache/queries.sql','/cache/queries.sql.process')

lines = open('/cache/queries.sql.process').readlines()

x=0;
for i, line in enumerate(lines[:]):
	try:
		cur = con.cursor()	
		cur.execute(line)
		con.commit()
	finally:
		cur.close()
		#print i
		#print line
		if((i-x)<len(lines)):
			del lines[i-x]
			x+=1;

open('/cache/queries.sql', 'a').writelines(lines)
os.remove('/cache/queries.sql.process')

try:
	cur = con.cursor()
	cur.execute("INSERT INTO electricity_buffer (`hour`, `datetime`, `usage`, `max`, `min`, `rate`) SELECT HOUR(`datetime`) AS `hour`, (unix_timestamp(concat(cast(`datetime` as date),' ',sec_to_time(((time_to_sec(`datetime`) DIV 900) * 900)))) + (2 * 3600)) AS `datetime`, round(((max(`consumption`.`usage`) - min(`consumption`.`usage`)) * 1000),0) AS `usage`, max(`usage`) AS `max`, min(`usage`) AS `min`, `rate_id` AS `rate` FROM `consumption` WHERE ((`rate_id` = 2) OR (`rate_id` = 3)) GROUP BY `rate_id`, DATE(`datetime`), HOUR(`datetime`), FLOOR((MINUTE(`datetime`) / 15)) HAVING ((`usage` > 0) and (`usage` < 1000)) AND `datetime` > (SELECT max(`datetime`) FROM electricity_buffer) ORDER BY DATE(`datetime`), HOUR(`datetime`), FLOOR((minute(`datetime`) / 15))");
	con.commit();
	cur.close();

	cur = con.cursor()
	cur.execute("INSERT INTO electricity (`hour`, `datetime`, `watt`) SELECT `hour`, `datetime`, ROUND(((`max`-`min`)+(`min`-IFNULL((SELECT `max` FROM electricity_buffer WHERE `datetime` < t1.`datetime` AND rate = t1.rate AND MINUTE(FROM_UNIXTIME(t1.`datetime`)) > 0 ORDER BY datetime DESC LIMIT 1), `min`)))*1000) AS watt FROM electricity_buffer t1 HAVING `datetime` > (SELECT max(`datetime`) FROM electricity)");
	con.commit();
	cur.close();
finally:
	x=1;

if con:    
	con.close()
