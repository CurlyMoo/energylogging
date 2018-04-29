<?PHP 
header('Content-Type: text/html; charset=utf-8');

$rDatabase = pg_connect("host=localhost port=5432 dbname=XXX user=XXX password=XXX");

function getPrevWeek($iWeek) {
	$iWeekPart = substr($iWeek, 4, 2);
	$iYearPart = substr($iWeek, 0, 4);
	$iPrevWeek = $iWeekPart-1;
	if($iPrevWeek == 0) {
		$iPrevWeek = ($iYearPart-1).'52';
	} else {
		$iPrevWeek = $iWeek-1;
	}
	return $iPrevWeek;
}

function getNextWeek($iWeek) {
	$iWeekPart = substr($iWeek, 4, 2);
	$iYearPart = substr($iWeek, 0, 4);
	$iNextWeek = $iWeekPart+1;
	if($iNextWeek == 53) {
		$iNextWeek = ($iYearPart+1).'01';
	} else {
		$iNextWeek = $iWeek+1;
	}
	return $iNextWeek;
}

$rWeek = pg_query("select * from week") or die(pg_error());

$sJson = '{"gas0": [';
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['gas_diff'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= '],"elech0": [';

$rWeek = pg_query("select * from week") or die(pg_error());
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['elec_hoog_in_diff'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= '],"elecl0": [';

$rWeek = pg_query("select * from week") or die(pg_error());
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['elec_laag_in_diff'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= '],"solaro": [';

$rWeek = pg_query("select * from week") or die(pg_error());
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['zon_diff'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= '],"solart": [';

$rWeek = pg_query("select * from week") or die(pg_error());
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['elec_hoog_uit_diff'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= '],"saldo": [';
$rWeek = pg_query("select * from week") or die(pg_error());
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['elec_bruto'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= '],"saldo1": [';
$rWeek = pg_query("select * from week") or die(pg_error());
while($aWeek = pg_fetch_assoc($rWeek)) {
	$iDate = strtotime('Last Monday', strtotime('1/1/'.$aWeek['year'].' + '.$aWeek['week'].' weeks'));
	$sJson .= '{"x": '.$iDate.'000, "y": '.round($aWeek['elec_netto'], 2).'},';
}
$sJson = substr($sJson, 0, -1);

$sJson .= ']}';
echo $sJson
?>
