
<?php

$base=mysqli_connect("localhost","root","","analyzer");

$records = mysqli_query($base,"SELECT * FROM health_info");



include("inc/jqgrid_dist.php");
//error_reporting(E_ALL);

$g = new jqgrid();

// set few params
$grid["forceFit"] = true;
$grid["autowidth"] = true;
$grid["multiselect"] = false;
$grid["ignoreCase"] = true; // do case insensitive sorting
//$grid["height"] = "100%";
$e["js_on_load_complete"] = "do_onload";
$grid["rowNum"] = 20;
$grid["resizable"] = true;



$g->set_options($grid);

$i=0;
while($row = mysqli_fetch_array($records)){

   	$data[$i]['id'] = $row[0] ;
 	  $data[$i]['info'] = $row[1] ;
    $data[$i]['index'] = $row[2] ;
	   $i++;
 }

// pass data in table param for local array grid display
$g->table = $data; // blank array(); will show no records

$col = array();
$col["title"] = "Device ID"; // caption of column
$col["name"] = "id";
//$col["fixed"] = true;

$col["width"] = "30";
$col["align"] = "center";
$cols[] = $col;

$col = array();
$col["title"] = "Health Info	"; // caption of column
$col["name"] = "info";
//$col["fixed"] = true;

$col["width"] = "";
$col["align"] = "center";
$cols[] = $col;

$col = array();
$col["title"] = "	Health Index"; // caption of column
$col["name"] = "index";
//$col["fixed"] = true;
$col["width"] = "30 ";
$col["align"] = "center";
$cols[] = $col;




$g->set_columns($cols);

$g->set_actions(array(
						"add"=>false, // allow/disallow add
						"edit"=>false, // allow/disallow edit
						"delete"=>false, // allow/disallow delete
						"view"=>true, // allow/disallow delete
						"rowactions"=>false, // show/hide row wise edit/del/save option
						"export"=>false, // show/hide export to excel option
						"autofilter" => true, // show/hide autofilter for search
						"search" => false // show single/multi field search condition (e.g. simple or advance)

					)
				);


// render grid
$out = $g->render("list2");

//////////////////////////////////////////////////////////////////
////////////widgets

?>

<html>
<head>
 <link rel="stylesheet" href="lib/js/themes/ui-lightness/jquery-ui.custom.css"></link>
    <link rel="stylesheet" href="lib/js/jqgrid/css/ui.jqgrid.css"></link>
    <script src="lib/js/jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
    <script src="lib/js/jqgrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
    <script src="lib/js/themes/jquery-ui.custom.min.js" type="text/javascript"></script>

</head>
<body>

					<center>
		<?php echo $out?>
		</center>
    <script>

        function do_onload()
        {
            if (jQuery(window).data('phpgrid_scroll'))
                jQuery('div.ui-jqgrid-bdiv').scrollTop(jQuery(window).data('phpgrid_scroll'));

            jQuery('div.ui-jqgrid-bdiv').scroll(function(){
                jQuery(window).data('phpgrid_scroll',jQuery('div.ui-jqgrid-bdiv').scrollTop());
            });
        }

</body>
</html>
