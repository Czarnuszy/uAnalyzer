<?php

	session_start();

	if (!isset($_SESSION['logged']))
	{
		header('Location: login.php');
		exit();
	}
////////////MM



?>


<html>
<head>
 <link rel="stylesheet" href="lib/js/themes/ui-lightness/jquery-ui.custom.css"></link>
    <link rel="stylesheet" href="lib/js/jqgrid/css/ui.jqgrid.css"></link>
    <script src="lib/js/jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
    <script src="lib/js/themes/jquery-ui.custom.min.js" type="text/javascript"></script>
    <script src="lib/js/notification/SmartNotification.min.js"></script>
  <script src="lib/js/jarvis.widget.min.js" type="text/javascript"></script>
  <script src="ajax/reloadJsGrid.js" type="text/javascript"></script>

</head>
<body>

	<script>
	function my_custom_link(cellvalue, options, rowObject)
	{
		return '<a href="mailto:'+cellvalue+'">'+cellvalue+'</a>';
	}


	</script>

	<section id="widget-grid" class="">

		<div class="row">

			<article class="col-xs-12">



				<div class="jarviswidget" id="wid-id-0"
				data-widget-deletebutton="false"
				data-widget-editbutton="false"
				data-widget-collapsed="false"
				data-widget-togglebutton="false"
				data-widget-colorbutton="false"
				data-widget-sortable="false">


			<header>

					<h2>Z-Wave Packet Analyzer </h2>
						<div class="widget-toolbar">
						 <label>
							 <input type="checkbox"  > <font face="Futura PT 300">Show Only My Network</font>
						 </label>

						</div>

					<div class="widget-toolbar">
						<button class="btn btn-default btn-xs" data-toggle="buttons" id="join-a1" ><i class="fa fa-search-plus"></i>
						Join
    					</button>


					</div>
						<div class="widget-toolbar">
							<label class="btn btn-default btn-xs " id="trash-a1"></i> Clear
						     <i class="fa fa-trash-o"></i>
						    </label>
						    <label class="btn btn-default btn-xs " id="save-a1"></i> Save
						    <i class="fa fa-save"></i>
						    </label>

						    <label class="btn btn-default btn-xs " id="refresh-a1"></i> Refresh
						    <i class="fa fa-refresh"></i>
						    </label>

						</div>



					<div class="widget-toolbar">

							<div class="btn-group" data-toggle="buttons">
					        <label class="btn btn-default btn-xs " id="play-a1">
					          <input type="radio" name="button" id="style-a1" value="start" > <i class="fa fa-play"></i> Capture
					        </label>

					       	<label class="btn btn-default btn-xs " id="pause-a1">
					          <input type="radio" name="button" id="style-a2" value="pause" > <i class="fa fa-pause"></i> Pause
					        </label>

					        <label class="btn btn-default btn-xs active " id="stop-a1">
					          <input type="radio" name="button" id="style-a3" value="stop" checked="checked" > <i class="fa fa-stop"></i> Stop
					        </label>

						</div>

					</div>


				</header>



				<div class="widget-body">
					<div id="body-w">
  					<center>
        		Loading Data...
        		</center>
      		</div>
		   </div>

				</div>
			</article>

		</div>

	</section>
</body>
</html>



<script type="text/javascript">

var radioButton;

 $('input').on('change', function() {
		radioButton = $('input[name=button]:checked').val();
		console.log(radioButton);
 });

	$(document).ready(function() {
		load();
  });


  function refresh(){
		var grid_rec = w2ui.grid.records.length;
		var NumberofLines;
		var form_data;

					$.ajax({
					      type: "POST",
					      url: "ajax/AnalyzerDataSize.php",
					      data: { DisplayedRecords: grid_rec },
					      success: function(response) {
					      	NumberofLines= response -1;
								//	count();
									}
								});


						 $.ajax({
							     url: "ajax/jsGridData.php",
							     type: 'POST',
							     data: form_data,
							     dataType:"json",
							     success: function(data) {
													console.log("File: " + NumberofLines);
													console.log("Grid: " + grid_rec);

										 if (NumberofLines -1>grid_rec){
											 console.log("more");

												for(x=grid_rec; x<NumberofLines; x++){
														grid_rec = w2ui.grid.records.length;

														w2ui['grid'].add({
														recid : grid_rec+1,
														 id: grid_rec+1,
														rssi: data[x][1],
														data: data[x][0],
														source: data[x][3],
														route: data[x][12],
														destination: data[x][5],
													 command: data[x][7],
													 });
											 }
										 }

							    }

							});
 }




	function load(){
		$( "#body-w" ).load( "ajax/jsGrid.php" );
	}

	function cleartrace(){

		$( "#body-w" ).load( "ajax/clear_trace.php" );
	}

  function start_analyzer(){

    $.get("ajax/stick.php");
    return false;

  }

  function stop_analyzer(){
    $.get("ajax/stop_analyzer.php");
    return false;
  }

  function count(){
    $.get("ajax/jsGridData.php");
    return false;
  }

	$("#play-a1").click(function(){

			if(radioButton == "stop"){
				console.log("start after stop");

			    w2ui.grid.clear();
					setTimeout(function(){
					      myInterval = setInterval(refresh, 200);
							}, 2000);
			    start_analyzer();
				}
				else if (radioButton == "pause") {
					console.log("start after pause");
					refresh();
					myInterval = setInterval(refresh, 200);
				}
				  $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>trace started</i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 3000
		    });

	});


	$("#pause-a1").click(function(){
		if(radioButton == 'start'){
				clearInterval(myInterval);
				console.log("pause after start");
			} else if(radioButton == 'stop') {
				clearInterval(myInterval);
				console.log("pause after stop");
			}
				  $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>refresh stopped</i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 3000
		    });

	});


	$("#stop-a1").click(function(){

		$.SmartMessageBox({
	title : "Z-Wave Packet Analyzer",
	content : "Are sure to stop? //add smth here",
	buttons : "[STOP][Cancel]",
			}, function(ButtonPress, Value) {

	if (ButtonPress === "STOP") {

		if(radioButton == "start"){
			console.log("stop after start k");
			clearInterval(myInterval);
			stop_analyzer();
		}else	if (radioButton == "pause") {
				console.log("stop after pause");
				stop_analyzer();

		}else	if (radioButton == "stop") {
				console.log("stop after stop");
				stop_analyzer();
				clearInterval(myInterval);
				refresh();
		}

		$.smallBox({
				title : "Z-Wave Packet Analyzer",
				content : "<i class='fa fa-clock-o'></i> <i>trace capture stopped</i>",
				color : "#C46A69",
				iconSmall : "fa fa-times fa-2x fadeInRight animated",
				timeout : 3000
			});
	}
	if (ButtonPress=== "Cancel") {
				$.smallBox({
						title : "Z-Wave Packet Analyzer",
						content : "<i class='fa fa-clock-o'></i> <i>trace in progres</i>",
						color : "#659265",
						iconSmall : "fa fa-times fa-2x fadeInRight animated",
						timeout : 3000
					});
					}
		});



	});

	$("#refresh-a1").click(function(){
  w2ui.grid.clear();
		$.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>I am here </i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 4000
		    });
	    	$(document).ready(function() {
			load();
			});

	});


	$("#save-a1").click(function(e) {

	    $.SmartMessageBox({
		title : "Z-Wave Packet Analyzer",
		content : "Please enter filename",
		buttons : "[Cancel][Save]",
		input : "text",
		placeholder : "Enter filename"
	    }, function(ButtonPress, Value) {

			if (ButtonPress === "Save") {

			//$.post("ajax/savetrace.php?filename="+Value");
			$( "#body-w" ).load( "ajax/savetrace.php?filename=" + Value);
			//.location.href = "ajax/savetrace.php?filename=" + Value; //save file
    			//var req = new Request({url: 'ajax/savetrace.php?filename='+Value});
			//req.send();

		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i>Trace saved to file :</i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 2000
		    });

		}
		if (ButtonPress=== "Cancel") {
		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Aborted...</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}

	    });

	    e.preventDefault();
	})
	   $("#delete-a1").click(function(e) {

	    $.SmartMessageBox({
		title : "Z-Wave Packet Analyzer",
		content : "Are sure to clear trace",
		buttons : "[OK][Cancel]",
		placeholder : "Enter filename"
	    }, function(ButtonPress, Value) {

		if (ButtonPress === "OK") {

		    cleartrace();

		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Trace Cleared </i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}
		if (ButtonPress=== "Cancel") {
		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Aborted...</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}
	    });




	    e.preventDefault();
	})

	    $("#join-a1").click(function(e) {

	    $.SmartMessageBox({
		title : "Z-Wave Packet Analyzer",
		content : "Please put controller into Add Device mode",
		buttons : "[Continue][Cancel]",
		placeholder : "Enter filename"
	    }, function(ButtonPress, Value) {

		if (ButtonPress === "Continue") {

		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Looking for new My Network</i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}
		if (ButtonPress=== "Cancel") {
		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Join Canceled...</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}
	    });

	    e.preventDefault();
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



	/*
	* SmartAlerts
	*/

	// With Callback

	$("#trash-a1").click(function(e) {
	    $.SmartMessageBox({
		title : "Z-Wave Packet Analyzer",
		content : "Are you sure to clear trace!!!!",
		buttons : '[Cancel][Yes]'
	    }, function(ButtonPressed) {
		if (ButtonPressed === "Yes") {
		    //ClearFile();
		   	cleartrace();
		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Trace cleared</i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}
		if (ButtonPressed === "Cancel") {
		    $.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>Aborted</i>",
			color : "#C46A69",
			iconSmall : "fa fa-times fa-2x fadeInRight animated",
			timeout : 4000
		    });
		}

	    });
	    e.preventDefault();
	})

	};

	// end pagefunction

	// run pagefunction
	pagefunction();

</script>
