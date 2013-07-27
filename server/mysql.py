#!/usr/bin/python
import MySQLdb as mdb
import sys
import os

con = None

con = mdb.connect('X.X.X.X', '*username*', '*password*', '*databae*');

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

if con:    
	con.close()
