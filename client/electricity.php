<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');

$rHourElec = mysql_query("SELECT * FROM electricity");
$rTS = mysql_query("SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP) as ts");
$aTS = mysql_fetch_assoc($rTS);

$sJson = '[';
while($aHourElec = mysql_fetch_assoc($rHourElec)) {

	$aDate = new DateTime(date("Y-m-d H:i:s", $aHourElec['datetime']-(($aTS['ts']+1)*3600)), new DateTimeZone(date_default_timezone_get()));
	if($aDate->format('I') == 0) {
		$aHourElec['datetime'] -= $aTS['ts']*3600;
	}
	
	if($aHourElec['hour'] >= 23 || $aHourElec['hour'] < 7) {
		$sJson .= '{"x": '.$aHourElec['datetime'].'000, "y": '.$aHourElec['watt'].', "color": "#2f7ed8"},';
	} else {
		$sJson .= '{"x": '.$aHourElec['datetime'].'000, "y": '.$aHourElec['watt'].', "color": "#ffc600"},';
	}
}
$sJson = substr($sJson, 0, -1);
echo $sJson .= ']';
?>
