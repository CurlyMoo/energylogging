<?PHP 
header('Content-Type: text/html; charset=utf-8');

$rDatabase = pg_connect("host=localhost port=5432 dbname=XXX user=XXX password=XXX");
$rHourElec = pg_query("select * from electricity_saldo") or die(pg_error());
$iHourOffset = 0;

$sJson = '[';

$aPrevElec = Array();

while($aHourElec = pg_fetch_assoc($rHourElec)) {
	$sJson .= '{"x": '.($aHourElec['interval']-($iHourOffset*3600)).'000, "y": '.$aHourElec['saldo'].', "color": "#ffc600"},';
}

$sJson = substr($sJson, 0, -1)."]";
echo $sJson;
?>
