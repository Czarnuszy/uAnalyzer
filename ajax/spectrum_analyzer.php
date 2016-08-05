<?php

	session_start();

	if (!isset($_SESSION['logged']))
	{
		header('Location: login.php');
		exit();
	}

?>
<?php require_once("inc/init.php"); ?>


<!--
	The ID "widget-grid" will start to initialize all widgets below
	You do not need to use widgets if you dont want to. Simply remove
	the <section></section> and you can use wells or panels instead
	-->


<section id="widget-grid" class="">

		<div class="row">

			<article class="col-sm-12">

				<div class="jarviswidget" id="wid-id-1"
				data-widget-deletebutton="false"
				data-widget-refresh="true"
				data-widget-editbutton="false"
				data-widget-collapsed="false"
				data-widget-togglebutton="false"
				data-widget-colorbutton="false"
				data-widget-sortable="false">


					<header>

							<h2> Analyzer </h2>

							<div class="widget-toolbar">

								<div class="btn-group" data-toggle="buttons">
							        <label class="btn btn-default btn-xs " id="play-a3">
							          <input type="radio" name="style-a1" id="style-a1"> <i class="fa fa-play"></i> Capture
							        </label>


							        <label class="btn btn-default btn-xs active" id="stop-a3">
							          <input type="radio" name="style-a2" id="style-a3"> <i class="fa fa-stop"></i> Stop
							        </label>


							    </div>
							    <div class="btn-group" data-toggle="buttons2">
					       			<label class="btn btn-default btn-xs " id="trash-a1"> Clear
					           		<i class="fa fa-trash-o"></i>
					        		</label>
								</div>

								<div class="widget-toolbar">
								 <label>
									 <input type="checkbox" id="max_checkbox" name = "max_checkbox" value="check"> <font face="Futura PT 300">Show Max</font>
								 </label>
								</div>

							</div>


					</header>



					<div class="widget-body">

						<div id="spectrum-body">
							loading...
						</div>

					</div>

				</div>
			</article>

		</div>

	</section>




<script type="text/javascript">
var pagefunction = function() {

	$(document).ready(function() {
		$( "#spectrum-body" ).load( "ajax/script_spectrum.php" );

		//load();


	});
	var randomColorFactor = function() {
				return Math.round(Math.random() * 255);
		};

	function load(){
	//	$('#max_checkbox').click();
	$.ajax({
				url: "ajax/spectrum_data.php",
				type: 'POST',
				data: { fileName: "data" },
				dataType:"json",
				success: function(data) {
					console.log([data[2][1]]);
					 for (var i = 0; i < data.length; i++) {
						 window.myLine.data.datasets[0].data[i] = data[i][1];
					 }

					window.myLine.update();


				}

		});
		//var indexToUpdate = Math.round(Math.random() * 10);

	//	window.myLine.data.datasets[0].data[3] = Math.random() * 100;
	//	window.myLine.update();

		//config.data.datasets[0]=44;
/*		$.each(config.data.datasets, function(i, dataset) {
							dataset.data = dataset.data.map(function() {
									return 11;
							});
		            window.myLine.update();
            });

*/
		$.ajax({
					url: "ajax/spectrum_data.php",
					type: 'POST',
					data: { fileName: "d" },
					dataType:"json",
					success: function(data) {
						console.log("max" + data[2][1]);

						 for (var i = 0; i < data.length; i++) {
							 window.myLine.data.datasets[1].data[i] = data[i][1];
						 }
					//if(radioButton == "check"){
						window.myLine.update();
				//	}

					}
			});

	}

	$("#play-a3").click(function(){
			start_spectrum();
		  myInterval = setInterval(load, 1000);
			$.smallBox({
          title : "Z-Wave Spectrum Analyzer",
      		content : "<i class='fa fa-clock-o'></i> <i>Start</i>",
      		color : "#659265",
      		iconSmall : "fa fa-times fa-2x fadeInRight animated",
      		timeout : 3000
		    });


	});

	$("#stop-a3").click(function(){
		stop_spectrum();
		clearInterval(myInterval);
		$.smallBox({
				title : "Z-Wave Spectrum Analyzer",
				content : "<i class='fa fa-clock-o'></i> <i>Stop</i> REMEMBER ABOUT RESET",
				color : "#C46A69",
				iconSmall : "fa fa-times fa-2x fadeInRight animated",
				timeout : 3000
			});
	});

	function start_spectrum(){
    $.get("ajax/start_spectrum.php");
    return false;
  }

	function stop_spectrum(){
		$.get("ajax/stop_spectrum.php");
		return false;
	}

	/*
		* SmartAlerts
		*/

		// With Callback



}

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
