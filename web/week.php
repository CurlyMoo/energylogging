<!DOCTYPE HTML>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
		<title>Energy Weekly Summary</title>
		<script type="text/javascript" src="js/jquery.min.js"></script>
		<script type="text/javascript">
$(function() {
	$.getJSON('week_graph.php', function(data) {
		// create the charts
		$('#electricityh').highcharts('StockChart', {

			chart: {
				alignTicks: false
			},

			rangeSelector: {
				enabled: false
			},

			title: {
				text: 'Electricity usage'
			},
			
			credits: {
				enabled: false
			},
			
			exporting: {
				enabled: false
			},
			
			navigator: {
				enabled: false
			},

			series: [{
				type: 'line',
				name: 'kWh high',
				turboThreshold: 10000000,
				data: data['elech0'],
				color: "#ca697c",
				animation: false
			},{
				type: 'line',
				name: 'kWh low',
				turboThreshold: 10000000,
				data: data['elecl0'],
				color: "#eb9aa3",
				animation: false
			},{
				type: 'line',
				name: 'kWh bruto',
				turboThreshold: 10000000,
				data: data['saldo'],
				color: "#a7c8f1",
				animation: false
			},{
				type: 'line',
				name: 'kWh saldo',
				turboThreshold: 10000000,
				data: data['saldo1'],
				color: "#f9d8e1",
				animation: false
			},{
				type: 'line',
				name: 'kWh overcapacity',
				turboThreshold: 10000000,
				data: data['solart'],
				color: "#fadc56",
				animation: false
			},{
				type: 'line',
				name: 'kWh generated',
				turboThreshold: 10000000,
				data: data['solaro'],
				color: "#6ac59c",
				animation: false
			}]
		});

		$('#gas').highcharts('StockChart', {

			chart: {
				alignTicks: false
			},

			rangeSelector: {
				enabled: false
			},

			title: {
				text: 'Gas Usage'
			},
			
			credits: {
				enabled: false
			},
			
			exporting: {
				enabled: false
			},
			
			navigator: {
				enabled: false
			},

			series: [{
				type: 'line',
				name: 'm3',
				turboThreshold: 10000000,
				data: data['gas0'],
				color: "#ca697c",
				animation: false
			}]
		});		
	});
});
		</script>
	</head>
		<script src="js/highstock.js"></script>
		<script src="js/modules/exporting.js"></script>
	</head>
<body>
<div id="electricityh" style="height: 500px"></div>
<div id="gas" style="height: 250px"></div>
</body>
</html>
