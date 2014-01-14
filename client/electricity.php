<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');

$rHourElec = mysql_query("SELECT * FROM electricity");
$rSE = mysql_query("SELECT MAX(`watt`) as `max`, AVG(`watt`) as `avg` FROM electricity");
$rTS = mysql_query("SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP) as ts");
$aTS = mysql_fetch_assoc($rTS);
$aSE = mysql_fetch_assoc($rSE);

$iMax = ($aSE['avg'])*1.25;
$sJson = '[';
while($aHourElec = mysql_fetch_assoc($rHourElec)) {

	$aDate = new DateTime(date("Y-m-d H:i:s", $aHourElec['datetime']-(($aTS['ts']+1)*3600)), new DateTimeZone(date_default_timezone_get()));
	if($aHourElec['watt']/$aSE['avg'] > $iMax) {
		$rRow = mysql_query("SELECT * FROM `electricity` WHERE `datetime` > '".($aHourElec['datetime']-1000)."' AND `datetime` < '".($aHourElec['datetime']+1000)."'") or die(mysql_error());
		$iMean = 0;
		$i = 0;
		while($aRow = mysql_fetch_assoc($rRow)) {
			if($aRow['watt'] < $iMax) {
				$i++;
				$iMean = $aRow['watt'];
			}
		}
		$aHourElec['watt'] = $iMean;
		mysql_query('UPDATE `electricity` SET `watt` = '.$iMean.' WHERE `datetime` = '.$aHourElec['datetime']) or die(mysql_error());
	}
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
