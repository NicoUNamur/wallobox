<?php
    include('class/auth.inc.php');
    require_once('../class-gen/db.class.php');
//$iMicroTime=microtime_float();
    $do_gzip_compress=Header_Compress();
	//--------------------------------------------------------------------------------------------------
    session_name(PREFIX.'auth');
    session_start();
//--------------------------------------------------------------------------------------------------
$db=t_DB::getInstance($MySqlServer, $MySqlLogin, $MySqlPass,$MySqlDatabase);

//------------------------------------------------------------------------------
$mode=isset($_REQUEST['mode'])?$_REQUEST['mode']:'html';
    $ret=$mode=='<ul>';
	$param='';
$arrayName=array('id_objet2'=>'objet',
				 'id_objet'=>'v_stock_professionnel',
				 'id_utilisateur'=>'utilisateur',
				 'id_statut'=>'statut',
				 'id_organisme'=>'organisme',
				 'id_profil'=>'profil',
				 'id_type_objet'=>'type_objet',
				 'id_colis'=>'03colis',
				 'id_benef'=>'02beneficiaire',
				 'id_contact'=>'01benef_contact',
				 'id_contact2'=>'01benef_contact'
				 );

foreach( $arrayName as $key => $value)
	if(isset($_REQUEST['edt'.$key]) || isset($_REQUEST['filter_edt'.$key])){
		$field=$key;
		$param=isset($_REQUEST['edt'.$key])?$_REQUEST['edt'.$key]:$_REQUEST['filter_edt'.$key];
		$name=$value;
		//spécifique
		if($name=='v_stock_professionnel')
			$req='SELECT id , CONCAT(nom_obj,\'(reste:\',qt_total,\')\') nom , photo FROM '.PREFIX.$name.' WHERE nom_obj like \'%'.$param.'%\' LIMIT 10;';
		else
		//fin spécifique
			$req='SELECT * FROM '.PREFIX.$name.' WHERE nom like \'%'.$param.'%\' LIMIT 10;';
		$db_search=$db->RequestDB( $req ,'req_suggestions');
		
		$data=array('Liste'=>array(),'Field'=> $field );
		while($res=$db->GetLigneDB($db_search)){
			$ret.='<li>'.$res['nom'].(isset($res['photo'])&&$res['photo']!=''?'<img src="'.$res['photo'].'" class="vignette" />':'')
									.'(id:'.$res['id'].')</li>';
		
			$tmp=array('value'=>$res['id'] , 'label'=>$res['nom'] , 'icon'=> isset($res['photo'])?$res['photo']:'');
			$data['Liste']=array_merge($data['Liste'],array($tmp) );
		}
	}
    $ret.='</ul>';
    echo $mode=='html'?$ret: json_encode($data);
	session_write_close();
    Footer_Compress($do_gzip_compress);
?>