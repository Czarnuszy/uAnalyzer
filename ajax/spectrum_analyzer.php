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

function load(){
console.log("lload");
$.ajax({
			url: "ajax/spectrum_data.php",
			type: 'POST',
			data: { fileName: "d" },
			dataType:"json",
			//async: false,
			success: function(data) {
				console.log(data[0].length);
				console.log(data[1].length);
				console.log(data[0][16][1]);
				console.log("max" + data[1][16][1]);
				if(data[0].length == 98 && data[1].length == 98){
					 for (var i = 0; i < 98; i++) {
						 window.myLine.data.datasets[0].data[i] = data[0][i][1];
				     window.myLine.data.datasets[1].data[i] = data[1][i][1];
			 	 		}
						window.myLine.update();

				}

			/*	 $.ajax({
							 url: "ajax/spectrum_data.php",
							 type: 'POST',
							 data: { fileName: "data" },
							 dataType:"json",
				//			 async: false,
							 success: function(data2) {
								 console.log( data2[0][1]);

									for (var i = 0; i < data2.length; i++) {
										window.myLine.data.datasets[0].data[i] = data2[i][1];
									}

								 window.myLine.update();

							 },

							error: function(xhr, status, error) {
								var err = eval("(" + xhr.responseText + ")");
								console.log(xhr + " " + status + " " + error);
							}
					 });*/

			},
			error: function(xhr, status, error) {
				var err = eval("(" + xhr.responseText + ")");
				console.log(xhr + " " + status + " " + error);
		}

	});

}

var myset

function myTimeoutFunction()
{
    load();
    myset = setTimeout(myTimeoutFunction, 2000);
}


$("#play-a3").click(function(){
		start_spectrum();
		console.log("ds");
		myInterval = setInterval(load, 1000);
//		myTimeoutFunction();
		$.smallBox({
				title : "Z-Wave Spectrum Analyzer",
				content : "<i class='fa fa-clock-o'></i> <i>Start</i>",
				color : "#659265",
				iconSmall : "fa fa-times fa-2x fadeInRight animated",
				timeout : 3000
			});


});

$("#stop-a3").click(function(){
	console.log("stop");
	stop_spectrum();
	clearInterval(myInterval);
//	 clearTimeout(myset);
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


var pagefunction = function() {

	$(document).ready(function() {
		$( "#spectrum-body" ).load( "ajax/script_spectrum.php" );
		load();

		//load();


	});


}


	pageSetUp();

	pagefunction();



</script>
