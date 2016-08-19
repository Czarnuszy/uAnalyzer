
<?php
function readCSV($csvFile){
   $file_handle = fopen($csvFile, 'r');
   while (!feof($file_handle) ) {
      $line_of_text[] = fgetcsv($file_handle, 1024);
 }
 fclose($file_handle);
 return $line_of_text;
}
//open file//read data to firt grid//opem secend file//read max data to second grid//copy first file to second file

function saveMax($csvFileMax){


}

$csvFile = '../zniffer/data/AnalyzerData.csv';
$AnalyzerData = readCSV($csvFile);
$max = count($AnalyzerData)-1;

for ($i=0; $i < $max; $i++) {
# c$AnalyzerData.sizeofode..$AnalyzerData.sizeof.
   $AnalyzerData[$i][0] = (float)$AnalyzerData[$i][0];
   $AnalyzerData[$i][0] = (int)$AnalyzerData[$i][0]/1000;
   $rssi = $AnalyzerData[$i][1];
     	$rssi = $rssi * 1.7;
     	$rssi = $rssi - 30;
     	$rssi = (int) $rssi;
     	if ($rssi> 100) $rssi = 100;
     	$AnalyzerData[$i][1] = $rssi;
}

$csvFileMax = '../zniffer/data/MaxAnalyzerData.csv';
$MaxAnalyzerData = readCSV($csvFileMax);
$maxM = count($MaxAnalyzerData)-1;

for ($i=0; $i < $max; $i++) {
  $MaxAnalyzerData[$i][0] = (float)$MaxAnalyzerData[$i][0];
  $MaxAnalyzerData[$i][0] = (int)$MaxAnalyzerData[$i][0]/1000;
  $mrssi = $MaxAnalyzerData[$i][1];
     $mrssi = $mrssi * 1.7;
     $mrssi = $mrssi - 30;
     $mrssi = (int) $mrssi;
     if ($mrssi> 100) $mrssi = 100;
     $MaxAnalyzerData[$i][1] = $mrssi;
}



//copy($csvFile, $csvFileMax);

?>

<div class="widget-body">

					<div class="row">

		<!-- a blank row to get started -->
		<div class="col-sm-12">
			<!-- your contents here -->
            <div>
				<canvas id="canvas" height="350" width="800"></canvas>
			</div>

		</div>

	</div>



	</div>




 <script  type="text/javascript">



		var config = {
      type: 'line',
      data: {
			labels : [
				    <?php
					for ($i=0; $i < $max; $i++){
			          echo $AnalyzerData[$i][0].',';
          }
				    ?>
				],

			datasets : [
				{
					  label: "RSSI",
		        backgroundColor: "rgba(60,96,139,1)",
            lineTension: 0.5,
            spanGaps: true,
            pointRadius: 0,
            pointHoverRadius: 5,

					data : [
    						<?php
    						    for ($i=0; $i < $max; $i++){
						    echo $AnalyzerData[$i][1].',';
						     }
    						?>
              ]
				},
        {
					  label: "Max Hold RSSI",
		        borderColor: "rgba(260,5,8,1)",
            lineTension: 0.5,
            spanGaps: true,
            pointRadius: 0,
            pointHoverRadius: 5,

					data : [
    						<?php
    						    for ($i=0; $i < $max; $i++){
						    echo $MaxAnalyzerData[$i][1].',';
						     }
    						?>
              ]
				},
        {
          label: "Z-Wave Channels",
          borderColor: "rgba(198,157,39,51)",
      //    spanGaps: true,
          pointRadius: 0,
    //      pointHoverRadius: 5,
          data:[
            <?php
                for ($i=0; $i < 36; $i++)
                  echo ' '.',';
                for ($i=36; $i < 42; $i++)
                  echo '100'.',';
                for ($i=42; $i < 74; $i++)
                  echo ' '.',';
                for ($i=74; $i < 80 ; $i++)
                  echo '100'.',';
            ?>
          ]

        },

      ],
    },
    options: {


      backgroundColor: "rgba(60,96,139,1)",

      responsive: true,
       //Boolean - Whether to show horizontal lines (except X axis)
      scaleShowHorizontalLines: false,
      //Boolean - Whether to show vertical lines (except Y axis)
      scaleShowVerticalLines: false,
      //Boolean - Whether to show a dot for each point
      pointDot : false,
      pointHitDetectionRadius : 5,
      //tooltipTemplate: "<%if (label){%><%=label%>MHz <%}%><%= value %>",
      //tooltipTemplate: "<%if (label){%><%=value%>%  at <%}%> <%= label %>MHz",

      tooltips:{

                mode: 'single',
                backgroundColor: 'rgba(60,96,139,0.8)',
              //  tooltipTemplate: "<%if (label){%><%=label%>::: <%}%><%= value %> ",
                    //          multiTooltipTemplate: "<%= datasetLabel %> - <%= datasetLabel %>",


      },


      scaleOverride: true,
      animation : false,

    // ** Required if scaleOverride is true **
    // Number - The number of steps in a hard coded scale
    scaleSteps: 10,
    // Number - The value jump in the hard coded scale
    scaleStepWidth: 10,
    // Number - The scale starting value
    scaleStartValue: 0,
    scaleLabel: "<%=value%> %",
    scaleShowLabels : true,
    valueShowLabels :false,
    showXLabels: 10,


    hover: {
                //   mode: 'dataset'
                mode: 'x-axis'
               },
               scales: {
                   xAxes: [{
                       display: true,

                       scaleLabel: {
                           display: true,
                           labelString: 'Frequency [MHz]'
                       }
                   }],
                   yAxes: [{
                       display: true,
                       scaleLabel: {
                           display: true,
                           labelString: 'RSSI [%]',
                       },
                       ticks: {
                           suggestedMin: 0,
                           suggestedMax: 100,
                       }
                   }]
               }

    }

		}


      var pagefunction = function() {

  			var ctx = document.getElementById("canvas").getContext("2d");
  			window.myLine = new Chart(ctx, config);

	   }


  $('#max_checkbox').click(function() {
  	if(this.checked){
      if(config.data.datasets.length == 1){
    		var maxDataSet = {
          label: 'MaxData',
          borderColor	: "rgba(650,96,10,1)",
  				data : [
            <?php
            for ($i=0; $i < $max; $i++)
              echo $MaxAnalyzerData[$i][1].',';
             ?>  ],
         }
        config.data.datasets.push(maxDataSet);
        window.myLine.update();
      }
  	}
  	else{
      if(config.data.datasets.length == 2){
        config.data.datasets.splice(1, 2);
        window.myLine.update();
      }
    }
    });

	</script>



<script type="text/javascript">


	/* DO NOT REMOVE : GLOBAL FUNCTIONS!
	 *
	 * pageSetUp(); WILL CALL THE FOLLOWING FUNCTIONS
	 *
	 * // activate tooltips
	 * $("[rel=tooltip]").tooltip();
	 *
	 * // activate popovers
	 * $("[rel=popover]").popover();
	 *
	 * // activate popovers with hover states
	 * $("[rel=popover-hover]").popover({ trigger: "hover" });
	 *
	 * // activate inline charts
	 * runAllCharts();
	 *
	 * // setup widgets
	 * setup_widgets_desktop();
	 *
	 * // run form elements
	 * runAllForms();
	 *
	 ********************************
	 *
	 * pageSetUp() is needed whenever you load a page.
	 * It initializes and checks for all basic elements of the page
	 * and makes rendering easier.
	 *
	 */

	pageSetUp();

	/*
	 * ALL PAGE RELATED SCRIPTS CAN GO BELOW HERE
	 * eg alert("my home function");
	 *
	 * var pagefunction = function() {
	 *   ...
	 * }
	 * loadScript("js/plugin/_PLUGIN_NAME_.js", pagefunction);
	 *
	 * TO LOAD A SCRIPT:
	 * var pagefunction = function (){
	 *  loadScript(".../plugin.js", run_after_loaded);
	 * }
	 *
	 * OR you can load chain scripts by doing
	 *
	 * loadScript("js/Chart.js", function(){
	 * 	 loadScript("../plugin.js", function(){
	 * 	   ...
	 *   })
	 * });
	 */

	// pagefunction



	// end pagefunction

	// run pagefunction
	pagefunction();



</script>
