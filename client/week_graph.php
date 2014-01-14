<?PHP 
header('Content-Type: text/html; charset=utf-8');

$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('log');


$rWeek = mysql_query("SELECT UNIX_TIMESTAMP(`datetime`) as `date`, YEAR(`datetime`) as `year`, WEEK(`datetime`) AS week, `usage` FROM consumption WHERE DAYNAME(`datetime`) = 'Monday' GROUP BY WEEK(`datetime`), rate_id ORDER BY YEAR(`datetime`), WEEK(`datetime`), rate_id") or die(mysql_error());

/* Make sure all missing hours are shows with zero's */
$iWeek = 0;
$i=0;
$aPrev = Array();
$aData = Array();
while($aWeek = mysql_fetch_assoc($rWeek)) {
	if($iWeek != $aWeek['week']) {
		$i=0;
		$iWeek = $aWeek['week'];
		$iDate = strtotime('1/1/'.$aWeek['year'].' + '.$iWeek.' weeks');
	}
	$i++;
	if(isset($aPrev[$i])) {
		$aData[$iDate][$i] = round($aWeek['usage']-$aPrev[$i],2);
	} else {
		
	}
	$aPrev[$i] = $aWeek['usage'];
}
$sJson = '{"gas": [';
foreach($aData as $iWeek => $aValue) {

	if(isset($aValue[1])) {
		$sJson .= '{"x": '.$iWeek.'000, "y": '.$aValue[1].'},';
	}
}
$sJson = substr($sJson, 0, -1);
$sJson .= '],';
$sJson .= '"elech": [';
foreach($aData as $iWeek => $aValue) {
	if(isset($aValue[2])) {
		$sJson .= '{"x": '.$iWeek.'000, "y": '.$aValue[2].'},';
	}
}
$sJson = substr($sJson, 0, -1);
$sJson .= '],';
$sJson .= '"elecl": [';
foreach($aData as $iWeek => $aValue) {
	if(isset($aValue[3])) {
		$sJson .= '{"x": '.$iWeek.'000, "y": '.$aValue[3].'},';
	}
}
$sJson = substr($sJson, 0, -1);
echo $sJson .= ']}';
?>
