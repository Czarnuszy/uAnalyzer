<?php

	session_start();
	
	if (!isset($_SESSION['logged']))
	{
		header('Location: login.php');
		exit();
	} 
	
?>
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
	
			<div class="cont">
	<br/>
			Here are the basic instructions for using your Z-Wave Toolbox.<br>
<br/>
<h4 class="header">Packet Analyzer</h4>
Join a network that you are troubleshooting, and get the toolbox close to the existing gateway.
When started, you can see the traffic on that particular gateway. See relationships and communication between devices in real-time.
<br/><br/>
<h4 class="header">Spectrum Analyzer</h4>
Use this tool to check the area for interference that may deny your Z-Wave network proper communication.
Readings higher than 40 at either of the two target lines indicates traffic that may drown out your Z-Wave signal.
Check the area for alternate sources of RF noise, including older baby monitors, etc. Restarting power in nearby rooms
(while keeping the analyzer running) may help isolate the noise.<br>
<br>	
<b>PLEASE NOTE:</b> changing between the Packet Analyzer and Spectrum Analyzer will require up to 60 seconds to reset the chip.
<br><br>
<h4 class="header">Network Health</h4>
These widgets show long-term relationships between gateways and devices, and also in between devices.
You should be looking for missing node IDs, weak signals for node IDs, and lots of neighbors to provide a good mesh network.

For more information on reading the Packet Analyzer, Spectrum Analyzer or Network Health Indicators,
please visit <a href="<?php echo APP_URL; ?>/index.php#ajax/z-wave-toolbox.php"><b>CLICK</b></a> to view the full instructions.

<br><br>
For more help, please <a href="ajax/contact.php"><b>Contact Us.</b></a>

</div>
		</div>
			
	</div>



<script type="text/javascript">
	
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
