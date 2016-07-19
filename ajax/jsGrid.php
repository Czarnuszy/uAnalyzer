<?php


function readCSV($csvFile){
	 $file_handle = fopen($csvFile, 'r');
	 	while (!feof($file_handle) ) {
	  	$line_of_text[] = fgetcsv($file_handle, 1024);
	 }
	 fclose($file_handle);
	 return $line_of_text;


}
$csvFile = '../zniffer/data/zniffer.csv';

$AnalyzerData = readCSV($csvFile);
$max = count($AnalyzerData) -1;


?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="ajax/w2ui/w2ui-1.4.3.min.css" />
    <script type="text/javascript" src="ajax/w2ui/w2ui-1.4.3.min.js"></script>
		<link rel="stylesheet" href="lib/js/themes/ui-lightness/jquery-ui.custom.css"></link>
	     <link rel="stylesheet" href="lib/js/jqgrid/css/ui.jqgrid.css"></link>
	     <script src="lib/js/jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
	     <script src="lib/js/jqgrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
	     <script src="lib/js/themes/jquery-ui.custom.min.js" type="text/javascript"></script>
</head>
<body>

<div id="gbod" style="width: 100%; height: 400px;"></div>

<script type="text/javascript">
// widget configuration
var config = {
    grid: {
        name: 'grid',
        show: {
					header         : true,
            footer    : true,
            toolbar    : true
        },
        columns: [
            { field: 'id', caption: 'ID', size: '5%', sortable: true, searchable: 'int', resizable: true },
						{ field: 'data', caption: 'Date', size: '20%', sortable: true, searchable: 'text', resizable: true },
            { field: 'rssi', caption: 'Rssi', size: '10%', sortable: true, searchable: 'int', resizable: true },
            { field: 'source', caption: 'Source', size: '10%', resizable: true, searchable: 'int', sortable: true },
            { field: 'route', caption: 'Route', size: '10%', resizable: true, sortable: true, searchable: 'text'},
            { field: 'destination', caption: 'Destination', size: '10%', resizable: true, sortable: true },
	    			{ field: 'command', caption: 'Command', size: '35%', sortable: true, searchable: 'int', resizable: true },
		//{ field: 'test2', caption: 'test2', size: '100px', type: "text", sortable: true, searchable: 'text',  resizable: true },
        ]
    }
}

$(function () {
    // initialization
    $().w2grid(config.grid);

		var rssi = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][1]."',";}   ?>]
		var data = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][0]."',";}   ?>]
		var source = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][3]."',";}   ?>]
		var payload = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][7]."',";}   ?>]
		var route = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][12]."',";}   ?>]
		var destination = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][5]."',";}   ?>]
		var command = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][7]."',";}   ?>]

		var max = <?php echo $max; ?>;

    for (var i = 0; i < max; i++) {
        w2ui['grid'].records.push({
            recid : i+1,
          id: i+1,
            rssi: rssi[i],
            data: data[i],
            source: source[i],
            route: route[i],
            destination:destination[i],
	    			command: payload[i] ,

        });
    }
    w2ui.grid.refresh();

    $('#gbod').w2render('grid');
});

</script>

</body>
</html>
