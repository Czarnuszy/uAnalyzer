<?php

    session_start();

    if (!isset($_SESSION['logged'])) {
        header('Location: login.php');
        exit();
    }
////////////

  $directory = '../data/Saves';
  $scanned_directory = array_diff(scandir($directory), array('..', '.'));
  $amount_files = count($scanned_directory);

    $fileID = fopen('../zniffer/data/zniffer.txt', 'r') or die('Unable to open file!');
    $homeid = fgets($fileID);
    fclose($fileID);

$homeid = substr($homeid, 0, -1);

?>


<html>
<head>

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

								<label class="btn btn-default btn-xs " id="open"></i> <i class="fa fa-search-plus"></i>   Traces

									</label>

						</div>
					<div class="widget-toolbar">

							<label class="btn btn-default btn-xs " id="join-a1"></i> <i class="fa fa-search-plus"></i>    Join

								</label>

					</div>

						<div class="widget-toolbar">
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

					        <label class="btn btn-default btn-xs  " id="stop-a1">
					          	<input type="radio" name="button" id="style-a3" value="stop" > <i class="fa fa-stop"></i> Stop
					        </label>

						</div>

					</div>

					<div class="widget-toolbar">
						<label class="" id="opened_filename">
								Current tracking
						</label>
					</div>

				</header>



				<div class="widget-body">
					<div id="body-w">
  					<center>
        		Loading Data...
        		</center>
      		</div>
					<div id="progresZniffer">	</div>

		   </div>

				</div>
			</article>

		</div>

	</section>
</body>
</html>



<script type="text/javascript">
var radioButton = "";

var is_zniffer_on = false;


var home_id= <?php  echo "'".$homeid."'"; ?>;
 $('input').on('change', function() {
		radioButton = $('input[name=button]:checked').val();
		console.log(radioButton);
 });



function parse_sqnum(x, data){
	var color = "";
	if (data[x][8]== "01")
		color = "#f0f0f0";
	else if (data[x][8]  === "02")
		color = "#E8E8E8";
	else if (data[x][8] == "03")
		color = "#E0E0E0";
	else if (data[x][8]  == "04")
		color = "#D8D8D8";
	else if (data[x][8]  == "05")
		color = "#D0D0D0";
	else if (data[x][8]  == "06")
		color = "#C8C8C8";
	else if (data[x][8]  == "07")
		color = "#C0C0C0";
	else if (data[x][8]  == "08")
		color = "#B8B8B8";
	else if (data[x][8]  == "09")
		color = "#B0B0B0";
	else if (data[x][8] == "10")
		color = "#A8A8A8";
	else if (data[x][8] == "11")
		color = "#A0A0A0";
	else if (data[x][8]  == "12")
		color = "#989898";
	else if (data[x][8]  == "13")
		color = "#909090";
	else if (data[x][8]  == "14")
		color = "#888888";
	else if (data[x][8] == "15")
		color = "#808080";

	return color;
}

function refresh(){

  if(is_zniffer_on){
    var grid_rec = w2ui.grid.records.length;
    var NumberofLines;
    if(w2ui.grid.records.length > 2)
      w2ui.grid.unlock();

             $.ajax({
                   url: "ajax/jsGridData.php",
                   type: 'POST',
                   data: {startline: grid_rec},
                   dataType:"json",
                   success: function(data) {
                     if (data == null){
                       console.log("null");
                     }else
                     {

                    NumberofLines =  data.length;
                    console.log("records: " + NumberofLines);
                    var color = "";
                    var ZWCommandParsed = "";
                    var ZWparsedRoute = "";
                    var ZWparsedSource = "";
                    var ZWparsedDestination = "";

                    console.log("more");
                    grid_rec = w2ui.grid.records.length;
                    for(x=0; x < NumberofLines-1; x++)
                    {
                  
                      color = "#AD3232";
                      if (data[x][2] != home_id)
                      {
                        ZWparsedSource = '-';
                        ZWparsedDestination = '-';
                        ZWparsedRoute = '-';
                      }
                      else
                      {
                        color = parse_sqnum(x, data);
                        ZWCommandParsed = parseCommand(data[x]);
                        ZWparsedRoute = parseRoute(data[x]);
                        ZWparsedSource = parseInt(data[x][3],10);
                        ZWparsedDestination = parseInt(data[x][5],10);
                      }

                      function add_rec(){
                        w2ui['grid'].add({
                          recid : grid_rec,
                          id: grid_rec,
                          rssi: data[x][1],
                          data: data[x][0],
                          source: ZWparsedSource,
                          route: ZWparsedRoute,
                          destination: ZWparsedDestination,
                          command: ZWCommandParsed,
                          h_id: data[x][2],
                          style: "background-color: " + color,
                          });
                        }
                      //	setTimeout(add_rec, 100);
                        add_rec();
                        grid_rec++;
                   }

                    var datalen = w2ui.grid.records.length;
                    $.ajax({
                          type: "POST",
                          url: "ajax/homeid_save.php",
                          data: { homeid: home_id, gridlen: datalen },
                          success: function(response) {

                         },
                         error: function(er){
                           console.log("save error" + er);
                         }
                          });
                      }
                      setTimeout(refresh, 500);
                      //refresh();
                  },
                  error: function (err) {
                    console.log(err);
                  }

              });
        }
}

  /*function refresh(){

		if(is_zniffer_on){
			var grid_rec = w2ui.grid.records.length;
			var NumberofLines;
			if(w2ui.grid.records.length > 0)
				w2ui.grid.unlock();

							 $.ajax({
								     url: "../zniffer/data/zniffer.csv",
								     type: 'GET',

								     success: function(response) {
											//  if (data == null){
											// 	 console.log("null");
											//  }else{
                      var data = CSVToArray( response );
                      console.log(home_id);
                      console.log(data[0][2]);

                  //    console.log(data);
                      console.log(data.length);
									 		NumberofLines =  data.length;
                      grid_rec = w2ui.grid.records.length;

                      if (NumberofLines > grid_rec) {


											console.log("records: " + NumberofLines);
											var color = "";
					            var ZWCommandParsed = "";
						        	var ZWparsedRoute = "";
						        	var ZWparsedSource = "";
						        	var ZWparsedDestination = "";

                      var rec2load = NumberofLines - grid_rec -1;
                      console.log(rec2load);;
                      console.log(NumberofLines);
                      if (rec2load >= 5) {
                        rec2load = 5;
                      }

									 		console.log("more");
											for(x=grid_rec; x <NumberofLines; x++)
											{
												color = "#AD3232";
												if (data[x][2] != home_id)
												{
													ZWparsedSource = '-';
													ZWparsedDestination = '-';
													ZWparsedRoute = '-';
												}
												else
												{
													color = parse_sqnum(x, data);
				      						ZWCommandParsed = parseCommand(data[x]);
			        						ZWparsedRoute = parseRoute(data[x]);
			        						ZWparsedSource = parseInt(data[x][3],10);
		        	  					ZWparsedDestination = parseInt(data[x][5],10);
												}

												function add_rec(){
													w2ui['grid'].add({
														recid : grid_rec,
														id: grid_rec,
														rssi: data[x][1],
														data: data[x][0],
														source: ZWparsedSource,
														route: ZWparsedRoute,
														destination: ZWparsedDestination,
												    command: ZWCommandParsed,
														h_id: data[x][2],
														style: "background-color: " + color,
									 	 				});
													}
												//	setTimeout(add_rec, 100);
													add_rec();
													grid_rec++;
										 }
                   }
											var datalen = w2ui.grid.records.length;
											// $.ajax({
											// 			type: "POST",
											// 			url: "ajax/homeid_save.php",
											// 			data: { homeid: home_id, gridlen: datalen },
											// 			success: function(response) {
                      //
											// 		 },
											// 		 error: function(er){
											// 			 console.log("save error" + er);
											// 		 }
											// 			});
											//	}
												setTimeout(refresh, 500);
												//refresh();
								    },
										error: function (err) {
											console.log(err);
										}

								});
					}
 }
*/
	function load(){
	/*	$.ajax({
			url: 'ajax/read_real_file_size.php',
			type: 'GET',
			success: function(realsize){
				$.ajax({
					type: "POST",
					url: "ajax/homeid_save.php",
					data: { homeid: home_id,  gridlen: realsize},
					success: function(){
						console.log("load full ok");
					}
				});
			}

		});*/
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

  function setActiveButton(button){
    var $play = $('#play-a1');
    var $stop = $('#stop-a1');
    var $pause = $('#pause-a1');

    switch (button) {
      case 'start':
          $stop.removeClass('active');
          $pause.removeClass('active');
          $play.addClass('active');
          radioButton = "start";

        break;
        case 'stop':
            $stop.addClass('active');
            $pause.removeClass('active');
            $play.removeClass('active');
            radioButton = "stop";

          break;
        case 'pause':
            $stop.removeClass('active');
            $pause.addClass('active');
            $play.removeClass('active');
            radioButton = "pause";

          break;
      default:

    }
  }


	$("#open").click(function(){
		openPopup();
		$( "#popupmain" ).load( "ajax/open_file.php" );
	});

	$("#play-a1").click(function(){

			if(radioButton == "stop"){
				start_analyzer();
				console.log("start after stop");
			    w2ui.grid.clear();
					w2ui.grid.lock('Getting ready.', true);
          load();
          is_zniffer_on = true;

          setTimeout(refresh, 2600);

				}
			else if (radioButton == "pause") {
					console.log("start after pause");
					load();
					is_zniffer_on = true;

					setTimeout(refresh, 600);
			//		myInterval = setInterval(refresh, 3500);
    }else {
      console.log(radioButton);
      console.log('start button exception');
    }
      // else if (!zniffer_status && !is_zniffer_on){
			// 	start_analyzer();
			// 	is_zniffer_on = true;
			// 	refresh();
			// }else if (!zniffer_status && is_zniffer_on) {
			// 	start_analyzer();
			// 	refresh();
			// }else if (!is_zniffer_on && zniffer_status) {
			// 	is_zniffer_on = true;
			// 	refresh();
      //
			// }

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
				//clearInterval(myInterval);
				is_zniffer_on = false;
				console.log("pause after start");
			} else if(radioButton == 'stop') {
			//	if(myInterval)
				//	clearInterval(myInterval);
				is_zniffer_on = false;
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
	//	if(radioButton == "start" || radioButton == "pause"){
		$.SmartMessageBox({
	title : "Z-Wave Packet Analyzer",
	content : "Are sure to stop? This will clear the trace",
	buttons : "[STOP][Cancel]",
			}, function(ButtonPress, Value) {

	if (ButtonPress === "STOP") {

		if(radioButton == "start"){
			console.log("stop after start k");
	//		clearInterval(myInterval);
		is_zniffer_on = false
			stop_analyzer();
		}else	if (radioButton == "pause") {
				console.log("stop after pause");
				stop_analyzer();
		}else	if (radioButton == "stop") {
				console.log("stop after stop");
				stop_analyzer();
				is_zniffer_on = false;
		//		clearInterval(myInterval);
		//		refresh();
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

	//		if(radioButton == "start"){
				$("#stop-a1").attr('class', 'btn btn-default btn-xs');
				$("#play-a1").attr('class', 'btn btn-default btn-xs active');
				if (is_zniffer_on) {
          radioButton = "start";
					//is_zniffer_on = true;
					setTimeout(refresh, 200);
				}else{
          radioButton = "stop";

        }
				$.smallBox({
						title : "Z-Wave Packet Analyzer",
						content : "<i class='fa fa-clock-o'></i> <i>trace in progres</i>",
						color : "#659265",
						iconSmall : "fa fa-times fa-2x fadeInRight animated",
						timeout : 3000
					});
			/*	}else if(radioButton == "pause") {
					$("#stop-a1").attr('class', 'btn btn-default btn-xs');
					$("#pause-a1").attr('class', 'btn btn-default btn-xs active');
					radioButton = "pause";
				}*/
					}
		});
//}
	});


$('#network_checkbox').click(function() {
	if(this.checked){
		w2ui['grid'].search('h_id', home_id);
	}
	else
		w2ui['grid'].search('h_id', '');

	});

	$("#refresh-a1").click(function(){
  w2ui.grid.clear();
	w2ui.grid.lock('Getting ready.', true);

	$("#opened_filename").text("Actual File");

		$.smallBox({
			title : "Z-Wave Packet Analyzer",
			content : "<i class='fa fa-clock-o'></i> <i>I am here </i>",
			color : "#659265",
			iconSmall : "fa fa-check fa-2x fadeInRight animated",
			timeout : 4000
		    });
			load();

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

				$( "#body-w" ).load( "ajax/savetrace.php?filename=" + Value);


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

				hid_interval = setInterval(get_homeid, 2000);

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

function openPopup() {
    w2popup.open({
        title   : 'Your Sniffers',
        width   : 400,
        height  : 400,
        showMax : true,
        body    : '<div id="popupmain" style="position: absolute; left: 0px; top: 0px; right: 0px; bottom: 0px;"></div>',
        onOpen  : function (event) {
            event.onComplete = function () {

            }
        },
        onToggle: function (event) {
            event.onComplete = function () {
								w2ui.grid.refresh();
            }
        }

    });

}

function get_homeid(){

	var NumberofLines;
	var file_dir = '../zniffer/data/zniffer.csv';
	var grid_rec = w2ui.grid.records.length;

					 $.ajax({
								 url: "ajax/open_file_data.php",
								 type: 'POST',
								 data: {data: file_dir },
								 dataType:"json",
								 success: function(data) {
									 NumberofLines = data.length -1;

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
																		data: { homeid: home_id,  gridlen: NumberofLines},
																		success: function(response) {
																			console.log("saved");
																			}
																		});

																		$.smallBox({
																title : "Z-Wave Packet Analyzer",
																content : "<i class='fa fa-clock-o'></i> <i>Joined!</i>",
																color : "#659265",
																iconSmall : "fa fa-check fa-2x fadeInRight animated",
																timeout : 3000
															    });
															break;

													}
										 }
								}
						});
}

function parseRoute(all_data)
{
var hop = all_data[9];
var count = all_data[10];
var header = all_data[11];
var route = "";
var source = parseInt(all_data[3],10);
var destination = parseInt(all_data[5],10);;


var hop1 = all_data[4];
	hop1  = hop1.slice(0,2);
	hop1 = parseInt(hop1,16);
var hop2 = all_data[4];
	hop2  = hop2.slice(3,5);
	hop2 = parseInt(hop2,16);

if (hop.includes("-1")) route = source + ">" + destination;
	else if (hop.includes("01"))
	{
  if ((count.includes("000")) && (header.includes("000")))
  {
  	route =source + ">"+all_data[4]+"-" + destination;
  }
  else if ((count.includes("001")) && (header.includes("000")))
  {
    route =source + "-"+ all_data[4]+">" + destination;
  }
  else if ((count.includes("001") )&& (header.includes("003")))
  {
    route =source + ">"+all_data[4]+"-" + destination;

  }
  else if ((count.includes("015")) && (header.includes("003")))
  {
  	route =source + "-"+all_data[4]+">" + destination;
  }
  else if ((count.includes("015")) && (header.includes("021")))
  {
    route =source + "X"+all_data[4]+">" + destination;
  }
 }
 else if (hop.includes("02"))
 {
 	if ((count.includes("000")) && (header.includes("000")))
	{
		route =source + ">"+ hop1+ "-"+hop2+"-" + destination;
	}

	if ((count.includes("001")) && (header.includes("000")))
	{
		route =source + "-"+ hop1+ ">"+hop2+"-" + destination;
	}

	else if ((count.includes("002")) && (header.includes("000")))
	{
		route =source + "-"+ hop1+ "-"+hop2+">" + destination;
	}
	else if ((count.includes("001")) && (header.includes("003")))
	{
		route =source + ">"+ hop1+ "-"+hop2+"-" + destination;
	}
	else if ((count.includes("000")) && (header.includes("003")))
	{
		route =source + "-"+ hop1+ ">"+hop2+"-" + destination;
	}
	else if ((count.includes("015")) && (header.includes("003")))
	{
		route =source + "-"+ hop1+ "-"+hop2+">" + destination;
	}
	else if ((count.includes("015")) && (header.includes("021")))
	{
		route =source + "-"+ hop1+ "-"+hop2+"X" + destination;
	}
	else if ((count.includes("000")) && (header.includes("037")))
	{
		route =source + "X"+ hop2+ ">"+hop1+"-" + destination;
	}
	else if ((count.includes("015")) && (header.includes("037")))
	{
		route =source + "X"+ hop1+ "-"+hop2+">" + destination;
	}

 }

//route = "cool";
return(route);


}





function parseCommand(all_data)
{

				var RawCommand = all_data[7];
				var ZWCommandClass=RawCommand.slice(3,5) ;
				var ZWCommand= RawCommand.slice(6,8) ;
				var ZWCommandPayload1 = RawCommand.slice(9,11)
				var ZWCommandDescription = "";
				var ZWpayload =all_data[7];
				var ZWpackettype =all_data[6];
				var ssource = all_data[3];
				var ZWack = all_data[13];


				ZWCommandDescription= "Unrecognized Command: "+RawCommand;

					// other network
					if (ZWack.includes("Ack") || ZWpackettype.includes("Ack"))
					{
						//if(ZWack.includes("")) ZWCommandDescription = "Ack";
						//else ZWCommandDescription = ZWack;
						ZWCommandDescription = ZWack;
					}
					else if ((ZWCommandClass.includes("20"))|| (ZWCommandClass.includes("25"))||(ZWCommandClass.includes("27")))
					{
						ZWCommandDescription = "Basic Command Class("+ ZWCommandClass+","+ZWCommand+","+ZWCommandPayload1+")";
						//it is Basic CC
						if (ZWCommand.includes("01"))
							{
								if (ZWCommandPayload1 == "00")
									ZWCommandDescription = "Turn OFF";
								else if (ZWCommandPayload1 == "FF")
									ZWCommandDescription = "Turn ON";
								else if (ZWCommandPayload1 < 64)
								{
									ZWCommandDescription=  "Dimm to:"+ ZWCommandPayload1;
								}
							}
						else if (ZWCommand.includes("02"))
							{
								ZWCommandDescription = "is Device On? Off?";
							}
						else if (ZWCommand.includes("03"))
							{
								ZWCommandDescription = "Basic Report";
								if (ZWCommandPayload1 == "00")
									ZWCommandDescription = "Device is OFF";
								else if (ZWCommandPayload1 == "FF")
									ZWCommandDescription = "Device is ON";
								else if (ZWCommandPayload1 < 64)
								{
									ZWCommandDescription=  "Dimm level = "+ ZWCommandPayload1;
								}
							}

					}

					else if (ZWCommandClass.includes("98"))
					{
						// Security CC
						ZWCommandDescription=  "Encrypted Command";
					}
					else if (ZWCommandClass.includes("71"))
					{
						// Security CC
						ZWCommandDescription=  "Notification/Alarm  Command";
					}
					else if (ZWCommandClass.includes("22"))
					{
						// Security CC
						ZWCommandDescription=  "Application Status";
					}
					else if (ZWCommandClass.includes("9B"))
					{
						// Security CC
						ZWCommandDescription=  "Configuration Association Command";
					}
					else if (ZWCommandClass.includes("85"))
					{
						// Security CC
						ZWCommandDescription=  "Association Command";
					}
					else if (ZWCommandClass.includes("95")||ZWCommandClass.includes("96")||ZWCommandClass.includes("97"))
					{
						// Security CC
						ZWCommandDescription=  "A/V Command";
					}
					else if (ZWCommandClass.includes("36"))
						{
							// Security CC
							ZWCommandDescription=  "Basic Tariff info Command";
						}
					else if (ZWCommandClass.includes("50"))
						{
							// Security CC
							ZWCommandDescription=  "Window Covering Comand";
						}
					else if (ZWCommandClass.includes("80"))
						{
							// Security CC
							ZWCommandDescription=  "Battery Command";
						}
					else if (ZWCommandClass.includes("2A"))
						{
							// Security CC
							ZWCommandDescription=  "Chimney Fan Command";
						}
					else if (ZWCommandClass.includes("46"))
						{
							// Security CC
							ZWCommandDescription=  "Climate Control Schedule Command";
						}

					else if (ZWCommandClass.includes("81"))
						{
							// Security CC
							ZWCommandDescription=  "Clock Command";
						}
					else if (ZWCommandClass.includes("70"))
						{
							// Security CC
							ZWCommandDescription=  "Configuration Command";
						}
					else if (ZWCommandClass.includes("21"))
						{
							// Security CC
							ZWCommandDescription=  "Controller Replication Command";
						}
					else if (ZWCommandClass.includes("56"))
						{
							// Security CC
							ZWCommandDescription=  "CRC16 Command";
						}
					else if (ZWCommandClass.includes("3A")||ZWCommandClass.includes("3B"))
						{
							// Security CC
							ZWCommandDescription=  "DCP command";
						}
					else if (ZWCommandClass.includes("4C"))
						{
							// Security CC
							ZWCommandDescription=  "Door Lock Logging Command";
						}
					else if (ZWCommandClass.includes("32"))
						{
							// Security CC
							ZWCommandDescription=  "Meter Command";
						}
					else if (ZWCommandClass.includes("60"))
						{
							// Security CC
							ZWCommandDescription=  "Multichanel Command";
						}
					else if (ZWCommandClass.includes("2B"))
						{
							// Security CC
							ZWCommandDescription=  "Scene Activation Command";
						}

			//ZWCommandDescription= ZWCommandDescription + " P:"+RawCommand;

// end of payload
return (ZWCommandDescription);

}



function open_file(atr, atr2){
	progressbar(0, 100);

	w2ui.grid.lock("Loading. Please wait.", true);
	var t0 = performance.now();

  $.ajax({
		url: "ajax/files_size.php",
		type: "POST",
		data: { DisplayedRecords: atr2},
		dataType: 'json',
		success: function(response){
			NumberofLines= response[1]-1;
			home_id = String(response[0]);
		  home_id =	home_id.slice(0, -1);
      console.log(NumberofLines);

			var t1 = performance.now();

      if(NumberofLines > 2000){
        console.log("over 1000");

        if(w2ui.grid.records.length > 0)
          w2ui.grid.clear();

				var rec_to_load = 2000;

				if(NumberofLines > 7000)
						rec_to_load = 8000;

        var reclen = w2ui.grid.records.length;
        var i = NumberofLines / rec_to_load;
        i = parseInt(i);

        var val = [0];
        for (var x = 1; x < i; x++) {
          val.push(x * rec_to_load);
        }
        val.push(val[val.length-1] + rec_to_load); //NumberofLines-i*1000


				var current = 0;

				do_ajax();
				function do_ajax(){

					if(current < val.length){
						var rec = val[current] + rec_to_load;
						if (rec > NumberofLines) {
							rec = NumberofLines;
						}

						$.ajax({
							url: 'ajax/open_file_data.php',
							type: 'POST',

						//  async: false,
							data: { data: atr, fsize: NumberofLines, tim: rec  , gridLen: val[current]},
							dataType: 'json',
							success: function(data){

								console.log(rec_to_load);
								console.log("val" + val[current]);
								console.log("number of lines " + NumberofLines);

								var t2 = performance.now();
						//    reclen = w2ui.grid.records.length;
								console.log("gridlen" + w2ui.grid.records.length);
								var color = "red";
								console.log("dl" + data.length)
								var ZWCommandParsed = "";
								var ZWparsedRoute = "";
								var ZWparsedSource = "";
								var ZWparsedDestination = "";


									for(x=0; x< data.length	; x++){
										reclen = w2ui.grid.records.length;
										color = "#AD3232";
										if (data[x][2] != home_id)
									{
										ZWparsedSource = '-';
										ZWparsedDestination = '-';
										ZWparsedRoute = '-';
									}
									else
									{
										color = parse_sqnum(x, data);
											ZWCommandParsed = parseCommand(data[x]);
												ZWparsedRoute = parseRoute(data[x]);
												ZWparsedSource = parseInt(data[x][3],10);
												ZWparsedDestination = parseInt(data[x][5],10);
									}

											w2ui['grid'].records.push({
												recid : reclen+1,
												id: reclen+1,
												rssi: data[x][1],
												data: data[x][0],
												source: ZWparsedSource,
												route: ZWparsedRoute,
												destination: ZWparsedDestination,
												command: ZWCommandParsed,
												h_id: data[x][2],
												style: "background-color: " + color

											 });

									}
									w2ui.grid.reload();
									delete data;
									w2ui.grid.sort('data', 'asc');
								//	if ((w2ui.grid.records.length-10) > NumberofLines )
								var t3 = performance.now();

								console.log("Call to readlines took " + (t1 - t0) + " milliseconds.");
								console.log("Call to readdata after readlines " + (t2 - t1) + " milliseconds.");
								console.log("Call to all took " + (t2 - t0) + " milliseconds.");
								console.log("Call to parsing and adding took " + (t3 - t2) + " milliseconds.");

								w2ui.grid.unlock();

								max = NumberofLines;
								x = w2ui.grid.records.length ;
								console.log(x);
								progressbar(x, max);

								current ++;
								do_ajax();
							},
							error: function(xhr, status, error) {
								var err = eval("(" + xhr.responseText + ")");
								console.log(xhr + " " + status + " " + error);
								alert(xhr + " " + status + " " + error);
						}

								});



					}

				}

				}
				 else{

					$.ajax({
						url: 'ajax/open_file_data.php',
						type: 'POST',
						data: { data: atr, fsize: NumberofLines},
						dataType: 'json',
						success: function(data){
							if(w2ui.grid.records.length > 0)
								w2ui.grid.clear();

							var color = "#AD3232";
									var ZWCommandParsed = "";
									var ZWparsedRoute = "";
									var ZWparsedSource = "";
									var ZWparsedDestination = "";

							for(x=0; x<	NumberofLines; x++){
								color = "#AD3232";
								if (data[x][2] != home_id)
								{
									ZWparsedSource = '-';
									ZWparsedDestination = '-';
									ZWparsedRoute = '-';
								}
								else
								{
									color = parse_sqnum(x, data);
									ZWCommandParsed = parseCommand(data[x]);
									ZWparsedRoute = parseRoute(data[x]);
									ZWparsedSource = parseInt(data[x][3],10);
									ZWparsedDestination = parseInt(data[x][5],10);
								}
								w2ui['grid'].records.push({
									recid : x+1,
									id: x+1,
									rssi: data[x][1],
									data: data[x][0],
									source: ZWparsedSource,
									route: ZWparsedRoute,
									destination: ZWparsedDestination,
									command: ZWCommandParsed,
									h_id: data[x][2],
									style: "background-color: " + color
								 });
							 }

							w2ui.grid.reload();

						$.smallBox({
							title : "Z-Wave Packet Analyzer",
							content : "<i>File opened.</i>",
							color : "#659265",
							iconSmall : "fa fa-check fa-2x fadeInRight animated",
							timeout : 1000
						});

						w2ui.grid.unlock();
						progressbar(1000, 1);
					}

					});
			}


	},
    error: function(xhr, status, error) {
      var err = eval("(" + xhr.responseText + ")");
      console.log(xhr + " " + status + " " + error);
      alert(xhr + " " + status + " " + error);
    }

  });

}

function onloadZnifferStatus(response) {
  if (response == 1) {
  //  $("#play-a1").attr('class', 'btn btn-default btn-xs active');
    radioButton = "start";
    load();
    is_zniffer_on = true;
    setTimeout(refresh, 1000);
    console.log('znif on');
    setActiveButton('start');
  }else if (response == 0) {
  //  $("#stop-a1").attr('class', 'btn btn-default btn-xs active');
    radioButton = "stop";
    setActiveButton('stop');

    console.log('znif oof');
    load();
    if (is_zniffer_on) {
      is_zniffer_on = false;
    }

  }else {
    console.log("zniffer status error");

  }
console.log(radioButton);
return response;
}


function returnZnifferStatus(response){
  if (response == 1){
    if (is_zniffer_on) {
    //  radioButton = "start";
      setActiveButton('start');
    }else if (!is_zniffer_on) {
      setActiveButton('pause');
    }
    console.log('Zniffer ON');
  }
  else if (response == 0){
    if (is_zniffer_on) {
      is_zniffer_on = false;
      setActiveButton('stop');
    }else if (!is_zniffer_on) {
      setActiveButton('stop');
    }

  //  radioButton = "stop";
    console.log('Zniffer OFF');

  }
  return response;
}

function zniffer_status(setZnifferStatus) {

	$.ajax({
		url: 'ajax/zniffer_status.php',
    dataType: 'json',
		success: setZnifferStatus,
    error: function(xhr, status, error) {
      var err = eval("(" + xhr.responseText + ")");
      console.log(xhr + " " + status + " " + error);
      alert(xhr + " " + status + " " + error);
    }

  });
}

function testZniffStat() {

 return Promise.resolve($.ajax({
     url: "ajax/zniffer_status.php"
 }));
}

function CSVToArray( strData, strDelimiter ){
       // Check to see if the delimiter is defined. If not,
       // then default to comma.
       strDelimiter = (strDelimiter || ",");

       // Create a regular expression to parse the CSV values.
       var objPattern = new RegExp(
           (
               // Delimiters.
               "(\\" + strDelimiter + "|\\r?\\n|\\r|^)" +

               // Quoted fields.
               "(?:\"([^\"]*(?:\"\"[^\"]*)*)\"|" +

               // Standard fields.
               "([^\"\\" + strDelimiter + "\\r\\n]*))"
           ),
           "gi"
           );


       // Create an array to hold our data. Give the array
       // a default empty first row.
       var arrData = [[]];

       // Create an array to hold our individual pattern
       // matching groups.
       var arrMatches = null;


       // Keep looping over the regular expression matches
       // until we can no longer find a match.
       while (arrMatches = objPattern.exec( strData )){

           // Get the delimiter that was found.
           var strMatchedDelimiter = arrMatches[ 1 ];

           // Check to see if the given delimiter has a length
           // (is not the start of string) and if it matches
           // field delimiter. If id does not, then we know
           // that this delimiter is a row delimiter.
           if (
               strMatchedDelimiter.length &&
               strMatchedDelimiter !== strDelimiter
               ){

               // Since we have reached a new row of data,
               // add an empty row to our data array.
               arrData.push( [] );

           }

           var strMatchedValue;

           // Now that we have our delimiter out of the way,
           // let's check to see which kind of value we
           // captured (quoted or unquoted).
           if (arrMatches[ 2 ]){

               // We found a quoted value. When we capture
               // this value, unescape any double quotes.
               strMatchedValue = arrMatches[ 2 ].replace(
                   new RegExp( "\"\"", "g" ),
                   "\""
                   );

           } else {

               // We found a non-quoted value.
               strMatchedValue = arrMatches[ 3 ];

           }


           // Now that we have our value string, let's add
           // it to the data array.
           arrData[ arrData.length - 1 ].push( strMatchedValue );
       }

       // Return the parsed data.
       return( arrData );
   }


function BETA_open_file(arg, atr2){
  w2ui.grid.lock("Loading. Please wait.", true);

  $.ajax({
    url: "ajax/files_size.php",
    type: "POST",
    data: { DisplayedRecords: atr2},
    dataType: 'json',
    success: function(response){
      NumberofLines= response[1]-1;
      home_id = String(response[0]);
      home_id =	home_id.slice(0, -1);



  $.ajax({
  	 url: arg,
  	 type: 'GET',
  	//dataType: 'json',

  	 success: function(responseText) {

       if(w2ui.grid.records.length > 0)
         w2ui.grid.clear();

      var color = "#AD3232";
      var ZWCommandParsed = "";
      var ZWparsedRoute = "";
      var ZWparsedSource = "";
      var ZWparsedDestination = "";

  		var data = CSVToArray( responseText );
      console.log(data.length);
      for(x=1; x<	data.length-1; x++){

        color = "#AD3232";
        if (data[x][2] != home_id)
        {
          ZWparsedSource = '-';
          ZWparsedDestination = '-';
          ZWparsedRoute = '-';
        }
        else
        {
          color = parse_sqnum(x, data);
          ZWCommandParsed = parseCommand(data[x]);
          ZWparsedRoute = parseRoute(data[x]);
          ZWparsedSource = parseInt(data[x][3],10);
          ZWparsedDestination = parseInt(data[x][5],10);
        }
        w2ui['grid'].records.push({
          recid : x+1,
          id: x+1,
          rssi: data[x][1],
          data: data[x][0],
          source: ZWparsedSource,
          route: ZWparsedRoute,
          destination: ZWparsedDestination,
          command: ZWCommandParsed,
          h_id: data[x][2],
          style: "background-color: " + color
         });
       }

      w2ui.grid.reload();
      if (w2ui.grid.records.length > 20) {
        w2ui.grid.unlock();
      }

  	 },

  	error: function(xhr, status, error) {
  		var err = eval("(" + xhr.responseText + ")");

  	 // alert("1" + err.Message);
  		console.log(xhr + " " + status + " " + error);
  	}
  	});
}
});
}



function onLoad_zniffer_status(){

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



	$(document).ready(function() {

  zniffer_status(onloadZnifferStatus);

			checkStatusINt = 	setInterval(function(){
        zniffer_status(returnZnifferStatus);
      }, 1000);


	});

		};

		pagefunction();

	</script>
