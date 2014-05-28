<?PHP
header('Content-Type: text/html; charset=utf-8');

$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');

if(file_exists("gas.json")) {
	$sContent = file_get_contents("gas.json");
} else {
	$sContent = '';
}
$iLen = strlen($sContent);
$sLContent = substr($sContent, $iLen-60, $iLen);
$iLDateTime = 0;
if(preg_match('("x": ([0-9]{13})+)', $sLContent, $aMatches) > 0) {
	$iLDateTime = $aMatches[1]/1000;
}

$rDayGas = mysql_query("SELECT * FROM gas WHERE `datetime` > ".$iLDateTime." ORDER BY `datetime`") or die(mysql_error());
$rTS = mysql_query("SELECT TIMEDIFF(NOW(), UTC_TIMESTAMP) as ts");
$aTS = mysql_fetch_assoc($rTS);

/* Make sure all missing hours are shows with zero's */
$aPrevGas = Array();
if($iLen == 0) {
	$sJson = '[';
} else {
	$sJson = substr($sContent, 0, -1).",";
}

while($aDayGas = mysql_fetch_assoc($rDayGas)) {
	if(count($aPrevGas) > 1) {
		$iDay = 0;
		$bDaySet = false;	
		$iDatePrev = $aPrevGas['hour'];
		$iDateCur = date("H", $aDayGas['datetime'])+(int)(substr($aTS['ts'], 0, 2));
		$iHourPrev = $aPrevGas['hour'];
		$iMissingHours = $iDateCur-$iDatePrev;
		if($iMissingHours < 0) {
			$iMissingHours += 24;
		}
		$iHourNew = $iHourPrev;		

		if($iMissingHours > 1) {
			for($i=0;$i<$iMissingHours-1;$i++) {
				if((++$iHourNew) >= 24) {
					$iHourPrev -= ($iHourNew-1);
					$iHourNew = 0;
					$iDay++;
				}
				$iDT = (($aPrevGas['datetime'])+($iDay*3600)+(($iHourNew-$iHourPrev)*3600));

				if(date("H", $iDT) >= 23 || date("H", $iDT) < 7) {
					$sJson .= '{"x": '.$iDT.'000, "y": 0, "color": "#2f7ed8"},';
				} else {
					$sJson .= '{"x": '.$iDT.'000, "y": 0, "color": "#ffc600"},';
				}
			}
		}

		if(date("H", $aDayGas['datetime']) >= 23 || date("H", $aDayGas['datetime']) < 7) {
			$sJson .= '{"x": '.$aDayGas['datetime'].'000, "y": '.$aDayGas['m3'].', "color": "#2f7ed8"},';
		} else {
			$sJson .= '{"x": '.$aDayGas['datetime'].'000, "y": '.$aDayGas['m3'].', "color": "#ffc600"},';
		}
	}
	$aPrevGas = $aDayGas;
}
$sJson = substr($sJson, 0, -1)."]";
file_put_contents("gas.json", $sJson);
echo $sJson;
?>
