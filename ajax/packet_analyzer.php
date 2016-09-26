<?php

    session_start();

    if (!isset($_SESSION['logged'])) {
        header('Location: login.php');
        exit();
    }
////////////

?>




<html>

<head>
  <script src="../js/parser.js"></script>
  <script src="../js/packet_analyzer.js"></script>
</head>

<body>

    <script>
        function my_custom_link(cellvalue, options, rowObject) {
            return '<a href="mailto:' + cellvalue + '">' + cellvalue + '</a>';
        }
    </script>

    <section id="widget-grid" class="">

        <div class="row">

            <article class="col-sm-12">

                <div class="jarviswidget" id="wid-id-0" data-widget-deletebutton="false"
                data-widget-editbutton="false" data-widget-collapsed="false" data-widget-togglebutton="false"
                 data-widget-colorbutton="false" data-widget-sortable="false">


                    <header id='zniffer_header'>

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
                        <div id="progresZniffer"> </div>

                    </div>

                </div>
            </article>

        </div>

    </section>
</body>

</html>

<script type="text/javascript">


// All javascripts in ../js/packet_analyzer.js
                      // ../parser.js





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
		 *
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
    var run_after_loaded = function(){
    //  packetAnalyzer.load();
    }

		var pagefunction = function() {
      //  loadScript("js/packet_analyzer.js", run_after_loaded);

		};
    $(document).ready(function() {

    });


		pagefunction();

	</script>
