<?php
require_once('class-gen/db.class.php');
//--------------------------------------------------------------------------------------------------
//Compression GZIP de la page
   $phpver = phpversion();
   $useragent = (isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : $HTTP_USER_AGENT;
   $do_gzip_compress=FALSE;
/*   if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) ){
       if ( extension_loaded('zlib') ){
           ob_start('ob_gzhandler');
       }
   }
   else if ( $phpver > '4.0' ){
       if ( strstr($HTTP_SERVER_VARS['HTTP_ACCEPT_ENCODING'], 'gzip') ){
           if ( extension_loaded('zlib') ){
               //$do_gzip_compress = TRUE;
               ob_start();
               ob_implicit_flush(0);
               header('Content-Encoding: gzip');
	           $do_gzip_compress=TRUE;
           }
       }
   }
*/
//--------------------------------------------------------------------------------------------------
	require_once('class/auth.inc.php');
	$iMicroTime=microtime_float();

    session_name(PREFIX.'auth');
    session_start();
//--------------------------------------------------------------------------------------------------
$db=t_DB::getInstance($MySqlServer, $MySqlLogin, $MySqlPass,$MySqlDatabase);

$deco=0;

if( isset($_SESSION[PREFIX.'id']) && $_SESSION[PREFIX.'id']){
	if( $action=='quit' || (!$moi->GetSessionValid($_COOKIE[PREFIX.'auth']) && $_SESSION[PREFIX.'id']!=0) ){
		//echo "$action=='quit' || (!moi->GetSessionValid(".$_COOKIE[PREFIX.'auth'].") && ".$_SESSION[PREFIX.'id']."!=0)";
		unset($_SESSION[PREFIX.'id']);
		$deco=1;
	}
}
$ajax= (isset($_REQUEST['ajax']) );

if(!$ajax){
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
<head>
	<meta http-equiv="content-type" content="text/html; charset=utf-8" />
	<title>WalloBox</title>
<link rel="stylesheet" type="text/css" href="default.css.php" media="screen">
<link rel="stylesheet" type="text/css" href="print.css.php" media="print">
	
	<script type="text/javascript" src="/js/jquery.ui.autocomplete.html.js"></script>
	
	<script type="text/javascript" src="/js/jquery.datetimepicker.full.min.js"></script>
	<link rel="stylesheet" type="text/css" href="/js/jquery.datetimepicker.min.css"/>
	
	<script type="text/javascript" src="custom.js.php?v=20171021" defer="defer"></script>
	
	<link rel="icon" type="type/ico" href="favicon.ico" />
	<link rel="shortcut icon" href="favicon.ico" />
<!-- [if lt IE9]>
<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
<![endif]-->
</head>
<body>
<?php
}
?>