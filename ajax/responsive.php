<?php
/**
 * PHP Grid Component
 *
 * @author Abu Ghufran <gridphp@gmail.com> - http://www.phpgrid.org
 * @version 2.0.0
 * @license: see license.txt included in package
 */
 include("inc/jqgrid_dist.php");

// include db config
mysql_connect("localhost", "root", "");
mysql_select_db("baza");
// include and create object
//include(PHPGRID_LIBPATH."inc/jqgrid_dist.php");
include(PHPGRID_LIBPATH."inc/jqgrid_dist.php");

// Database config file to be passed in phpgrid constructor
/*$db_conf = array(
					"type" 		=> PHPGRID_DBTYPE,
					"server" 	=> PHPGRID_DBHOST,
					"user" 		=> PHPGRID_DBUSER,
					"password" 	=> PHPGRID_DBPASS,
					"database" 	=> PHPGRID_DBNAME
				);
*/
$g = new jqgrid($db_conf);

// set few params
$grid["caption"] = "Sample Grid";
$grid["autoresize"] = true;
$grid["autowidth"] = true;
$grid["resizable"] = true;
$grid["scroll"] = true;

$g->set_options($grid);

// set database table for CRUD operations
$g->table = "csv";

$g->set_actions(array(
						"add"=>true, // allow/disallow add
						"edit"=>true, // allow/disallow edit
						"delete"=>true, // allow/disallow delete
						"showhidecolumns"=>true,
						"rowactions"=>true, // show/hide row wise edit/del/save option
						"autofilter" => true, // show/hide autofilter for search
					)
				);

// render grid
$out = $g->render("list1");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">
<html>
<head>
	<link rel="stylesheet" href="lib/js/themes/ui-lightness/jquery-ui.custom.css"></link>
     <link rel="stylesheet" href="lib/js/jqgrid/css/ui.jqgrid.css"></link>
     <script src="lib/js/jqgrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
     <script src="lib/js/jqgrid/js/jquery.jqGrid.min.js" type="text/javascript"></script>
     <script src="lib/js/themes/jquery-ui.custom.min.js" type="text/javascript"></script>

</head>
<body>
	<div id="mk" >
	<?php echo $out?>
	</div>

	<script>
		jQuery(window).bind("resize", function () {

			var gid = "list1";

			var oldWidth = jQuery("#"+gid).jqGrid("getGridParam", "width");

			if (oldWidth < 500)
			{
				jQuery("#"+gid).jqGrid("hideCol","company");
				jQuery("#"+gid).jqGrid("hideCol","gender");
			}
			else
			{
				jQuery("#"+gid).jqGrid("showCol","company");
				jQuery("#"+gid).jqGrid("showCol","gender");
			}

		}).trigger("resize");

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
