#!/usr/bin/python
import MySQLdb as mdb
import sys
import os
import warnings
import re
warnings.filterwarnings("ignore", "Unknown table.*")

password = None;
database = None;
username = None;
host = None;

f = open('/proc/cmdline', 'r');
cmdline = f.read();
f.close();

try:
        reg = re.search(r"mysql.password=([\w]+)", cmdline);
        password = reg.group(1);
except:
        exit(0);
finally:
        pass;

try:
        reg = re.search(r"mysql.username=([\w]+)", cmdline);
        username = reg.group(1);
except:
        exit(0);
finally:
        pass;

try:
        reg = re.search(r"mysql.host=([0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3}.[0-9]{1,3})", cmdline);
        host = reg.group(1);
except:
        exit(0);
finally:
        pass;

try:
        reg = re.search(r"mysql.database=([\w]+)", cmdline);
        database = reg.group(1);
except:
        exit(0);
finally:
        pass;

if username is None or password is None or host is None or database is None:
        exit(0);

try:
        con = None
        con = mdb.connect(host, username, password, database);
except:
        exit(0)
finally:
	pass

try:
        os.rename('/cache/queries.sql','/cache/queries.sql.process')

        lines = open('/cache/queries.sql.process').readlines()
finally:
        a=0

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

try:
        open('/cache/queries.sql', 'a').writelines(lines)
        os.remove('/cache/queries.sql.process')
finally:
        a=0

try:
	cur = con.cursor()
	cur.execute("INSERT INTO electricity_buffer (`hour`, `datetime`, `usage`, `max`, `min`, `rate`) SELECT HOUR(`datetime`) AS `hour`, (unix_timestamp(concat(cast(`datetime` as date),' ',sec_to_time(((time_to_sec(`datetime`) DIV 900) * 900)))) + (2 * 3600)) AS `datetime`, round(((max(`consumption`.`usage`) - min(`consumption`.`usage`)) * 1000),0) AS `usage`, max(`usage`) AS `max`, min(`usage`) AS `min`, `rate_id` AS `rate` FROM `consumption` WHERE ((`rate_id` = 2) OR (`rate_id` = 3)) GROUP BY `rate_id`, DATE(`datetime`), HOUR(`datetime`), FLOOR((MINUTE(`datetime`) / 15)) HAVING ((`usage` > 0) and (`usage` < 1000)) AND (`datetime` > (SELECT max(`datetime`) FROM electricity_buffer) OR (SELECT count(*) FROM electricity_buffer) = 0) ORDER BY DATE(`datetime`), HOUR(`datetime`), FLOOR((minute(`datetime`) / 15))");
	con.commit();
	cur.close();

	cur = con.cursor()
	cur.execute("INSERT INTO electricity (`hour`, `datetime`, `watt`, `prev_hour`, `prev_max`)  SELECT `hour`, `datetime`, if(@lastHour = `hour`, ROUND(((`max` - `min`) + (`min` - @lastMax))*1000), ROUND(((`max` - `min`))*1000)) AS watt, @lastHour := `hour` AS prev_hour, (SELECT @lastMax := `max` FROM electricity_buffer t2 WHERE t2.rate = t1.rate AND t2.datetime = t1.datetime) AS prev_max FROM electricity_buffer t1, (SELECT @lastMax := 0, @lastHour := 0) SQLVars GROUP BY `datetime`, `hour` HAVING (`datetime` > (SELECT max(`datetime`) FROM electricity) OR (SELECT count(*) FROM electricity) = 0)");
	con.commit();
	cur.close();
	
	cur = con.cursor()
	cur.execute("INSERT INTO gas_buffer (`hour`, `datetime`, `usage`, `max`, `min`, `rate`) SELECT HOUR((`datetime` + INTERVAL 1 HOUR)) AS `hour`, (unix_timestamp((DATE_FORMAT(`datetime`, '%Y-%m-%d %H:00:00') + INTERVAL IF((MINUTE(`datetime`) < 30), 0, 1) HOUR)) + (1 * 3600)) AS `datetime`, ROUND((MAX(`usage`) - MIN(`usage`)), 0) AS `usage`,	MAX(`usage`) AS `max`, MIN(`usage`) AS `min`, `rate_id` AS `rate` FROM `consumption` WHERE (`rate_id` = 1) GROUP BY `rate_id`, DATE(`datetime`), HOUR(`datetime`) HAVING (`datetime` > (SELECT max(`datetime`) FROM gas_buffer) OR (SELECT count(*) FROM gas_buffer) = 0) ORDER BY DATE(`datetime`), HOUR(`datetime`)");
	con.commit();
	cur.close();

	cur = con.cursor()
	cur.execute("INSERT INTO gas (`hour`, `datetime`, `m3`, `prev_hour`, `prev_max`) SELECT `hour`, `datetime`, (ROUND((if(@lastMax = 0, 0, `min`-@lastMax )*1000))/1000) AS m3, @lastHour := `hour` AS prev_hour, if(`max` > 0, @lastMax := `max`, @lastMax = @lastMax) AS prev_max FROM gas_buffer t1, (SELECT @lastMax := 0, @lastHour := 0) SQLVars HAVING (`datetime` > (SELECT max(`datetime`) FROM gas) OR (SELECT count(*) FROM gas) = 0)");
	con.commit();
	cur.close();
finally:
	x=1;

if con:
        con.close()

