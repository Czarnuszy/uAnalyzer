
<?php
include("inc/jqgrid_dist.php");

////////////MM
mysql_connect("localhost", "root", "razdwa3");
mysql_select_db("analyzer");

include(PHPGRID_LIBPATH."inc/jqgrid_dist.php");
$g = new jqgrid($db_conf);

$g->select_command = "SELECT ID, rssi, date, source, destination, sequence FROM csv";
// set few params
$grid["forceFit"] = true;
$grid["autowidth"] = true;
//$grid["autoheight"] = true;
$grid["multiselect"] = false;
$grid["ignoreCase"] = true; // do case insensitive sorting
//$grid["rowList"] = array();
//$grid["height"] = "400";
$grid["resizable"] = true;
//$grid["scroll"] = true;  //true tip for large tables
$grid["rowNum"] = 100;
$e["js_on_load_complete"] = "do_onload";
$grid["loadtext"] ="...";


$col = array();
$col["title"] = "ID"; // caption of column
$col["name"] = "ID";
$col["width"] = "10";
$col["align"] = "center";
$col["sortable"] = false;
$col["search"] = false;
$cols[] = $col;


$col = array();
$col["title"] = "Rssi"; // caption of column
$col["name"] = "rssi";
$col["width"] = "8";
$col["align"] = "left";
//$col["cellcss"] = "'color':'green'";
//$col["default"] = "<div style='width:{bar}px; background-color:navy; height:14px'></div>";
$col["search"] = false;
$cols[] = $col;

$col = array();
$col["title"] = "Date"; // caption of column
$col["name"] = "date";
$col["width"] = "30";
$col["align"] = "center";
$cols[] = $col;


$col = array();
$col["title"] = "Source"; // caption of column
$col["name"] = "source";
$col["align"] = "center";
$col["width"] = "10";
$cols[] = $col;

$col = array();
$col["title"] = "Route"; // caption of column
$col["name"] = "route";
$col["align"] = "center";
$col["width"] = "20";
$cols[] = $col;

$col = array();
$col["title"] = "Destination"; // caption of column
$col["name"] = "destination";
$col["align"] = "center";
$col["width"] = "12";
$cols[] = $col;

$col = array();
$col["title"] = "Command";
$col["name"] = "command";
$col["width"] = "40";
$col["align"] = "center";
//$col["search"] = false;
$col["sortable"] = false;
$cols[] = $col;

$col = array();
$col["title"] = "Sequence"; // caption of column
$col["name"] = "sequence";
$col["align"] = "center";
$col["width"] = "20";
$col["search"] = false;
$col["hidden"] = true;
$cols[] = $col;


$col = array();
$col["title"] = "RouteCount"; // caption of column
$col["name"] = "route_count";
$col["align"] = "center";
$col["width"] = "20";
$col["hidden"] = true;
$cols[] = $col;


$col = array();
$col["title"] = "Properties"; // caption of column
$col["name"] = "properties";
$col["align"] = "center";
$col["width"] = "20";
$col["hidden"] = true;
$cols[] = $col;


//////////////////formating of rows based on Sequence number

$col = array();

$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "02"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#f0f0f0'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "15"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#e0e0e0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "04"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#d0d0d0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "13"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#c0c0c0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "03"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#b0b0b0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "14"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#a0a0a0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "05"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#909090'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "12"; // you can use placeholder of column name as value

$col["css"] = "'background-color':'#808080'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "06"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#f0f0f0'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "11"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#e0e0e0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "07"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#d0d0d0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "10"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#c0c0c0'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "09"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#909090'";
$col_conditions[] = $col;
$col = array();
$col["column"] = "sequence";
$col["op"] = "eq";
$col["value"] = "01"; // you can use placeholder of column name as value
$col["css"] = "'background-color':'#909090'";
$col_conditions[] = $col;




$col = array();
$col["column"] = "strength";
$col["op"] = "eq";
$col["value"] = "||"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'Red'";
$col["default"] = "<div style='width:{bar}px; background-color:navy; height:14px'></div>";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = "eq";
$col["value"] = "|||"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'OrangeRed'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = "eq";
$col["value"] = "||||"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'Orange'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = "cn";
$col["value"] = "|||||"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'Green'";
$col_conditions[] = $col;


$g->set_conditional_css($col_conditions);

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
$g->set_events($e);

$g->set_options($grid);

//$g->table = "csv";
$out = $g->render("list1");


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

    <script>

    </script>
    <?php echo $out ?>
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

    </script>
</body>
</html>
