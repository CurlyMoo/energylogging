<?PHP
header('Content-Type: text/html; charset=utf-8');

$rDatabase = pg_connect("host=localhost port=5432 dbname=XXX user=XXX password=XXX");
$rHourGas = pg_query("select * from gas") or die(pg_error());
$iHourOffset = 0;

$sJson = '[';

$aPrevGas = Array();

while($aGas = pg_fetch_assoc($rHourGas)) {
	if($aGas['hour'] >= 23 || $aGas['hour'] < 7) {
		$sJson .= '{"x": '.($aGas['interval']-($iHourOffset*3600)).'000, "y": '.$aGas['m3'].', "color": "#2f7ed8"},';
	} else {
		$sJson .= '{"x": '.($aGas['interval']-($iHourOffset*3600)).'000, "y": '.$aGas['m3'].', "color": "#ffc600"},';
	}
}

$sJson = substr($sJson, 0, -1)."]";
echo $sJson;
?>
