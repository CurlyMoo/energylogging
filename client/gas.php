<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');


$rDayGas = mysql_query("SELECT * FROM gas") or die(mysql_error());
/* Make sure all missing hours are shows with zero's */
$iNrRows = mysql_num_rows($rDayGas);
$aGas = Array();
while($aDayGas = mysql_fetch_assoc($rDayGas)) {
	$aGas[] = $aDayGas;
	if(count($aGas) > 1) {
		$iDay = 0;
		$bDaySet = false;
		$iDatePrev = $aGas[count($aGas)-2]['datetime']/3600;
		$iDateCur = $aDayGas['datetime']/3600;
		$iHourPrev = $aGas[count($aGas)-2]['hour'];
		
		$iMissingHours = $iDateCur-$iDatePrev;
		$iHourNew = $iHourPrev;
		if($iMissingHours > 1) {
			for($i=0;$i<$iMissingHours-1;$i++) {
				$x = count($aGas)-1;
				$aOldGas = $aGas[$x];
				if((++$iHourNew) >= 24) {
					$iHourPrev -= ($iHourNew-1);
					$iHourNew = 0;
					$iDay++;
				}
				$aGas[$x]['hour'] = $iHourNew;
				$aGas[$x]['datetime'] = ($iDatePrev*3600)+($iDay*3600)+(($iHourNew-$iHourPrev)*3600);
				$aGas[$x]['m3'] = 0;
				$aGas[$x+1]=Array();
				$aGas[$x+1]=$aOldGas;
			}
		}
	}
}
$sJson = '[';
$i=0;
foreach($aGas as $aDayGas) {
	$sJson .= '['.$aDayGas['datetime'].'000,'.$aDayGas['m3'].'],';
	$i++;
}
$sJson = substr($sJson, 0, -1);
echo $sJson .= ']';
?>
