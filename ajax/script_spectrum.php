
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
      if( $AnalyzerData[$i][1] > $MaxAnalyzerData[$i][1])
        	$MaxAnalyzerData[$i][1] = $AnalyzerData[$i][1];

}

$file = fopen($csvFileMax, "w") or die("Unable to open file!");
  for ($i=0; $i < $max; $i++) {
    $txt =  $AnalyzerData[$i][0].','. $MaxAnalyzerData[$i][1]."\n";
    fwrite($file, $txt);
}

fclose($file);

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
					  label: "My First dataset",
		        backgroundColor: "rgba(60,96,139,1)",
            lineTension: 0.1,
            spanGaps: true,

					data : [
    						<?php
    						    for ($i=0; $i < $max; $i++){
						    echo $AnalyzerData[$i][1].',';
						     }
    						?>
              ]
				},
        {
					  label: "My secnd dataset",
		        borderColor: "rgba(260,5,8,1)",
            lineTension: 0.1,
            spanGaps: true,

					data : [
    						<?php
    						    for ($i=0; $i < $max; $i++){
						    echo $MaxAnalyzerData[$i][1].',';
						     }
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
      pointHitDetectionRadius : 1,
      //tooltipTemplate: "<%if (label){%><%=label%>MHz <%}%><%= value %>",
      tooltipTemplate: "<%if (label){%><%=value%>%  at <%}%> <%= label %>MHz",
      //tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= value %>",'
      scaleOverride: true,
      animation : false,

    // ** Required if scaleOverride is true **
    // Number - The number of steps in a hard coded scale
    scaleSteps: 10,
    // Number - The value jump in the hard coded scale
    scaleStepWidth: 10,
    // Number - The scale starting value
    scaleStartValue: 0,
    scaleLabel: "<%=value%>%",
    scaleShowLabels : true,
    valueShowLabels :false,
    showXLabels: 10,


    hover: {
                   mode: 'dataset'
               },
               scales: {
                   xAxes: [{
                       display: true,
                       scaleLabel: {
                           display: false,
                           labelString: 'Something'
                       }
                   }],
                   yAxes: [{
                       display: true,
                       scaleLabel: {
                           display: false,
                           labelString: 'Value',
                       },
                       ticks: {
                           suggestedMin: 0,
                           suggestedMax: 100,
                       }
                   }]
               }

    }

		}
    var randomColorFactor = function() {
          return Math.round(Math.random() * 255);
      };

    var randomColor = function(opacity) {
            return 'rgba(' + randomColorFactor() + ',' + randomColorFactor() + ',' + randomColorFactor() + ',' + (opacity || '.3') + ')';
        };
        var randomScalingFactor = function() {
                  return Math.round(Math.random() * 100);
                  //return 0;
              };
    $.each(config.data.datasets, function(i, dataset) {
    //         dataset.borderColor = randomColor(0.4);
            // dataset.backgroundColor = "rgba(60,96,139,1)";
        //     dataset.pointBorderColor = randomColor(0.7);
        //     dataset.pointBackgroundColor = randomColor(0.5);
          //   dataset.pointBorderWidth = 1;
    //      dataset.data = dataset.data.map(function() {
            //        randomScalingFactor();
         });

         function c(radioButton) {
           if(radioButton == "chek"){
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
         }
         var  radioButton= "";
      var pagefunction = function() {

  			var ctx = document.getElementById("canvas").getContext("2d");
  			window.myLine = new Chart(ctx, config);
        $('input').on('change', function() {
          radioButton = $('input[name=max_checkbox]:checked').val();
            console.log(radioButton);
        //    c(radioButton);
         });

	   }


  /*$('#max_checkbox').click(function() {
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
*/
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
