
<?php require_once("inc/init.php"); ?>

<!-- row -->

<!-- end row -->

<!--
	The ID "widget-grid" will start to initialize all widgets below
	You do not need to use widgets if you dont want to. Simply remove
	the <section></section> and you can use wells or panels instead
	-->



	<div class="row">

		<!-- a blank row to get started -->
		<div class="col-sm-12">
			<!-- your contents here -->
<section id="widget-grid" class="">

		<div class="row">

			<article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">


			<div class="jarviswidget" id="wid-id-6"
			data-widget-deletebutton="false"

			data-widget-editbutton="false"
			data-widget-collapsed="false"
			data-widget-togglebutton="false"
			data-widget-colorbutton="false"
			data-widget-refreshbutton="false"
			>


			<header>
					<span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
					<h2>Network Health Tester</h2>

					<div class="widget-toolbar">
							<label class="btn btn-default btn-xs " id="addBtn"></i> Add
								<i class="fa fa-plus"></i>
							</label>

							<label class="btn btn-default btn-xs " id="removeBton"></i> Remove
									<i class="fa fa-trash-o"></i>
							</label>
							<label class="btn btn-default btn-xs " id="resetBtn"></i> Reset
									<i class="fa fa-refresh"></i>
							</label>
					</div>

									<div class="widget-toolbar">

						<div class="btn-group" data-toggle="buttons">
					        <label class="btn btn-default btn-xs " id="play-a2">
					          <input type="radio" name="style-a1" id="style-a1"> <i class="fa fa-play"></i> Start
					        </label>

					        <label class="btn btn-default btn-xs active" id="stop-a2">
					          <input type="radio" name="style-a2" id="style-a3"> <i class="fa fa-stop"></i> Stop
					        </label>
					    </div>



					</div>
				</header>



					<div class="widget-body">
					<div id="w-body2">
						Loading...
					</div>
					</div>

				</div>
				</article>


				<article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">





				<div class="jarviswidget" id="wid-id-4"
				data-widget-deletebutton="false"
				data-widget-editbutton="false"
				data-widget-collapsed="false"
				data-widget-togglebutton="false"
				data-widget-colorbutton="false"
				data-widget-resizable="true">


			<header>
					<span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
					<h2>Static Connections Table</h2>

					<div class="widget-toolbar">


						</div>
				</header>



					<div class="widget-body">
					<div id="controller-body">
					<center>
						Loading...
					</center>
					</div>
					</div>

				</div>

			</article>

		</div>

	</section>

		</div>

	</div>



<script type="text/javascript">

	var	$addBtn = $('#addBtn');

	$(document).ready(function() {
		load_health_tester();
		load_controller();

			//uncomment to pseudo-responsive
		//$(window).resize(function(){
        //	$( "#w-body2" ).load( "ajax/ima_table.php" );
        //	$("#controller-body").load("ajax/controller.php");
    	//	});

		});

	function refresh(){
		jQuery("#list2").trigger("reloadGrid");
}
	function load_health_tester(){
		$( "#w-body2" ).load( "ajax/ima_table.php" );
	}

	function load_controller(){
		$("#controller-body").load("ajax/controller.php");
	}

	$("#play-a2").click(function(){
				  myInterval = setInterval(load_health_tester, 2000);
	});

	$("#stop-a2").click(function(){

				   clearInterval(myInterval);

	});

	$addBtn.on('click', function(){
		console.log('click');
			$.ajax({
					url: 'ajax/addDevice.php',
					success: function () {
						console.log('Device added');
						load_health_tester()
					},
					error: function(err){
							console.log(err);
					}
			})

	})


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
	 * loadScript(".../plugin.js", function(){
	 * 	 loadScript("../plugin.js", function(){
	 * 	   ...
	 *   })
	 * });
	 */

	// pagefunction

	var pagefunction = function() {
		// clears the variable if left blank
	};

	// end pagefunction

	// run pagefunction
	pagefunction();

</script>
