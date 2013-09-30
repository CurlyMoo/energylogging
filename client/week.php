<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Energy Weekly Summary</title>
	</head>
<body>
<table style="width: 100%; border: 1px solid black; border-collapse: collapse; text-align: left;">
	<tr>
		<th style="border: 1px solid black; background: #FFCC00; width: 120px;">Week</th>
		<th style="border: 1px solid black; background: #FFCC00;">Gas</th>
		<th style="border: 1px solid black; background: #FFCC00;">Electricity High</th>
		<th style="border: 1px solid black; background: #FFCC00;">Electricity Low</th>
<?PHP 
header('Content-Type: text/html; charset=utf-8');

$rConnect = mysql_connect('127.0.0.1', 'username', 'password');
$rDatabase = mysql_select_db('database');


$rWeek = mysql_query("SELECT WEEK(`datetime`) AS week, `usage` FROM consumption WHERE DAYNAME(`datetime`) = 'Monday' GROUP BY WEEK(`datetime`), rate_id ORDER BY WEEK(`datetime`), rate_id") or die(mysql_error());
		
/* Make sure all missing hours are shows with zero's */
$iWeek = 0;
while($aWeek = mysql_fetch_assoc($rWeek)) {
	if($iWeek != $aWeek['week']) {
		$iWeek = $aWeek['week'];	
?>
	</tr>	
	<tr <?=(($aWeek['week']%2 == 0) ? 'style="background: #DDDDDD"' : '')?>>	
			<td style="border: 1px solid black;"><?=$aWeek['week']?></td>	
<?PHP
	}
?>			
			<td style="border: 1px solid black;"><?=$aWeek['usage']?></td>
<?PHP
}

$rElecLow = mysql_query("SELECT (AVG(watt)*4*8*365)/1000 as avg FROM `electricity` WHERE hour < 7 OR hour = 23") or die(mysql_error());
$aElecLow = mysql_fetch_assoc($rElecLow);

$rElecHigh = mysql_query("SELECT (AVG(watt)*4*16*365)/1000 as avg FROM `electricity` WHERE hour > 7 AND hour < 23") or die(mysql_error());
$aElecHigh = mysql_fetch_assoc($rElecHigh);

$rDayGas = mysql_query("SELECT * FROM gas") or die(mysql_error());
/* Make sure all missing hours are shows with zero's */
$iNrGas = 0;
$iTotalGas = 0;
$aPrevGas = Array();
while($aDayGas = mysql_fetch_assoc($rDayGas)) {
	if(count($aPrevGas) > 1) {
		$iDay = 0;
		$bDaySet = false;
		$iDatePrev = $aPrevGas['datetime']/3600;
		$iDateCur = $aDayGas['datetime']/3600;
		$iHourPrev = $aPrevGas['hour'];
		
		$iMissingHours = $iDateCur-$iDatePrev;
		$iHourNew = $iHourPrev;		
		
		if($iMissingHours > 1) {
			for($i=0;$i<$iMissingHours-1;$i++) {
				if((++$iHourNew) >= 24) {
					$iHourPrev -= ($iHourNew-1);
					$iHourNew = 0;
					$iDay++;
				}
				$iNrGas++;
				$iTotalGas += 0;
			}
		}
		$iNrGas++;
		$iTotalGas += $aDayGas['m3'];
	}
	$aPrevGas = $aDayGas;
}
?>
	</tr>
</table>
<br />
<table style="width: 100%; border: 1px solid black; border-collapse: collapse; text-align: left;">
	<tr>
		<th style="border: 1px solid black; background: #FFCC00;" colspan="2"><b>Forecast</b></td>
	</tr>
	<tr>
		<td style="border: 1px solid black; width: 120px;">Gas:</td>
		<td style="border: 1px solid black;"><?=round((($iTotalGas/$iNrGas)*24*365), 3)?></td>
	</tr>
	<tr style="background: #DDDDDD">
		<td style="border: 1px solid black; width: 120px;">Electricity:</td>
		<td style="border: 1px solid black;"><?=round($aElecHigh['avg']+$aElecLow['avg'], 3)?></td>
	</tr>		
	<tr>
		<td style="border: 1px solid black; width: 120px;">- High:</td>
		<td style="border: 1px solid black;"><?=round($aElecHigh['avg'], 3)?></td>
	</tr>
	<tr style="background: #DDDDDD">
		<td style="border: 1px solid black; width: 120px;">- Low:</td>
		<td style="border: 1px solid black;"><?=round($aElecLow['avg'], 3)?></td>
	</tr>
</table>
</body>
</html>
