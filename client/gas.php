<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');


$rDayGas = mysql_query("SELECT * FROM gas") or die(mysql_error());
/* Make sure all missing hours are shows with zero's */
$iNrRows = mysql_num_rows($rDayGas);
$aPrevGas = Array();
$sJson = '[';
while($aDayGas = mysql_fetch_assoc($rDayGas)) {
	if(count($aPrevGas) > 1) {
		$iDay = 0;
		$bDaySet = false;
		$iDatePrev = $aPrevGas['datetime']/3600;
		$iDateCur = $aDayGas['datetime']/3600;
		$iHourPrev = $aPrevGas['hour'];
		
		$iMissingHours = $iDateCur-$iDatePrev;
		$iHourNew = $iHourPrev;		
		
		$aDate = new DateTime(date("Y-m-d H:i:s", $aDayGas['datetime']-(2*3600)));
                if($aDate->format('I') == 0) {
                        $aDayGas['hour'] -= 1;
                        $aDayGas['datetime'] -= 3600;
                }
		
		
		if($iMissingHours > 1) {
			for($i=0;$i<$iMissingHours-1;$i++) {
				if((++$iHourNew) >= 24) {
					$iHourPrev -= ($iHourNew-1);
					$iHourNew = 0;
					$iDay++;
				}
				$sJson .= '['.(($iDatePrev*3600)+($iDay*3600)+(($iHourNew-$iHourPrev)*3600)).'000,0],';
			}
		}
		$sJson .= '['.$aDayGas['datetime'].'000,'.$aDayGas['m3'].'],';		
	}
	$aPrevGas = $aDayGas;
}
$sJson = substr($sJson, 0, -1);
echo $sJson .= ']';
?>
