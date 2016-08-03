<?php

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
		var atr = '../zniffer/data/zniffer.csv';

		var user_home_id= <?php  echo "'".$homeid."'"; ?>;
		var NumberofLines = 0;

	$.ajax({
		url: "ajax/files_size.php",
		type: "POST",
		data: { DisplayedRecords: atr},
		success: function(response){
			NumberofLines= response-1;

      if(NumberofLines > 1000){
        console.log("over 10000");

        if(w2ui.grid.records.length > 0)
          w2ui.grid.clear();

        var reclen = w2ui.grid.records.length;
        var i = NumberofLines / 2000;
        i = parseInt(i);

        var val = [0];
        for (var x = 1; x < i; x++) {
          val.push(x * 2000);
        }
        val.push(val[val.length-1] + 2000); //NumberofLines-i*1000
        console.log(val);
        val.forEach(function(value, i){
          console.log("val" + value);

          $.ajax({
            url: 'ajax/open_file_data.php',
            type: 'POST',
            async: false,
            data: { data: atr, fsize: NumberofLines, tim: value+2000 , gridLen: value},
            dataType: 'json',
            success: function(data){
          //    reclen = w2ui.grid.records.length;
              console.log("gridlen" + w2ui.grid.records.length);
              var color = "red";
              console.log("dl" + data.length);
        			for(x=0; x< data.length	; x++){
                reclen = w2ui.grid.records.length;
                color = "red";
        				if (data[x][2] != home_id){
        						data[x][3] = '."'-'".';
        						data[x][5] = '."'-';".';
                    data[x][12] = '-';
        					}else {
        						color = parse_sqnum(x, data);
        					}

          				w2ui['grid'].records.push({
          					recid : reclen+1,
          					id: reclen+1,
          					rssi: data[x][1],
          					data: data[x][0],
          					source: data[x][3],
          					route: data[x][12],
          					destination: data[x][5],
          				 	command: data[x][7],
          				 	h_id: data[x][2],
          				 	style: "background-color: " + color

          				 });
          		}
          		w2ui.grid.reload();


              }
            });
          });
    //       }
    $.smallBox({
      title : "Z-Wave Packet Analyzer",
      content : "<i>File opened.</i>",
      color : "#659265",
      iconSmall : "fa fa-check fa-2x fadeInRight animated",
      timeout : 1000
    });
      }  else{

    	$.ajax({
    		url: 'ajax/open_file_data.php',
    		type: 'POST',
    		data: { data: atr, fsize: NumberofLines},
    		dataType: 'json',
    		success: function(data){
    			if(w2ui.grid.records.length > 0)
    				w2ui.grid.clear();

    			var color = "red";
    			for(x=0; x<	NumberofLines; x++){
            color = "red";
    				if (data[x][2] != home_id){
    						data[x][3] = '."'-'".';
    						data[x][5] = '."'-';".';
                data[x][12] = '-';
    					}else {
    						color = parse_sqnum(x, data);
    					}
    				w2ui['grid'].records.push({
    					recid : x+1,
    					id: x+1,
    					rssi: data[x][1],
    					data: data[x][0],
    					source: data[x][3],
    					route: data[x][12],
    					destination: data[x][5],
    				 	command: data[x][7],
    				 	h_id: data[x][2],
    				 	style: "background-color: " + color

    				 });
    		}
    		w2ui.grid.reload();
    		$.smallBox({
    			title : "Z-Wave Packet Analyzer",
    			content : "<i>File opened.</i>",
    			color : "#659265",
    			iconSmall : "fa fa-check fa-2x fadeInRight animated",
    			timeout : 1000
    		});
    	}


    	});
    }

      }
	});
    w2ui.grid.refresh();

    $('#gbod').w2render('grid');
});

w2ui['grid'].hideColumn('h_id');

</script>

</body>
</html>
