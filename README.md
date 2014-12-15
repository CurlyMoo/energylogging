===================
### If you like my solutions, then don't hesitate to donate:
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=donate%40pilight%2eorg&lc=NL&item_name=CurlyMoo&no_note=1&no_shipping=1&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donateCC_LG%2egif%3aNonHosted" target="_blank">
<img alt="PayPal" title="PayPal" border="0" src="https://www.paypalobjects.com/nl_NL/NL/i/btn/btn_donateCC_LG.gif" ></a><br />
<a href="http://flattr.com/thing/1106962/" target="_blank">
<img src="http://api.flattr.com/button/flattr-badge-large.png" alt="Flattr this" title="Flattr this" border="0" /></a>
<hr>

## Raspberry Pi P1 Reader

1. Download the latest image from https://sourceforge.net/projects/rpip1reader/files/
2. Write the image to your SD card with e.g. https://sourceforge.net/projects/win32diskimager/
3. Change the settings in the cmdline.txt
4. Put the SD card in your Raspberry Pi and boot
5. (You're adviced to connect a ext3/4 formatted USB drive for caching)

## Connecting the P1 to the Raspberry Pi
Use the following scheme to connect the P1 port to the Raspberry Pi (other methods will work as well, but this is how i did it)<br /><br />
<img src="http://www.pilight.org/smartmeter.png" alt="Schematic" title="Schematic" />

## Setting up the database
Use the `tables.sql` to set up the MySQL database

## The client

1. Put the files from the `client` folder on your server
2. Change the server, password, username, and database settings in the `gas.php` and `electricity.php` files

## Final result

<img src="http://www.pilight.org/p1meter.jpg" alt="Final result" title="Final result" />
