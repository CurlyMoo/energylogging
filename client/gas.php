<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');


$rDayGas = mysql_query("
SELECT
	`datetime`,
	`hour`,
	ROUND((`min`-IFNULL((SELECT 
							`max` 
						FROM 
							gas 
						WHERE 
							datetime < t1.datetime 
						ORDER BY 
							datetime 
						DESC 
						LIMIT 1), `min`))*1000)/1000 AS m3
FROM 
	gas t1") or die(mysql_error());
		
/* Make sure all missing hours are shows with zero's */
$iNrRows = mysql_num_rows($rDayGas);
$aGas = Array();
while($aDayGas = mysql_fetch_assoc($rDayGas)) {
	$aGas[] = $aDayGas;
	if(count($aGas) > 1) {
		$iHour = $aGas[count($aGas)-2]['hour'];
		if($iHour == 23) {
			$iHour = 0;
			$y=0;
		} else {
			$y=1;
		}
		$missingHours=(($aDayGas['hour']-$iHour));
		
		if($iHour == 22 && $aDayGas['hour'] != 23) {
			$x = count($aGas)-1;
			$aOldGas = $aGas[$x];
			$aGas[$x]['hour'] = 23;
			$aGas[$x]['datetime'] = strtotime(date("d-m-Y", $aGas[$x-1]['datetime']).(($aGas[$x]['hour'] < 10) ? "0".$aGas[$x]['hour'] : $aGas[$x]['hour']).".00.00");
			$aGas[$x]['m3'] = 0;
			$aGas[$x+1]=Array();
			$aGas[$x+1]=$aOldGas;	
			$y=0;
			if($aOldGas['hour'] != 0) {
				$missingHours=2;
				$iHour = -1;
				$y=1;
			}
		}

		if($missingHours > 1) {
			for($i=0;$i<($missingHours-$y);$i++) {
				$x = count($aGas)-1;
				$aOldGas = $aGas[$x];
				$aGas[$x]['hour'] = $iHour+($i+$y);
				$aGas[$x]['datetime'] = strtotime(date("d-m-Y", $aGas[$x]['datetime']).(($aGas[$x]['hour'] < 10) ? "0".$aGas[$x]['hour'] : $aGas[$x]['hour']).".00.00");
				$aGas[$x]['m3'] = 0;
				$aGas[$x+1]=Array();
				$aGas[$x+1]=$aOldGas;
			}
		}
	}
}
$sJson = '[';
foreach($aGas as $aDayGas) {
	$sJson .= '['.$aDayGas['datetime'].'000,'.$aDayGas['m3'].'],';
}
$sJson = substr($sJson, 0, -1);
echo $sJson .= ']';
?>