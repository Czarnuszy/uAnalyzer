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

$fileID = fopen("../zniffer/data/id.txt", "r") or die("Unable to open file!");
$homeid = fgets($fileID);
fclose($fileID);


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

<div id="gbod" style="width: 100%; height: 600px;"></div>

<script type="text/javascript">
//var h = window.innerHeight - 250+'px';
//var h2 = document.getElementById('body-w').clientHeight-10; + 'px';
//console.log(h2);
//$('#gbod').css({"height": h2});

$( window ).resize(function() {
//	var h2 = document.getElementById('body-w').clientHeight; + 'px';
	var h = window.innerHeight - 275+'px';
  $('#gbod').css({"height": h});
	console.log(h);
});
// widget configuration
var config = {
    grid: {
        name: 'grid',
        show: {

            footer    : true,
            toolbar    : true
        },
        columns: [
            { field: 'id', caption: 'ID', size: '5%', sortable: true, searchable: 'int', resizable: true,  attr: "align=center" },
						{ field: 'data', caption: 'Date', size: '20%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
            { field: 'rssi', caption: 'Rssi', size: '10%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },
            { field: 'source', caption: 'Source', size: '10%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },
            { field: 'route', caption: 'Route', size: '10%', resizable: true, sortable: true, searchable: 'text', attr: "align=center"},
            { field: 'destination', caption: 'Destination', size: '10%', resizable: true, sortable: true, searchable: 'text', attr: "align=center" },
	    			{ field: 'command', caption: 'Command', size: '25%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },

		//{ field: 'test2', caption: 'test2', size: '100px', type: "text", sortable: true, searchable: 'text',  resizable: true },
	],
	searches: [
				{ field: 'h_id', caption: 'h_id', type: 'int', hidden: true },

				]
    }
}

$(function () {
    // initialization
    $().w2grid(config.grid);
		if(w2ui.grid.records.length > 0)
			w2ui['grid'].clear();

		var user_home_id= <?php  echo "'".$homeid."'"; ?>;

		var rssi = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][1]."',";}   ?>]
		var data = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][0]."',";}   ?>]
		var source = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][3]."',";}   ?>]
		var payload = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][7]."',";}   ?>]
		var route = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][12]."',";}   ?>]
		var destination = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][5]."',";}   ?>]
		var command = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][7]."',";}   ?>]
		var home_id = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][2]."',";}   ?>]
		var seq_num = [<?php for ($i=0; $i < $max; $i++){ echo "'".$AnalyzerData[$i][8]."',";}   ?>]

		var max = <?php echo $max ; ?>;
		var x =1;
		var color = "red";

		for (var i = 0; i < max; i++) {
			color = "red";
			if(home_id[i] != user_home_id){
					source[i] = '-';
					destination[i] = '-';
					route[i] = '-';
				}else{
				//	if(seq_num[i] === "00")
				//		color = "#f0f0f0";
					if (seq_num[i] == "01")
						color = "#f0f0f0";
					else if (seq_num[i]  === "02")
						color = "#808080";
					else if (seq_num[i]  == "03")
						color = "#D0D0D0";
					else if (seq_num[i]  == "04")
						color = "#909090";
					else if (seq_num[i]  == "05")
						color = "#C0C0C0";
					else if (seq_num[i]  == "06")
						color = "#A0A0A0";
					else if (seq_num[i]  == "07")
						color = "#B8B8B8";
					else if (seq_num[i]  == "08")
						color = "#A8A8A8";
					else if (seq_num[i]  == "09")
						color = "#B0B0B0";
					else if (seq_num[i]  == "10")
						color = "#989898";
					else if (seq_num[i]  == "11")
						color = "#C8C8C8";
					else if (seq_num[i]  == "12")
						color = "#888888";
					else if (seq_num[i]  == "13")
						color = "#D8D8D8";
					else if (seq_num[i]  == "14")
						color = "#E0E0E0";
					else if (seq_num[i]  == "15")
						color = "#E8E8E8";
				}
				var bg = "background-color: "
				var sty = bg + color;
	        w2ui['grid'].records.push({
	            recid : i+1,
	          	id: i+1,
	            rssi: rssi[i],
	            data: data[i],
	            source: source[i],
	            route: route[i],
	            destination:destination[i],
		    			command: payload[i] ,
							h_id: home_id[i],
							seq_num: seq_num[i],
							style: "background-color: " + color
        });

			//	var recs = w2ui['grid'].find({ h_id: user_home_id });

		//			for(x=1; x<max; x++){
			//			if($.inArray(x, recs) == -1 )
			//				w2ui['grid'].set(x, { source: '-', destination: '-' });
					//	}

    }
    w2ui.grid.refresh();
;
    $('#gbod').w2render('grid');
});

w2ui['grid'].hideColumn('h_id');

</script>

</body>
</html>
