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
							        <label class="btn btn-default btn-xs " id="start-a3">
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
		load();
	});

	function load(){
		$( "#spectrum-body" ).load( "ajax/script_spectrum.php" );
	}

	$("#play-a3").click(function(){
				  myInterval = setInterval(load, 2000);
				  $.SmartMessageBox({
				title : "Smart Notification: Buttons",
				content : "Lots of buttons to go...",
				buttons : '[Need?][You][Do][Buttons][Many][How]'
			});

			e.preventDefault();

	});

	$("#stop-a3").click(function(){



	});




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
