
<?php
////////////MM

function readCSV($csvFile){
 $file_handle = fopen($csvFile, 'r');
 while (!feof($file_handle) ) {
  $line_of_text[] = fgetcsv($file_handle, 1024);
 }
 fclose($file_handle);
 return $line_of_text;


}
print "Started:"; print date('D, d M Y H:i:s:u T').PHP_EOL;
$csvFile = '../data/zniffer.csv';

$SnifferData = readCSV($csvFile);
$max = count($SnifferData);

print "file loaded:"; print date('D, d M Y H:i:s:u T').PHP_EOL;

// include and create object
include("inc/jqgrid_dist.php");
//error_reporting(E_ALL);

$g = new jqgrid();

// set few params
$grid["forceFit"] = true;
$grid["autowidth"] = true;
//$grid["autoheight"] = true;
$grid["multiselect"] = false;
$grid["ignoreCase"] = true; // do case insensitive sorting
$grid["rowList"] = array();
$grid["height"] = "400";
$grid["resizable"] = true;
//$grid["scroll"] = true;  //true tip for large tables
$max = $max-1;
if ($max> 200)
  {
    $grid["rowNum"] = 100;    // show only 500 recods per page

  }
  else
  {
    $grid["rowNum"] = $max;   // show all recods on page

  }

$e["js_on_load_complete"] = "do_onload";
$g->set_events($e);

$g->set_options($grid);


$max = $max-1;


for ($i = 1; $i < $max; $i++)
{
  $l = ($i-1);
    $data[$l]['line'] = $i;
    $data[$l]['date'] = $SnifferData[$i][0] ;
    // rescale RSSI from 20 to 80 to 0:100
    $rssi = $SnifferData[$i][1];
    $rssi = $rssi * 1.5;
    $rssi = $rssi - 20;
    $rssi = (int) $rssi;
    if ($rssi> 100) $rssi = 100;



  // RSSI measurments
  if ($rssi <20) $data[$l]['strength'] = "|";
  else if ($rssi <30) $data[$l]['strength'] = "||";
  else if ($rssi <40) $data[$l]['strength'] = "|||";
  else if ($rssi <50) $data[$l]['strength'] = "||||";
  else if ($rssi <60) $data[$l]['strength'] = "|||||";
  else if ($rssi <70) $data[$l]['strength'] = "||||||";
  else if ($rssi <80) $data[$l]['strength'] = "|||||||";
  else if ($rssi <90) $data[$l]['strength'] = "||||||||";
  else $data[$l]['strength'] = "||||||||||";
  //$data[$l]['strength'] = $SnifferData[$i][1] ;

  //$data[$l]['strength'] = $rssi;

    $data[$l]['source'] = $SnifferData[$i][3] ;
    $data[$l]['destination'] = $SnifferData[$i][5] ;
    if ($data[$l]['destination'] == "255") $data[$l]['destination']= "All";

    //$data[$l]['type'] = $SnifferData[$i][6] ;
    $type = $SnifferData[$i][6] ;
    //$data[$l]['command'] = $SnifferData[$i][7] ;
    $command = $SnifferData[$i][7] ;

    //$data[$l]['raw'] = $SnifferData[$i][7] ;
    //$data[$l]['sequence'] = $SnifferData[$i][8] ;
    //$data[$l]['hop'] = $SnifferData[$i][9] ;
    //$data[$l]['route_count'] = $SnifferData[$i][10] ;
    //$data[$l]['properties'] = $SnifferData[$i][11] ;

    $repeaters = $SnifferData[$i][4] ;
    $raw = $SnifferData[$i][7] ;
    $sequence = $SnifferData[$i][8] ;
    $hop = $SnifferData[$i][9] ;
    $count = $SnifferData[$i][10] ;
    $header = $SnifferData[$i][11] ;


    $data[$l]['sequence'] = $sequence;

    if ($type == " unsupported yet: 02")
    { //MULTICAST
        $data[$l]['destination'] = "Multiple";
        $CommandClass = $command[90].$command[91];
        $Command = $command[93].$command[94];
        $Value1 = $command[96].$command[97];
        $data[$l]['destination'] = "Multiple:".$CommandClass.$Command;
    }
    else
    {
        $CommandClass = $command[3].$command[4];
        $Command = $command[6].$command[7];
        $Value1 = $command[9].$command[10];
    }
    //$command = $data[$l]['command'];

// Basic PAYLOAD PARSING
    if ($type == "Ack") $data[$l]['command']= "Ack";  // ACK does not carry payload
    else if (($CommandClass == "20") || ($CommandClass == "26") || ($CommandClass == "25"))// Basic, Multilevel, Binary CC
      {
          if ($Command == "01" )
            {
              if ($Value1 == "00")  $data[$l]['command']= "TURN OFF";
              else if ($Value1 == "FF")  $data[$l]['command']= "TURN ON";
              else
                {
                  $data[$l]['command']= "Go to ".hexdec($Value1)."%" ;
                }
            }
          else if ($Command == "02" )
            {
              $data[$l]['command']= "ON/OFF?";
            }
          else if ($Command == "03"  )  // Report
            {
              if ($Value1 == "00"  )  $data[$l]['command']= "OFF";
              else if ($Value1 == "FF" )  $data[$l]['command']= "ON";
              else
                {
                  $data[$l]['command']= hexdec($Value1)."%" ;
                  //$data[$l]['command']= "got it".
                }
                //+((int)($command[9]+$command[10]));
            }
          //else $data[$l]['command']= "Unknown Command: " + $command;
      }
     else if ($CommandClass== "27") // All Switch CC
      {
          if ($Command == "04" )
            {
              $data[$l]['command']= "ALL DEVICES ON";
            }
          else if ($Command == "05" )
            {
              $data[$l]['command']= "ALL DEVICES OFF";
            }
          else if ($Command == "03" )
            {
              $data[$l]['command']= "ALL SWITCH CC";
            }
          //else $data[$l]['command']= "Unknown Command: " + $command;
      }
       else if ($CommandClass== "00" ) // NOP
      {
         $data[$l]['command']= "Test Command";
         //else $data[$l]['command']= "Unknown Command: " + $command;
      }
      else if ($CommandClass== "01") // Protocol CC
      {

              $data[$l]['command']= "Z-Wave Protocol Command";
      }
      else if ($CommandClass== "98") // Security CC
      {
              $data[$l]['command']= "Secure Command";
      }
      else if ($CommandClass == "31") // MultiLevel Sensor CC
      {
          //$data[$l]['command']= "Sensor Get Level- here";

            if ($Command == 4)
            {
              $data[$l]['command']= "Sensor Get Level";
            }
            else if ($Command == 5)
            {
              $data[$l]['command']= "Sensor Report";
            }

      }
      else if ($CommandClass == "40") // Thermostat Mode CC
      {
          //$data[$l]['command']= "Sensor Get Level- here";
            if ($Command == 1)
            {
              $data[$l]['command']= "Thermostat Mode Set";
            }
            else if ($Command == 2)
            {
              $data[$l]['command']= "Thermostat Mode ?";
            }
            else if ($Command == 3)
            {
              $data[$l]['command']= "Thermostat Mode Report";
            }

      }
       else if ($CommandClass == "43") // Thermostat Setpoint CC
      {
          $data[$l]['command']= "Thermostat Setpoint ";

            if ($Command == 2)
            {
              $data[$l]['command']= "Thermostat Setpoint Set";
            }
            else if ($Command == 2)
            {
              $data[$l]['command']= "Thermostat Setpoint ?";
            }
            else if ($Command == 3)
            {
              $data[$l]['command']= "Thermostat Setpoint Report";
            }
      }
       else if ($CommandClass == "2B") // Scene Activation CC
      {
          $data[$l]['command']= "Scene Command";
      }
      else if ($CommandClass == "82") // Scene Activation CC
      {
          $data[$l]['command']= "Hail Command";
      }
      else if ($CommandClass == "60") // Scene Activation CC
      {
          $data[$l]['command']= "Multi-Channel";
      }
      else if ($CommandClass == "88") // Scene Activation CC
      {
          $data[$l]['command']= "Proprietary Command";
      }
    //else $data[$l]['command']= "Unknown Command: " + $command;
    else $data[$l]['command']= $raw;
// END OF PAYLOAD PARSING

// ROUTE PARSING


if ($hop == -1) $data[$l]['route'] = ">";

else if ($hop == 1)
{
  if (($count == 0) && ($header == 0))
  {
    $data[$l]['route'] = ">".$SnifferData[$i][4]."-";
  }
  else if (($count == 1) && ($header == 0))
  {
    $data[$l]['route'] = "-".$SnifferData[$i][4].">";
  }
  else if (($count == 0) && ($header == 3))
  {
    $data[$l]['route'] = ">".$SnifferData[$i][4]."-";
    $data[$l]['command']= "Ack";
  }
  else if (($count == 15) && ($header == 3))
  {
    $data[$l]['route'] = "-".$SnifferData[$i][4].">";
    $data[$l]['command']= "Ack";
  }
  else if (($count == 15) && ($header == 21))
  {
    $data[$l]['route'] = "x".$SnifferData[$i][4].">";
    $data[$l]['command']= "No Ack from: ".$data[$l]['source'];

  }

}
else if ($hop == 2)
{

 if (($count == 0) && ($header == 0))
  {
    $data[$l]['route'] = ">".$SnifferData[$i][4]."-";

  }
  else if (($count == 1) && ($header == 0))
  {
    $data[$l]['route'] = "-".substr($repeaters, 0, -4).">".substr($repeaters,-3)."-";
  }
  else if (($count == 2) && ($header == 0))
  {
    $data[$l]['route'] = "-".$SnifferData[$i][4].">";

  }
  else if (($count == 1) && ($header == 3))
  {
    $data[$l]['route'] = ">".substr($repeaters,-3)."-".substr($repeaters, 0, -4)."-";
    $data[$l]['command']= "Ack";
  }
  else if (($count == 0) && ($header == 3))
  {
    $data[$l]['route'] = "-".substr($repeaters,-3).">".substr($repeaters, 0, -4)."-";
    $data[$l]['command']= "Ack";
  }
  else if (($count == 15) && ($header == 3))
  {
    $data[$l]['route'] = "-".substr($repeaters,-3)."-".substr($repeaters, 0, -4).">";
    $data[$l]['command']= "Ack";

  }
  else if (($count == 15) && ($header == 21))
  {
    $data[$l]['route'] = "-".substr($repeaters,-3)."-".substr($repeaters, 0, -4)."x";
    $data[$l]['command']= "No Ack from: ".$data[$l]['source'];
  }

  else if (($count == 0) && ($header == 37))
  {
    $data[$l]['route'] = "x".substr($repeaters,-3)."-".substr($repeaters, 0, -4)."-";
    $data[$l]['command']= "No Ack from: ".$data[$l]['source'];

  }
  else if (($count == 15) && ($header == 37))
  {
    $data[$l]['route'] = "x".substr($repeaters,-3)."-".substr($repeaters, 0, -4).">";
    $data[$l]['command']= "No Ack from: ".$data[$l]['source'];
  }

}
else if ($hop == 3)
{

}
else if ($hop == 4)
{

}

//$data[$l]['command'] = $type;

// ROUTE PARSING

 }

print "Data Parsing completed"; print date('D, d M Y H:i:s:u T'); print "\\n";
// pass data in table param for local array grid display
$g->table = $data; // blank array(); will show no records

$col = array();
$col["title"] = "Line"; // caption of column
$col["name"] = "line";
$col["width"] = "10";
$col["align"] = "center";
$col["sortable"] = false;
$col["search"] = false;
$cols[] = $col;

$col = array();
$col["title"] = "RSSI"; // caption of column
$col["name"] = "strength";
$col["width"] = "8";
$col["align"] = "left";
$col["cellcss"] = "'color':'green'";
//$col["default"] = "<div style='width:{bar}px; background-color:navy; height:14px'></div>";
$col["search"] = false;
$cols[] = $col;

$col = array();
$col["title"] = "Date/Time"; // caption of column
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

/*$col = array();
$col["title"] = "Type"; // caption of column
$col["name"] = "type";
$col["align"] = "center";
$col["width"] = "20";
//$col["hidden"] = true;
$cols[] = $col;
*/

# Custom made column to show link, must have default value as it's not db driven
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


/*
$col = array();
$col["title"] = "Hop"; // caption of column
$col["name"] = "hop";
$col["align"] = "center";
$col["width"] = "20";
$col["hidden"] = true;
$cols[] = $col;
*/

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


/*$col = array();
$col["title"] = "RAW"; // caption of column
$col["name"] = "raw";
$col["align"] = "center";
$col["width"] = "20";
//$col["hidden"] = true;
$cols[] = $col;
*/
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

// Signal strenght formating
/*$col = array();
$col["column"] = "strength";
$col["op"] = "<";
$col["value"] = "31"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'red'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = ">";
$col["value"] = "30"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'orange'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = ">";
$col["value"] = "40"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'yellow'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = ">";
$col["value"] = "50"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'green'";
$col_conditions[] = $col;

$col = array();
$col["column"] = "strength";
$col["op"] = ">";
$col["value"] = "60"; // you can use placeholder of column name as value
$col["cellcss"] = "'color':'darkgreen'";
//$col['strength']= "))))))))
$col_conditions[] = $col;
*/


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
////////////////// end of formating

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

//print "Start rendering:"; print date('D, d M Y H:i:s:u T').PHP_EOL;
// render grid
$out = $g->render("list1");

//print "Done rendering:"; print date('D, d M Y H:i:s:u T').PHP_EOL;

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
	var opts = {
	"stateOptions": {
                storageKey: "gridStateCookie",
                columns: true,
                filters: false,
                selection: true,
                expansion: true,
                pager: true,
                order: true
                }
	};
    </script>
    <?php echo $out ?>

    </center>

    <?php print "Done priniting:"; print date('D, d M Y H:i:s:u T').PHP_EOL; ?>

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
