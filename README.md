RaspberryPi-P1-port
===================

Reading the P1 port with a Raspberry Pi

## Raspberry Pi Reader

1. Download the https://github.com/CurlyMoo/MinimalPi files and read the README.md
2. Add the files from the `server` folder in the appropriate folder
3. Change the server, password, username, and database settings in the mysql.py file
4. Format an USB key as ext2/3/4 and connect it to the Raspberry Pi
5. Create a cache folder `mkdir cache` in the root of MinimalPi
6. Create the MinimalPi image

## Connecting the P1 to the Raspberry Pi
Use the following scheme to connect the P1 port to the Raspberry Pi (other methods will work as well, but this is how i did it)<br /><br />
<img src="http://img208.imageshack.us/img208/3122/awlt.jpg" alt="Schematic" title="Schematic" />

## Setting up the database
Use the `tables.sql` to set up the MySQL database

## The client

1. Put the files from the `client` folder on your server
2. Change the server, password, username, and database settings in the `gas.php` and `electricity.php` files
