<?php
/**
 * Charts 4 PHP
 *
 * @author Shani <support@chartphp.com> - http://www.chartphp.com
 * @version 1.2.3
 * @license: see license.txt included in package
 */
 
 define("CHARTPHP_DBNAME","northwind");

define("CHARTPHP_DBTYPE","pdo");
define("CHARTPHP_DBHOST","sqlite:../../sampledb/Northwind.db");
define("CHARTPHP_DBUSER","");
define("CHARTPHP_DBPASS","");
define("CHARTPHP_DBNAME","");

// Basepath for lib
define("CHARTPHP_LIBPATH",dirname(__FILE__).DIRECTORY_SEPARATOR."lib".DIRECTORY_SEPARATOR);


// PHP Grid database connection settings
define("PHPGRID_DBTYPE","{{dbtype}}"); // or mysqli
define("PHPGRID_DBHOST","{{dbhost}}");
define("PHPGRID_DBUSER","{{dbuser}}");
define("PHPGRID_DBPASS","{{dbpass}}");
define("PHPGRID_DBNAME","{{dbname}}");

// Automatically make db connection inside lib
define("PHPGRID_AUTOCONNECT",0);

// Basepath for lib
define("PHPGRID_LIBPATH","/www/lib");
