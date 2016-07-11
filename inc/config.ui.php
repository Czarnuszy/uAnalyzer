<?php

//CONFIGURATION for SmartAdmin UI

//ribbon breadcrumbs config
//array("Display Name" => "URL");
$breadcrumbs = array(
	"Home" => APP_URL
);

/*navigation array config

ex:
"dashboard" => array(
	"title" => "Display Title",
	"url" => "http://yoururl.com",
	"url_target" => "_blank",
	"icon" => "fa-home",
	"label_htm" => "<span>Add your custom label/badge html here</span>",
	"sub" => array() //contains array of sub items with the same format as the parent
)

*/
$page_nav = array(
    "packet_analyzer" => array(
	"title" => "Z-Wave Packet Analyzer",
	"url" => "ajax/packet_analyzer.php",
	"icon" => "fa-list-alt"
    ),
    "spectrum_analyzer" => array(
	"title" => "Spectrum Analyzer",
	"url" => "ajax/spectrum_analyzer.php",
	"icon" => "fa-bar-chart"
    ),
     "network_health" => array(
	"title" => "Network Health",
	"url" => "ajax/ima.php",
	"icon" => "fa-stethoscope"
    ),	
    "help" => array(
	"title"	=> "Help",
	"url" => "ajax/help.php",
	"icon" => "fa-info"
    ),	
    "contact" => array(
	"title" => "Contact Us",
	"url" => "ajax/contact.php",
	"icon" => "fa-mobile"
    ),	
    	
);


//configuration variables
$page_title = "";
$page_css = array();
$no_main_header = false; //set true for lock.php and login.php
$page_body_prop = array(); //optional properties for <body>
$page_html_prop = array(); //optional properties for <html>
?>