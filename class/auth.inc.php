<?php
 $MySqlDatabase = 'labo';
 $MySqlServer   = 'localhost';
 $MySqlLogin    = 'masoireerpgphp';
 $MySqlPass     = 'nicopapou';

 define("PREFIX", "labo_");

//----------------------------------------------------------------------
function LanceDe($nb,$face){
    $result=0;
    for($i=0;$i<$nb;$i++){
		$de[$i]=(mt_rand()%$face)+1;
		$result+=$de[$i];
    }
    return $result;
}
//----------------------------------------------------------------------
function sprinta($obj){
 global $__level_deep;
 $sortie = "";
 if (!isset($__level_deep))
	  $__level_deep = array();
  if (is_object($obj)){
	$sortie.= '[obj:'.get_class($obj).']';//.$obj;
  }
  elseif (is_array($obj)) {
	  foreach(array_keys($obj) as $keys) {
		  array_push($__level_deep, "[".$keys."]");
		  $sortie.=sprinta($obj[$keys]);
		  array_pop($__level_deep);
		  $sortie.="<br />\n";
	  }
  }
  else
  	  $sortie.=implode(" ", $__level_deep)." = $obj";
	return $sortie;
}
//----------------------------------------------------------------------
function Header_Compress(){
   $phpver = phpversion();
   $useragent = (isset($_SERVER['HTTP_USER_AGENT']) ) ? $_SERVER['HTTP_USER_AGENT'] : $HTTP_USER_AGENT;
   if ( $phpver >= '4.0.4pl1' && ( strstr($useragent,'compatible') || strstr($useragent,'Gecko') ) ){
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
	       return TRUE;
           }
       }
   }
   return FALSE;
}
//----------------------------------------------------------------------
function Footer_compress( $do_gzip_compress ){
if ( $do_gzip_compress ){
   //
   // Borrowed from php.net!
   //
   $gzip_contents = ob_get_contents();
   ob_end_clean();

   $gzip_size = strlen($gzip_contents);
   $gzip_crc = crc32($gzip_contents);

   $gzip_contents = gzcompress($gzip_contents, 9);
   $gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);

   echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
   echo $gzip_contents;
   echo pack('V', $gzip_crc);
   echo pack('V', $gzip_size);
}
exit;
}
//----------------------------------------------------------------------
function print_debug($p_var){
		echo '<textarea cols=22 rows=10>';
		var_dump($p_var);
		echo '</textarea>';

}
//======================================================================
function SendMailInscr($pId,$pNiss=''){
	$db=t_DB::getInstance();

	$db_req_perso=$db->RequestDB("SELECT id,nom,email,id_citoyen FROM `".PREFIX."utilisateur` WHERE id='".$pId."';",'req_email');
	while($res=$db->GetLigneDB($db_req_perso) ){
		//print_r($res);
		$password=genererMDP(8);
		$db->RequestDB("UPDATE `".PREFIX."utilisateur` SET password=PASSWORD('".$password."') WHERE id=".$pId.";",'updt_passwd');

		$body="<html><body>
Bonjour ".$res['nom'].",<br />
<br />
Bienvenue dans le programme WalloBox.<br />
<br />
Votre identifiant est votre adresse email ( ".$res['email']." ).<br/>
<br />
Votre mot de passe est le suivant pour le serveur de test: test<br />Vous pourrez en changer dans l'application.<br />
L'adresse d'accès au programme en version de test/formation est : <a href=\"http://www.ma-soiree.be/wallobox\">http://www.ma-soiree.be/wallobox</a><br />
<br />
Votre mot de passe est le suivant pour le serveur de production: ".$password."<br />Vous pourrez en changer dans l'application.<br />
L'adresse d'accès au programme en version de production (attention, données réelles) est : <a href=\"http://www.ma-soiree.be.be/\">http://www.ma-soiree.be</a><br />
<br />
Cordialement<br />
<br />
Nicolas<br />
<br />
</body></html>";

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: wallobox@ma-soiree.be' . "\r\n";
		echo $body;

		//mail($res['email'],"[WalloBox] Bienvenue",$body,$headers);
	}

}
//----------------------------------------------------------

?>