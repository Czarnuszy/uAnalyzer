<?php
$fileID = fopen("../zniffer/data/id.txt", "r") or die("Unable to open file!");
$homeid = fgets($fileID);
fclose($fileID);
?>


<!DOCTYPE html>
<html>
<head>
    <link rel="stylesheet" type="text/css" href="ajax/w2ui/w2ui-1.4.3.css" />
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
		var atr = '../zniffer/data/zniffer.csv';
		var user_home_id= <?php  echo "'".$homeid."'"; ?>;
		var NumberofLines = 0;
		open_file(atr);
    w2ui.grid.refresh();
    $('#gbod').w2render('grid');
});
w2ui['grid'].hideColumn('h_id');
</script>

</body>
</html>
