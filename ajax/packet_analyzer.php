<?php

	session_start();

	if (!isset($_SESSION['logged']))
	{
		header('Location: login.php');
		exit();
	}
////////////MM

  $directory = '../data/Saves';
  $scanned_directory = array_diff(scandir($directory), array('..', '.'));
  $amount_files = count($scanned_directory);

	$fileID = fopen("../zniffer/data/id.txt", "r") or die("Unable to open file!");
	$homeid = fgets($fileID);
	fclose($fileID);



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
	<link rel="stylesheet" type="text/css" href="ajax/w2ui/w2ui-1.4.3.min.css" />
	<script type="text/javascript" src="ajax/w2ui/w2ui-1.4.3.min.js"></script>

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

			<article class="col-sm-12">

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
							 <input type="checkbox" id="network_checkbox" name = "network_checkbox" > <font face="Futura PT 300">Show Only My Network</font>
						 </label>
						</div>

						<div class="widget-toolbar">

								<label class="btn btn-default btn-xs " id="open"></i> <i class="fa fa-search-plus"></i>    Open

									</label>

						</div>
					<div class="widget-toolbar">

							<label class="btn btn-default btn-xs " id="join-a1"></i> <i class="fa fa-search-plus"></i>    Join

								</label>

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
					          	<input type="radio" name="button" id="style-a3" value="stop" checked= true > <i class="fa fa-stop"></i> Stop
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
var home_id= <?php  echo "'".$homeid."'"; ?>;

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
											 grid_rec = w2ui.grid.records.length;
												for(x=grid_rec; x<NumberofLines; x++){
														if (data[x][2] != home_id){
																data[x][3] = '-';
																data[x][5] = '-';
															}
														  w2ui['grid'].add({
																	recid : grid_rec,
																	id: grid_rec,
																	rssi: data[x][1],
																	data: data[x][0],
																	source: data[x][3],
																	route: data[x][12],
																	destination: data[x][5],
																  command: data[x][7],
																	h_id: data[x][2],
												 	 								 });
																			 grid_rec++;

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

	$("#open").click(function(){
//		$( "#body-w" ).load( "ajax/open_files.php" );
	//	$( "#body-w" ).load( "ajax/jsGrid.php" );
		//	$.get("ajax/open_files.php");
		openPopup();


	});


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
	else if (ButtonPress=== "Cancel") {
//		$("#style-a3").prop("checked", false);
//		$("#style-a1").prop("checked", true);
//		document.getElementById("style-a3").checked = false;

//		document.getElementById("style-a1").checked = true;

//		var $radios = $('input:radio[name=button]');
//		$radios.filter('[value=start]').prop('checked', true);
$('input:radio[name="button"]').filter('[value="stop"]').attr('checked', false);

		$('input:radio[name="button"]').filter('[value="start"]').attr('checked', true);

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


$('#network_checkbox').click(function() {
	if(this.checked){
		w2ui['grid'].search('h_id', home_id);

	}
	else
		w2ui['grid'].search('h_id', '');
	/*	grid_rec = w2ui.grid.records.length;
		var recs = w2ui['grid'].find({ h_id: home_id });

			for(x=1; x<grid_rec; x++){
				if($.inArray(x, recs) == -1 )
					w2ui['grid'].set(x, { source: '-', destination: '-' });
				}*/
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
	    //	$(document).ready(function() {
			load();
//			});

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

				start_analyzer();
				w2ui.grid.lock('In progres', true);
				if(home_id === "empty")
					hid_interval = setInterval(get_homeid, 2000);
				else {
					clearInterval(hid_interval);
				}
				//	while (home_id === "empty") {
				//		get_homeid();
				//	}

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
/////////////////////////////////////////////////////////////
var config = {
    layout: {
        name: 'layout',
        padding: 0,
        panels: [
        ///    { type: 'top', size: 32, content: '<div style="padding: 7px;">Your Sniffers</div>', style: 'border-bottom: 1px solid silver;' },
            { type: 'left', size: 400, resizable: true, minSize: 120 },
        //    { type: 'main', minSize: 350, overflow: 'hidden' }
        ]
    },
    sidebar: {
        name: 'sidebar',
        nodes: [
            { id: 'general', text: 'Z wave	', group: true, expanded: true, nodes: [
              <?php
                for($i = 2; $i <= $amount_files; $i+=3)
                  echo "{ id: '".$scanned_directory[$i]."', text: '".$scanned_directory[$i]."', img: 'icon-page' },";
                ?>
            ]}
        ],
        onClick: function (event) {
            switch (event.target) {
                <?php
                    //echo "w2ui.grid.clear();";
                  for($i = 2; $i <= $amount_files; $i+=3){
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
																	url: "."'ajax/open_homeid.php',
																	type: "."'POST'".',
																	data: { fileName:'. "'".$scanned_directory[$i+1]."'"." },
																	success: function(response) {
																		home_id = response;
																		console.log(3432);
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
																 h_id: data[x][2],
                                 });

                            }
                          }
                        });';

                      echo  "break; ";
                   }
                 ?>
            }
        //    w2popup.close();

        }
    },

}

$(function () {
    // initialization in memory
    $().w2layout(config.layout);
    $().w2sidebar(config.sidebar);
    $().w2grid(config.grid);

});

function openPopup() {
    w2popup.open({
        title   : 'Your Sniffers',
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

function get_homeid(){

	var NumberofLines;
	var form_data;
	var grid_rec = w2ui.grid.records.length;

				$.ajax({
							type: "POST",
							url: "ajax/AnalyzerDataSize.php",
							data: { DisplayedRecords: grid_rec },
							success: function(response) {
								NumberofLines= response -1;
								}
							});

					 $.ajax({
								 url: "ajax/jsGridData.php",
								 type: 'POST',
								 data: form_data,
								 dataType:"json",
								 success: function(data) {
											for(x=0; x<NumberofLines; x++){
													if(data[x][7].startsWith("FF 01 08 01")){
														home_id = data[x][2];
														console.log(home_id);
														  w2ui['grid'].unlock();
															stop_analyzer();
															clearInterval(hid_interval);
															w2ui.grid.clear();
															load();
															$.ajax({
																		type: "POST",
																		url: "ajax/homeid_save.php",
																		data: { homeid: home_id },
																		success: function(response) {
																			console.log("saved");
																			}
																		});
															break;

													}
										 }
								}
						});
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
