<?PHP 
header('Content-Type: text/html; charset=utf-8');

$rDatabase = pg_connect("host=localhost port=5432 dbname=XXX user=XXX password=XXX");
$rHourElec = pg_query("select * from solar_in") or die(pg_error());
$iHourOffset = 0;

$aPrevElec = Array();
$aData = Array();
while($aHourElec = pg_fetch_assoc($rHourElec)) {
	$aData[$aHourElec['interval']] = Array();
	$aData[$aHourElec['interval']]['north'] = $aHourElec['north'];
	$aData[$aHourElec['interval']]['south'] = $aHourElec['south'];
	$aData[$aHourElec['interval']]['total'] = $aHourElec['total'];
}

$sJson = '{';
$sJson .= '"north": [';
foreach($aData as $sInterval => $aWatt) {
	$sJson .= '{"x": '.($sInterval-($iHourOffset*3600)).'000, "y": '.round($aWatt['north']).', "color": "#f9d8e1"},';
}
$sJson = substr($sJson, 0, -1)."],";
$sJson .= '"south":[';
foreach($aData as $sInterval => $aWatt) {
	$sJson .= '{"x": '.($sInterval-($iHourOffset*3600)).'000, "y": '.round($aWatt['south']).', "color": "#ca697c"},';
}
$sJson = substr($sJson, 0, -1)."],";
$sJson .= '"total":[';
foreach($aData as $sInterval => $aWatt) {
	$sJson .= '{"x": '.($sInterval-($iHourOffset*3600)).'000, "y": '.round($aWatt['total']).', "color": "#ffc600"},';
}
$sJson = substr($sJson, 0, -1)."]}";
echo $sJson;
?>
