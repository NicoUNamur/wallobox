<?php

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
function ExistInDB($tabl,$champ,$val){
	$res=$db->GetLigneDB( $db->RequestDB("SELECT id FROM $tabl WHERE $champ='$val'") );
	return $res[0];
}
//----------------------------------------------------------------------
function ValidEmail($email){
//repiqué sur nexen.net
  $email = strtolower($email);

  if(ExistInDB('users','email',$email) || ExistInDB('users','login',$email) ){
	echo"L'adresse email est déja utilisée dans la base de donnée<br>";
	return -1;
  }
  if (strlen($email) < 6){
  	echo"$email : E-Mail trop court";
  	return 0;
  }
  if (strlen($email) > 255){
  	echo"$email : E-Mail trop long";
  	return 0;
  }
  if (!ereg("@", $email)){
  	echo"$email : L'E-Mail n'a pas d'arobase(@)";
  	return 0;
  }
  if (preg_match_all("/([^a-zA-Z0-9_\@\.\-])/i", $email, $trouve)){
  	echo"$email : caractère(s) interdit dans un E-Mail(".implode(", ", $trouve[0]).").";
  	return 0;
  }
  if (!preg_match("/^([a-z0-9_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,4}\$/i", $email))
  {
	echo"$email : ce n'est pas un la forme d'un email.";
  	return 0;
  }
  list($compte, $domaine)=split("@", $email, 2);
  if (!checkdnsrr($domaine, "MX")){
  	echo"$email : Ce domaine ($domaine) n'accepte pas les E-Mails";
  	return 0;
  }
  return 1;
}
//----------------------------------------------------------------------
function CreateResizedImage($from_file,$width,$height,$to_file,$type,$proportion=0,$addr=0){
//	Header("Content-type: image/jpeg");
switch(exif_imagetype ( $from_file )){
	case '1':
		$src_im = imagecreatefromgif($from_file);
//$type='gif';
		break;
	case '2':
		$src_im = loadjpeg($from_file);
		break;
	case '3':
		$src_im = imagecreatefrompng($from_file);
		break;
	default:
		return 0;
}
	$src_w = imageSX($src_im);
	$src_h = imageSY($src_im);

	$dst_h = floor($height);
if($proportion!=0)
	$dst_w = round($width);
else
	$dst_w = round( ($dst_h*$src_w)/$src_h );



	/* ImageCreateTrueColor crée  une image noire en vraie couleurs */
	$dst_im = ImageCreateTrueColor($dst_w,$dst_h);

	/* ImageCopyResampled copie et rééchantillonne l'image originale*/

	ImageCopyResampled($dst_im,$src_im,0,0,0,0,$dst_w,$dst_h,$src_w,$src_h);

	/* ImageJpeg génère l'image dans la sortie standard (c.à.d le navigateur).
	Le second paramètre est optionnel ; dans ce cas, l'image est générée dans un fichier*/

if($addr)PutAdresse($dst_im);

switch($type){
	case 'jpg':
	case 'jpeg':
		ImageJpeg($dst_im,$to_file.".jpg");
		break;
	case 'gif':
		if(file_exists($path."images/resto/$fromfile.gif"))
			unlink ($path."images/resto/$fromfile.gif");
		rename ( $from_file , $to_file.".gif" );
	//	ImageGif($dst_im,$to_file.".gif");
		break;
	case 'png':
		ym_trans($dst_im.".png");
		ImagePng($dst_im,$to_file);
		break;
	case 'wbmp':
		// header("IMAGE/VND.WAP.WBMP");
		ImageWBMP($dst_im,$to_file.".wbmp");
		break;
	default:

}
	ImageDestroy($dst_im);
	imageDestroy($src_im);
}
//----------------------------------------------------------------------
function loadjpeg($imgname) { // source : nexen.net
  $im = @imagecreatefromjpeg($imgname); /* Tentative d'ouverture */
  if (!$im) { /* Vérification */
    $im = imagecreate(150, 30); /* Création d'une image blanche */
    $bgc = imagecolorallocate($im, 255, 255, 255);
    $tc  = imagecolorallocate($im, 0, 0, 0);
    imagefilledrectangle($im, 0, 0, 150, 30, $bgc);
// Affichage d'un message d'erreur
    imagestring($im, 1, 5, 5, "Erreur de chargement de l'image $imgname", $tc);
  }
  return $im;
}
//----------------------------------------------------------------------
function ym_trans($im){ //source : nexen.net
 //codé par yannick | webmaster@henna-tatoo.net //
 //la fonction n'a besoin que de l'image comme argument
 //on pipe le coin de l'image
 $c_fond = imagecolorat($im, 1, 1);
 // on change les occurences $c_fond en transparent
 $trans=imagecolortransparent($im, $c_fond);
}
//----------------------------------------------------------------------
function UploadFich($fich,$id_resto){
//printa($_FILES);
//printa($res);
//printa($_FILES[$res[8]]);

	if( is_uploaded_file ( $fich ) ){
//echo"<br><br>Fichier chargé!!!<br><br>";
		chmod($fich,0666);
		$nomfich=$path."images/resto/$id_resto_.jpg";
		move_uploaded_file ($fich,$nomfich);
		CreateResizedImage($nomfich,400,300,$nomfich);
	}
}
//----------------------------------------------------------------------
function microtime_float(){
    list($usec,$sec)=explode(" ",microtime());
    return ((float)$usec+(float)$sec);
}
//----------------------------------------------------------------------
?>