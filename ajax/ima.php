
<?php require_once("inc/init.php"); ?>
<head>
  <script src="../js/parser.js"></script>

</head>
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


	                    <div class="jarviswidget" id="wid-id-6" data-widget-deletebutton="false"
											data-widget-editbutton="false" data-widget-collapsed="false" data-widget-colorbutton="false"
											 data-widget-refreshbutton="false">


	                        <header id="healthHeader">
	                            <span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
	                            <h2>Device List</h2>

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

											<div class="jarviswidget" id="wid-id-3" data-widget-deletebutton="false"
											 data-widget-editbutton="false" data-widget-collapsed="false"
											 data-widget-colorbutton="false" data-widget-resizable="true">


													<header>
															<span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
															<h2>Connections Tester</h2>

															<div class="widget-toolbar">
																	<label class="btn btn-default btn-xs " id="getStatusBtn"></i> Load
																		<i class="fa fa-refresh"></i>
																</label>

															</div>
													</header>



													<div class="widget-body">
															<div id="status-table-body">
																	<center>
																			Loading...
																	</center>
															</div>
													</div>

											</div>



	                </article>



	                <article class="col-xs-12 col-sm-6 col-md-6 col-lg-6">

	                    <div class="jarviswidget" id="wid-id-4" data-widget-deletebutton="false"
											data-widget-editbutton="false" data-widget-collapsed="false" data-widget-colorbutton="false"
											data-widget-resizable="true">


	                        <header>
	                            <span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
	                            <h2>Static Connections Table</h2>

	                            <div class="widget-toolbar">
	                                <label class="btn btn-default btn-xs " id="routingRefresh"></i> Refresh
												              <i class="fa fa-refresh"></i>
												          </label>
                                  <label class="btn btn-default btn-xs " id="updateNeighbors"></i> Update neighbors
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

											<div class="jarviswidget" id="wid-id-5" data-widget-deletebutton="false"
											data-widget-editbutton="false" data-widget-collapsed="false" data-widget-colorbutton="false"
											data-widget-resizable="true">


													<header>
															<span class="widget-icon"> <i class="fa fa-stethoscope"></i> </span>
															<h2> Netwok Health Tester</h2>

															<div class="widget-toolbar">
																	<label class="btn btn-default btn-xs " id="testBtn"></i> Test
																			<i class="fa fa-refresh"></i>
																	</label>
                                  <label class="btn btn-default btn-xs " id="stopTestBtn"></i> Stop
                                      <i class="fa fa-stop"></i>
                                  </label>
															</div>
													</header>



													<div class="widget-body">
															<div id="test-widget-body">
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
			var $learnBtn = $el.find('#learnBtn');
			var $refreshBtn = $el.find('#refreshBtn');


			//bind events
			$addBtn.on('click', onAddClick);
			$rmvBtn.on('click', onRmvClick);
			$resetBtn.on('click', onResetClick);
			$learnBtn.on('click', onLearnClick);
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
          connectionTable.refresh();
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
      var $neightUpdateBtn = $('#updateNeighbors');
			var $refreshBtn = $('#routingRefresh');
			var $body = $('#controller-body');

    //  $neightUpdateBtn.toggle();
      $neightUpdateBtn.attr('disabled', true);


			//Bind events
			$refreshBtn.on('click', onRefreshClick);
      $neightUpdateBtn.on('click', onUpdateClick);

			load_controller();
			var spinnerHTML = '<i class="'+'fa fa-spinner fa-spin fa-3x fa-fw"'+'></i>';

			function onRefreshClick() {
					$body.html(spinnerHTML);
					console.log('click');
					healthTester.startIMA('routingInf', load_controller);
			}

      function onUpdateClick() {
          console.log(selectedDevId);
          $body.html(spinnerHTML);
          startIMA(selectedDevId, function () {
          healthTester.startIMA('routingInf', load_controller);
        });

     }

			function load_controller(){
					console.log('loaded');
					$body.load("ajax/controller.php");

			}

      function startIMA(_req, onSuccess) {
          $.ajax({
              url: 'ajax/send_neight_update.php',
              type: 'POST',
              data: {req: _req},
              success: onSuccess,
              error: healthTester.errorFun
          })
      }

      return{
        refresh: onRefreshClick,
      }
	})();


var statusTable = (function () {
	var $body = $('#status-table-body');


	$body.load("ajax/status_table.php");

  function fillGrid() {
      $body.load("ajax/status_table.php");
  //  devicesStatus.fillGrid();
  }

  return{
      fillGrid: fillGrid,
  }


})();


var testDevice = (function () {

    var $body = $('#test-widget-body');
    var $testBtn = $('#testBtn');
    var $stopBtn = $('#stopTestBtn')

    $testBtn.attr('disabled', true);
    $stopBtn.attr('disabled', true);

    $body.load("ajax/test_device.php");

    $testBtn.on('click', onTestClick);
    $stopBtn.on('click', onStopClick);

    var current = 0;

    function onTestClick() {
      if (record != 'none')
      {
          $testBtn.attr('disabled', true);
          $stopBtn.attr('disabled', false);
          w2ui['testDevGrid'].lock('In progress', true);
          size = w2ui['testDevGrid'].records.length;
          allrec = [];
          for (var i = 0; i < size; i++)
            allrec.push(w2ui['testDevGrid'].get(i));

          console.log('hue' + record.dev);
          recordid = record.recid;
          var i = 0;
          get_dev_status();
          devStatusTab = [];

            function get_dev_status() {

              if (current < 60) {
                dev = record.dev;
                  $.ajax({
                      url: 'ajax/send_dev_req.php',
                      type: 'POST',
                      data: {dev: dev},
                      success: function (resp) {
                        if (resp == 'done') {
                          devid = dev;
                          $.ajax({
                              url: 'data/ima/device_status.csv',
                              success: function (stat) {
                                  w2ui['testDevGrid'].unlock();
                                  stat = parse.CSVToArray(stat);

                                  devStatusTab.push(stat[0][0]);
                                  console.log(devStatusTab);

                                  progres = parseInt(current/60*100) + '% | ' + testStatus(devStatusTab);
                                  allrec[recordid].result = progres;
                                  w2ui['testDevGrid'].clear();

                                  for (var i = 0; i < size; i++) {
                                    w2ui['testDevGrid'].records.push({
                                      recid: i,
                                      dev: allrec[i].dev,
                                      specific:  allrec[i].specific,
                                      result: allrec[i].result,
                                    });
                                  }
                                  w2ui['testDevGrid'].reload();

                                    current++;
                                    i++;
                                    get_dev_status();
                              }
                          })
                        }

                      },
                      error: function () {
                          console.log('error');
                      }
                  })


                }

            function testStatus(data) {
                fails = 0;
                success = 0;
                for (var i = 0; i < data.length; i++) {
                  if (data[i] == "False")
                    fails ++;
                  else if (data[i] == 'OK')
                    success++;
                }
              return success + '/' + data.length + ' OK';
            }
          }
        }else {
          alert("Select device");
        }
    }

    function onStopClick() {
        current = 60;
        $stopBtn.attr('disabled', true);
        $testBtn.attr('disabled', false);

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
