<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');

$iHourOffset = 2;

if(file_exists("electricity.json")) {
	$sContent = file_get_contents("electricity.json");
} else {
	$sContent = '';
}
$iLen = strlen($sContent);
$sLContent = substr($sContent, $iLen-60, $iLen);
$iLDateTime = 0;
if(preg_match('("x": ([0-9]{13})+)', $sLContent, $aMatches) > 0) {
	$iLDateTime = ($aMatches[1]/1000);
}

$rHourElec = mysql_query("SELECT * FROM electricity WHERE `datetime` > ".$iLDateTime) or die(mysql_error());
$rSE = mysql_query("SELECT MAX(`watt`) as `max`, AVG(`watt`) as `avg` FROM electricity");
$rTS = mysql_query("SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP) as ts");
$aTS = mysql_fetch_assoc($rTS);
$aSE = mysql_fetch_assoc($rSE);

$iMax = ($aSE['avg'])*1.25;
if($iLen == 0) {
	$sJson = '[';
} else {
	$sJson = substr($sContent, 0, -1).",";
}
while($aHourElec = mysql_fetch_assoc($rHourElec)) {

	$aDate = new DateTime(date("Y-m-d H:i:s", $aHourElec['datetime']-(($aTS['ts']+1)*3600)), new DateTimeZone(date_default_timezone_get()));
	if(($aHourElec['watt']/$aSE['avg'] > $iMax) || ($aHourElec['watt']/$aSE['avg'] < 0)) {
		$rRow = mysql_query("SELECT * FROM `electricity` WHERE `datetime` > '".($aHourElec['datetime']-1000)."' AND `datetime` < '".($aHourElec['datetime']+1000)."'") or die(mysql_error());
		$iMean = 0;
		$i = 0;
		while($aRow = mysql_fetch_assoc($rRow)) {
			if(($aRow['watt']/$aSE['avg']) < $iMax && ($aRow['watt']/$aSE['avg']) > 0) {
				$i++;
				$iMean = $aRow['watt'];
			}
		}
		$aHourElec['watt'] = $iMean;
		mysql_query('UPDATE `electricity` SET `watt` = '.$iMean.' WHERE `datetime` = '.$aHourElec['datetime']) or die(mysql_error());
	}
//	if($aDate->format('I') == 0) {
//		$aHourElec['datetime'] -= $aTS['ts']*3600;
//	}
	
	if($aHourElec['hour'] >= 23 || $aHourElec['hour'] < 7) {
		$sJson .= '{"x": '.$aHourElec['datetime'].'000, "y": '.$aHourElec['watt'].', "color": "#2f7ed8"},';
	} else {
		$sJson .= '{"x": '.$aHourElec['datetime'].'000, "y": '.$aHourElec['watt'].', "color": "#ffc600"},';
	}
}
$sJson = substr($sJson, 0, -1)."]";
file_put_contents("electricity.json", $sJson);
echo $sJson;
?>
