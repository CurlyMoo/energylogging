<?PHP 
header('Content-Type: text/html; charset=utf-8');
$rConnect = mysql_connect('x.x.x.x', '*username*', '*password*');
$rDatabase = mysql_select_db('*database*');

$rHourElec = mysql_query("
SELECT
	`hour`,
	`datetime`,
	ROUND(
		((`max`-`min`)+
		(`min`-IFNULL((SELECT 
							`max` 
						FROM 
							electricity 
						WHERE 
							datetime < t1.datetime 
						AND 
							rate = t1.rate
                       	AND
                       		MINUTE(FROM_UNIXTIME(t1.`datetime`)) > 0
						ORDER BY 
							datetime 
						DESC 
						LIMIT 1), `min`)))*1000) AS watt
FROM 
	electricity t1
");

$iNrRows = mysql_num_rows($rHourElec);
$sJson = '[';
while($aHourElec = mysql_fetch_assoc($rHourElec)) {
	if($aHourElec['hour'] >= 23 || $aHourElec['hour'] < 7)
		$sJson .= '{"x": '.$aHourElec['datetime'].'000, "y": '.$aHourElec['watt'].', "color": "#2f7ed8"},';
	else
		$sJson .= '{"x": '.$aHourElec['datetime'].'000, "y": '.$aHourElec['watt'].', "color": "#ffc600"},';
}
$sJson = substr($sJson, 0, -1);
echo $sJson .= ']';
?>