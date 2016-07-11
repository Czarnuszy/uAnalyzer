<?php

$base=mysqli_connect("localhost","root","","analyzer");

$records = mysqli_query($base,"SELECT * FROM analyzerdata");

$i=0;
while($row = mysqli_fetch_array($records))
{
 $AnalyzerData[$i][0] = $row[1]/1000;
 $rssi = $row[2] ;
   	$rssi = $rssi * 1.7;
   	$rssi = $rssi - 30;
   	$rssi = (int) $rssi;
 if ($rssi> 100) $rssi = 100;
   	$AnalyzerData[$i][1] = $rssi;

    $i++;
}

$max = count($AnalyzerData)-1;

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



		var lineChartData = {
			labels : [
				    <?php
					for ($i=0; $i < $max; $i++){
				echo $AnalyzerData[$i][0].',';
          //echo (int)$row['1']/1000 . ",";
          }
//          while($row = mysqli_fetch_array($wynik))

  //        {echo $row[0]/1000 . ","; }


				    ?>
				],
			datasets : [
				{
					label: "My First dataset",
					fillColor : "rgba(0, 0, 255,1)",
					strokeColor : "rgba(220,220,220,1)",
					pointColor : "rgba(220,220,220,1)",
					pointStrokeColor : "#fff",
					pointHighlightFill : "#fff",
					pointHighlightStroke : "rgba(220,220,220,1)",
					data : [
    						<?php
    						    for ($i=0; $i < $max; $i++){
						    echo $AnalyzerData[$i][1].',';

						     }
              //  while($row = mysqli_fetch_array($wynik))

                //{
                  //echo (int)$row['2']*1.7-30 . ","; }
    						?>
                        		    ]
				},

					]

		}

        	var pagefunction = function() {
			var ctx = document.getElementById("canvas").getContext("2d");
			window.myLine = new Chart(ctx).Line(lineChartData, {

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



		});
	}


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
