#!/usr/bin/python
import sys
import os
import time
import serial
import re
import datetime
import MySQLdb as mdb
from subprocess import call

ser = serial.Serial()
ser.baudrate = 9600
ser.bytesize=serial.SEVENBITS
ser.parity=serial.PARITY_EVEN
ser.stopbits=serial.STOPBITS_ONE
ser.xonxoff=0
ser.rtscts=0
ser.timeout=20
ser.port="/dev/ttyAMA0"

newElecticitySerial=''
oldElecticitySerial=''
newElecticityRateUsedOffPeak=0
oldElecticityRateUsedOffPeak=0
newElecticityRateUsedPeak=0
oldElecticityRateUsedPeak=0
newElecticityRateGeneratedOffPeak=0
oldElecticityRateGeneratedOffPeak=0
newElecticityRateGeneratedPeak=0
oldElecticityRateGeneratedPeak=0
newElecticityCurrentRate=0
oldElecticityCurrentRate=0
newEecticityTotalUsed=0
oldElecticityTotalUsed=0
newElecticityTotalGenerated=0
oldElecticityTotalGenerated=0
newElecticityTotalGenerated=0
oldElecticityTotalGenerated=0
newElecticitySwitchPosition=0;
oldElecticitySwitchPosition=0;
newGasSerial=''
oldGasSerial=''
newGasLogDateTime='000000000000'
oldGasLogDateTime='000000000000'
newGasTotal=0
oldGasTotal=0

try:
    ser.open()
except:
    sys.exit ("Fout bij het openen van %s. Programma afgebroken."  % ser.name)

try:
	pid = os.fork();
	if pid > 0:
		exit(0)
except OSError, e:
	exit(1)
	
os.chdir("/")
os.setsid()
os.umask(0)
	
try:
	pid = os.fork()
	if pid > 0:
		exit(0)
except OSError, e:
	exit(1)

now = datetime.datetime.now();

while(now.year == 1970):
	now = datetime.datetime.now();
	time.sleep(1)

while(1):
	p1_teller=0
	lines=[]
	while p1_teller < 20:
		p1_line=''
		try:
			p1_raw = ser.readline()
		except:
			sys.exit ("Seriele poort %s kan niet gelezen worden. Programma afgebroken." % ser.name )
		p1_str=str(p1_raw)
		p1_line=p1_str.strip()
		lines.append(p1_line)
		p1_teller = p1_teller +1

	if p1_teller == 20:
		for i, line in enumerate(lines[:]):
                        if(re.match("[0-9]-[0-9]:96.1.1.*",line)):
                                newElecticitySerial = str(re.search("[0-9]-[0-9]:96.1.1\((.*)\)",line).group(1))
                        if(re.match("[0-9]-[0-9]:1.8.1.*",line)):
                                newElecticityRateUsedOffPeak = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:1.8.1\([0]{1,}(.*)\*kWh\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:1.8.2.*",line)):
                                newElecticityRateUsedPeak = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:1.8.2\([0]{1,}(.*)\*kWh\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:2.8.1.*",line)):
                                newElecticityRateGeneratedOffPeak = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:2.8.1\([0]{1,}(.*)\*kWh\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:2.8.2.*",line)):
                                newElecticityRateGeneratedPeak = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:2.8.2\([0]{1,}(.*)\*kWh\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:96.14.0.*",line)):
                                newElecticityCurrentRate = re.search("[0-9]-[0-9]:96.14.0\([0]{1,}(.*)\)",line).group(1)
                        if(re.match("[0-9]-[0-9]:1.7.0.*",line)):
                                newElecticityTotalUsed = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:1.7.0\([0]{1,}(.*)\*kW\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:2.7.0.*",line)):
                                newElecticityTotalGenerated = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:2.7.0\([0]{1,}(.*)\*kW\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:96.3.10.*",line)):
                                newElecticitySwitchPosition = float("{0:.2f}".format(float(re.search("[0-9]-[0-9]:96.3.10\((.*)\)",line).group(1))))
                        if(re.match("[0-9]-[0-9]:96.1.0.*",line)):
                                newGasSerial = re.search("[0-9]-[0-9]:96.1.0\((.*)\)",line).group(1)
                        if(re.match("[0-9]-[0-9]:24.3.0.*",line)):
                                newGasLogDateTime = re.search("[0-9]-[0-9]:24.3.0\(([0-9]{1,})\).*",line).group(1)
                        if(re.match(".*[0-9]-[0-9]:24.3.0.*",line)):
                                newGasTotal = float(re.search("\([0]{1,}(.*)\)",lines[i+1]).group(1))
		
			try:
				file = open("/cache/queries.sql", "a")
				
				if(newElecticitySerial != oldElecticitySerial):
					oldElecticitySerial = newElecticitySerial;
					file.write("INSERT INTO devices (name, type_id) VALUES ('%s', (SELECT type_id FROM device_types WHERE name = \'electricity\')) ON DUPLICATE KEY UPDATE name = '%s', type_id = (SELECT type_id FROM device_types WHERE name = \'electricity\');\n" % (newElecticitySerial, newElecticitySerial))
				if(newGasSerial != oldGasSerial):
					oldGasSerial = newGasSerial;
					file.write("INSERT INTO devices (name, type_id) VALUES ('%s', (SELECT type_id FROM device_types WHERE name = \'gas\')) ON DUPLICATE KEY UPDATE name = '%s', type_id = (SELECT type_id FROM device_types WHERE name = \'gas\');\n" % (newGasSerial, newGasSerial))
				if(newElecticityRateUsedOffPeak != oldElecticityRateUsedOffPeak):
					oldElecticityRateUsedOffPeak = newElecticityRateUsedOffPeak
					file.write("INSERT INTO consumption (dev_id, rate_id, `usage`, `datetime`, direction) VALUES ((SELECT dev_id FROM devices WHERE name = '%s'), (SELECT rate_id FROM rate_types WHERE name = 'low'), %.2f, '%s', 0);\n" % (newElecticitySerial, newElecticityRateUsedOffPeak, str(time.strftime('%Y-%m-%d %H:%M:%S'))))
				if(newElecticityRateUsedPeak != oldElecticityRateUsedPeak):
					oldElecticityRateUsedPeak = newElecticityRateUsedPeak
					file.write("INSERT INTO consumption (dev_id, rate_id, `usage`, `datetime`, direction) VALUES ((SELECT dev_id FROM devices WHERE name = '%s'), (SELECT rate_id FROM rate_types WHERE name = 'high'), %.2f, '%s', 0);\n" % (newElecticitySerial, newElecticityRateUsedPeak, str(time.strftime('%Y-%m-%d %H:%M:%S'))))
				if(newElecticityRateGeneratedOffPeak != oldElecticityRateGeneratedOffPeak):
					oldElecticityRateGeneratedOffPeak = newElecticityRateGeneratedOffPeak
					file.write("INSERT INTO consumption (dev_id, rate_id, `usage`, `datetime`, direction) VALUES ((SELECT dev_id FROM devices WHERE name = '%s'), (SELECT rate_id FROM rate_types WHERE name = 'low'), %.2f, '%s', 1);\n" % (newElecticitySerial, newElecticityRateGeneratedOffPeak, str(time.strftime('%Y-%m-%d %H:%M:%S'))))
				if(newElecticityRateGeneratedPeak != oldElecticityRateGeneratedPeak):
					oldElecticityRateGeneratedPeak = newElecticityRateGeneratedPeak
					file.write("INSERT INTO consumption (dev_id, rate_id, `usage`, `datetime`, direction) VALUES ((SELECT dev_id FROM devices WHERE name = '%s'), (SELECT rate_id FROM rate_types WHERE name = 'high'), %.2f, '%s'), 1);\n" % (newElecticitySerial, newElecticityRateGeneratedPeak, str(time.strftime('%Y-%m-%d %H:%M:%S'))))
				if(newGasTotal != oldGasTotal):
					oldGasTotal = newGasTotal
					file.write("INSERT INTO consumption (dev_id, rate_id, `usage`, `datetime`, direction) VALUES ((SELECT dev_id FROM devices WHERE name = '%s'), (SELECT rate_id FROM rate_types WHERE name = 'both'), %.3f, '%s', 0);\n" % (newGasSerial, newGasTotal, str(time.strftime('%Y-%m-%d %H:%M:%S'))))

#                                print "-- ENERGIEMETER --"
#                                print ""
#                                print "Serienummer:\t\t"+newElecticitySerial
#                                print ""
#                                print "- Verbruikt"
#                                print "Daltarief:\t\t"+str(newElecticityRateUsedOffPeak)+"kWh"
#                                print "Piektarief:\t\t"+str(newElecticityRateUsedPeak)+"kWh"
#                                print ""
#                                print "- Teruggeleverd"
#                                print "Daltarief:\t\t"+str(newElecticityRateGeneratedOffPeak)+"kWh"
#                                print "Piektarief:\t\t"+str(newElecticityRateGeneratedPeak)+"kWh"
#                                print ""
#                                print "Huidige tarief:\t\t"+str(newElecticityCurrentRate)
#                                print ""
#                                print "Totaal verbruikt:\t"+str(newElecticityTotalUsed)
#                                print "Totaal teruggeleverd:\t"+str(newElecticityTotalGenerated)
#                                print ""
#                                print "Stand schakelaar:\t"+newElecticitySwitchPosition
#                                print ""
#                                print "-- GASMETER --"
#                                print ""
#                                print "Serienummer:\t\t"+newGasSerial
#                                print "Log datum/tijd:\t\t"+newGasLogDateTime
#                                print "Totaal:\t\t\t"+str(newGasTotal)+"m3"

                        except:
                                print "Fout bij het openen van /cache/queries.sql."

                        finally:
                                file.close();

ser.close();
