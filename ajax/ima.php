
<?php require_once("inc/init.php"); ?>

<!-- row -->

<!-- end row -->

<!--
	The ID "widget-grid" will start to initialize all widgets below
	You do not need to use widgets if you dont want to. Simply remove
	the <section></section> and you can use wells or panels instead
	-->
	<script src="../js/parser.js"></script>



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


			<header id="healthHeader">
					<span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
					<h2>Network Health Tester</h2>

					<div class="widget-toolbar">
							<label class="btn btn-default btn-xs " id="refreshBtn"></i> Refresh
									<i class="fa fa-refresh"></i>
							</label>
					</div>

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
							<label class="btn btn-default btn-xs " id="learnBtn"></i> Learn
									<i class="fa fa-refresh"></i>
							</label>
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
						<label class="btn btn-default btn-xs " id="routingRefresh"></i> Refresh
								<i class="fa fa-refresh"></i>
						</label>

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
	<style>
		#controller-body {

			height: 600px;
		}
	</style>


<script type="text/javascript">


	var healthTester = (function () {

			//cache DOM
			var $el = $('#healthHeader');
			var	$addBtn = $el.find('#addBtn');
			var $rmvBtn = $el.find('#removeBton');
			var $resetBtn = $el.find('#resetBtn');
			var $learbBtn = $el.find('#learbBtn');
			var $refreshBtn = $el.find('#refreshBtn');


			//bind events
			$addBtn.on('click', onAddClick);
			$rmvBtn.on('click', onRmvClick);
			$resetBtn.on('click', onResetClick);
			$learbBtn.on('click', onLearnClick);
			$refreshBtn.on('click', onRefreshClick);

			load();
	//		startIMA('nodeInf', load);

			function load(){
					$( "#w-body2" ).load( "ajax/ima_table.php" );
					console.log('loadin health test');
			}

			function onAddClick() {
					w2ui.NodeInfoGrid.lock("Please wait.", true);
					console.log('click');
					startIMA('add', reloadGridCallback);
			}

			function onRmvClick() {
					console.log('rmv click');
					w2ui.NodeInfoGrid.lock("Please wait.", true);
					startIMA('rm', reloadGridCallback);
			}

			function onResetClick() {
					console.log('reset click');
					w2ui.NodeInfoGrid.lock("Please wait.", true);
					startIMA('reset', reloadGridCallback);
			}

			function onLearnClick() {
					console.log('learn click');
					w2ui.NodeInfoGrid.lock("Please wait.", true);
					startIMA('learn', reloadGridCallback);
			}

			function onRefreshClick() {
					console.log('refresh click');
					w2ui.NodeInfoGrid.clear();
					w2ui.NodeInfoGrid.lock("Please wait.", true);
					startIMA('nodeInf', loadNodeInfoCallback);
			}

			function loadNodeInfoCallback() {
					console.log('node info loaded');
					load();
					w2ui.NodeInfoGrid.unlock();

			}

			function reloadGridCallback() {
					console.log('done');
					w2ui.NodeInfoGrid.clear();
					startIMA('nodeInf', loadNodeInfoCallback);
			}


			function startIMA(_req, onSuccess) {
					$.ajax({
							url: 'ajax/startIMA.php',
							type: 'POST',
							data: {req: _req},
							success: onSuccess,
							error: errorFun
					})
			}

			function errorFun(xhr, status, error) {
					var err = eval("(" + xhr.responseText + ")");
					console.log(xhr + " " + status + " " + error);
			}

			return{
					startIMA: startIMA,
					error: errorFun
			}

	})();


	var connectionTable = (function () {

			//cache DOM
			var $refreshBtn = $('#routingRefresh');
			var $body = $('#controller-body');

			//Bind events
			$refreshBtn.on('click', onRefreshClick);


			load_controller();
			var spinnerHTML = '<i class="'+'fa fa-spinner fa-spin fa-3x fa-fw"'+'></i>';

			function onRefreshClick() {
					$body.html(spinnerHTML);
					console.log('click');
					healthTester.startIMA('routingInf', load_controller);
			}

			function load_controller(){
					console.log('loaded');
					$body.load("ajax/controller.php");
			}
	})();



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
