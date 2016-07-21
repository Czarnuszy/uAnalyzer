
<?php
  $directory = '../data/Saves';
  $scanned_directory = array_diff(scandir($directory), array('..', '.'));

  function readCSV($csvFile){
  	 $file_handle = fopen($csvFile, 'r');
  	 	while (!feof($file_handle) ) {
  	  	$line_of_text[] = fgetcsv($file_handle, 1024);
  	 }
  	 fclose($file_handle);
  	 return $line_of_text;
  }
  $file_name = 'fds.csv';
  $csvFile = '../data/Saves/'.$scanned_directory[2];

  $AnalyzerData = readCSV($csvFile);
  $max = count($AnalyzerData) -1;


  //print_r($scanned_directory);
  $amount_files = count($scanned_directory);


?>


<!DOCTYPE html>
<html>
<head>
    <title>Open csv file sys</title>
    <link rel="stylesheet" type="text/css" href="ajax/w2ui/w2ui-1.4.3.min.css" />
    <script type="text/javascript" src="ajax/w2ui/w2ui-1.4.3.min.js"></script>
</head>
<body>

<div id="gbod" style="width: 100%; height: 400px;"></div>



<script type="text/javascript">



        openPopup();

var home_id = "EC549ADB";

// widget configuration
var config = {
    layout: {
        name: 'layout',
        padding: 0,
        panels: [
            { type: 'top', size: 32, content: '<div style="padding: 7px;">Top Panel</div>', style: 'border-bottom: 1px solid silver;' },
            { type: 'left', size: 400, resizable: true, minSize: 120 },
        //    { type: 'main', minSize: 350, overflow: 'hidden' }
        ]
    },
    sidebar: {
        name: 'sidebar',
        nodes: [
            { id: 'general', text: 'General', group: true, expanded: true, nodes: [
              <?php
                for($i = 2; $i <= $amount_files; $i+=2)
                  echo "{ id: '".$scanned_directory[$i]."', text: '".$scanned_directory[$i]."', img: 'icon-page' },";
                ?>
            ]}
        ],
        onClick: function (event) {
            switch (event.target) {
                <?php
                    //echo "w2ui.grid.clear();";
                  for($i = 2; $i <= $amount_files; $i+=2){
                      echo "case '".$scanned_directory[$i]."': ";
                    //  echo "w2ui.grid.clear(); ";
                  //  echo "w2ui.grid.clear();";
                      echo "console.log('ds'); ";
                    // echo   "w2ui.layout.content('main',  w2ui.grid); ";
                      echo "$('#gbod').w2render('grid')";
                      echo "
                      var NumberofLines;
                      $.ajax({
                            url: "."'ajax/files_size.php',
            					      type: "."'POST'".',
            					      data: { DisplayedRecords:'. "'".$scanned_directory[$i]."'"." },
            					      success: function(response) {
            					      	NumberofLines= response-1;
                                console.log(response);
            									}
            								});

                      $.ajax({
                            url: "."'ajax/open_file_data.php',
                            type: "."'POST'".',
                            data: {'."'data'".":'".$scanned_directory[$i]."'".'}'.',
                            dataType:'. '"json"'. ',
                            success: function(data) {
                              w2ui.grid.clear();
                              for(x=0; x<	NumberofLines; x++){
                                if (data[x][2] != home_id){
                                    data[x][3] = '."'-'".';
                                    data[x][5] = '."'-';".'
                                  }
                                w2ui['."'grid'".'].records.push({
                                  recid : x+1,
                                   id: x+1,
                                  rssi: data[x][1],
                                  data: data[x][0],
                                  source: data[x][3],
                                  route: data[x][12],
                                  destination: data[x][5],
                                 command: data[x][7],
                                 h_id: data[x][3],
                                 });

                            }
                          }
                        });';

                      echo  "break; ";
                   }
                 ?>
            }
            //w2popup.close();

        }
    },
  /*  grid: {
        name: 'grid',
        style: 'border: 0px; border-left: 1px solid silver',
        show: {
            toolbar    : true
        },
        columns: [
            { field: 'id', caption: 'ID', size: '5%', sortable: true, searchable: 'int', resizable: true,  attr: "align=center" },
						{ field: 'data', caption: 'Date', size: '20%', sortable: true, searchable: 'text', resizable: true, attr: "align=center" },
            { field: 'rssi', caption: 'Rssi', size: '10%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },
            { field: 'source', caption: 'Source', size: '10%', resizable: true, searchable: 'int', sortable: true, attr: "align=center" },
            { field: 'route', caption: 'Route', size: '10%', resizable: true, sortable: true, searchable: 'text', attr: "align=center"},
            { field: 'destination', caption: 'Destination', size: '10%', resizable: true, sortable: true, attr: "align=center" },
	    			{ field: 'command', caption: 'Command', size: '35%', sortable: true, searchable: 'int', resizable: true, attr: "align=center" },
		//{ field: 'test2', caption: 'test2', size: '100px', type: "text", sortable: true, searchable: 'text',  resizable: true },
  ],

}*/
}

$(function () {
    // initialization in memory
    $().w2layout(config.layout);
    $().w2sidebar(config.sidebar);
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
});

function openPopup() {
    w2popup.open({
        title   : 'Popup',
        width   : 400,
        height  : 400,
        showMax : true,
        body    : '<div id="main" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px;"></div>',
        onOpen  : function (event) {
            event.onComplete = function () {
                $('#w2ui-popup #main').w2render('layout');
                w2ui.layout.content('left', w2ui.sidebar);
          //    w2ui.layout.content('main', w2ui.grid);
            }
        },
        onToggle: function (event) {
            event.onComplete = function () {
                w2ui.layout.resize();
            }
        }
    });
}

//$( "#body-w" ).load( "ajax/jsGrid.php" );
</script>

</body>
</html>
