<?php
$fileID = fopen("../zniffer/data/id.txt", "r") or die("Unable to open file!");
$homeid = fgets($fileID);
fclose($fileID);
?>


<!DOCTYPE html>
<html>
<head>

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
            toolbar    : true,
             lineNumbers  : true,
        },
        columns: [
      			{ field: 'data', caption: 'Date', size: '17%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
            { field: 'rssi', caption: 'Rssi', size: '10%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },
            { field: 'source', caption: 'Source', size: '10%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },
            { field: 'route', caption: 'Route', size: '10%', resizable: true, sortable: true, searchable: 'text', attr: "align=center"},
            { field: 'destination', caption: 'Destination', size: '10%', resizable: true, sortable: true, searchable: 'text', attr: "align=center" },
	    			{ field: 'command', caption: 'Command', size: '22%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
		//{ field: 'test2', caption: 'test2', size: '100px', type: "text", sortable: true, searchable: 'text',  resizable: true },
	],
	searches: [
				{ field: 'h_id', caption: 'h_id', type: 'int', hidden: true },
				]
    }
}
var pd = 0;

function progressbar(x){
	var progress = 0 ;
	progress += pd;
	var pr = progress + "%";
	var html = "Please Wait	<div class=" + "'progress progress-micro'"+">	<div class="+
	"'progress-bar bg-color-blueLight'"+" role='progressbar'" + "style='width: "+pr+";'"+">" +
	 "</div></div>"

	$( "#progresZniffer" ).html( html );
	pd += 10;
	if (pd >= 100){
		clearInterval(progrssInt);
		pd =0;
		$( "#progresZniffer" ).html( " " );
	}
}

$(function () {
    // initialization
    $().w2grid(config.grid);
		var atr = '../zniffer/data/zniffer.csv';
		var user_home_id= <?php  echo "'".$homeid."'"; ?>;
		var NumberofLines = 0;
    w2ui.grid.lock('Getting ready.', true);
  //  progrssInt = setInterval(function() {progressbar(pd);}, 200);
		open_file(atr);
    w2ui.grid.refresh();
    $('#gbod').w2render('grid');
});
w2ui['grid'].hideColumn('h_id');
</script>

</body>
</html>
