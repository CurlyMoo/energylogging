#!/usr/bin/python
import psycopg2 as pdb
import sys
import os
import warnings
import re
warnings.filterwarnings("ignore", "Unknown table.*")

password = 'XXXX';
database = 'XXX';
username = 'XXX'
host = 'XX.XX.XX.XX';

if os.path.exists('/cache/queries.sql.process'):
	print("queries processing file already exists");
	exit(0);

try:
	cs = "dbname=%s user=%s password=%s host=%s port=%s" % (database, username, password, host, 5432)
	con = pdb.connect(cs)
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
                cur.execute(line.replace('`', ''))
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

if con:
        con.close()

