<?php
//redirige vers le HTTPS si la connexion n'est pas sécurisée
if(isset($_SERVER['HTTPS'])){
    if ($_SERVER['HTTPS'] == "on") {}else{ header('Location: https:'.$_SERVER['HTTP_HOST']);exit;}
}else{ header('Location: https:'.$_SERVER['HTTP_HOST']);exit;}


include('mod/page_header.inc.php');

unset($_SESSION['debug']);
unset($_SESSION['debug_sql']);

//$_SESSION['debug_sql']=true;
//$_SESSION['debug']=true;

require_once('class-gen/objet.class.php');
$iMicroTime=microtime_float();

echo'<div id="body">';

if(isset($_REQUEST['debug']))$_SESSION['debug']=$_REQUEST['debug'];
if(isset($_REQUEST['debug_sql']))$_SESSION['debug_sql']=$_REQUEST['debug_sql'];
?>
<h1><span>WalloBox</span>&nbsp;</h1>
<?php
$action=isset($_REQUEST['action'])?$_REQUEST['action']:'';
	if( $action=='logout'){
		unset($_SESSION['idLogin']);
		unset($_SESSION['idOrg']);
		unset($_SESSION['idProfil']);
		echo'Utilisateur d&eacute;connect&eacute;';
	}

	$tables = new tTable();
	//echo $tables;

	$filter = null;
	if(! isset($_SESSION['idLogin']) ){
		if(isset($_POST['email']) &&  isset($_POST['password']) ){
			$filter['email']	= $_POST['email'];
			$filter['password']	= $_POST['password'];

			$droit = new tDroitsAcces( );
			$obj=new tObj( $droit , $tables , 'utilisateur' , $filter );
			if( $obj->getNbResult() ){
				if( $obj->isActif() ){
					$_SESSION['idLogin']		=$obj->getId();
					$_SESSION['idOrg']			=$obj->getOrg();
					$_SESSION['idProfil']['usr']=$obj->getProfil();
					echo 'Bienvenue '.$obj->getNom($obj->getId() );
				}
				else{
					echo '<div class="erreur">Votre compte a été désactivé. Veuillez prendre contact avec ...</div>';
				}
			}
			else
				echo '<div class="erreur">Login ou mot de passe incorrect</div>';
			$droit->reset();
		}
	}
	if(! isset($_SESSION['idLogin']) ){
		?>
<fieldset id="content"><legend>Contenu</legend>
		<div id="login">
<?php 
switch($action){
	case 'inscr':
		if(isset($_POST['email'])){
//validation NISS			
	/*if( 97-(substr($_POST['niss'],0,9)%97)==substr($_POST['niss'],9,2)+0 );
	else{ $err="NISS invalide";
		break;
	}*/
			echo 'Formulaire envoyé, vous recevrez bientôt un demande de confirmation par e-mail à l\'adresse : '.$_POST['email'];
			$db=t_DB::getInstance();
			$niss=CallAPI("PUT", "https://91.121.217.193/validation/v1/nrn/".$_POST['niss'], $data = false, "groupe4:z6U3hb8giD" );
echo print_r($niss);

			$req='INSERT INTO '.PREFIX.'utilisateur (nom,email,password,id_citoyen,id_organisme)  (SELECT nom,\''.$_POST['email'].'\',\'test\',id,id_commune FROM '.PREFIX.'citoyen WHERE niss_hash=PASSWORD(\''.$_POST['niss'].'\'))';
//echo $req;
			$db_var=$db->RequestDB($req,'Ïnscript');
			$id=$db->LastId($db_var);
			SendMailInscr($id);
		}else{
		?><form action="?action=inscr" method="POST">
		<p><label for="email">email</label><input name="email"/></p>
		<p><label for="niss">niss</label><input name="niss"/></p>
		<input type="submit" />
		</form><?php
		}
		break;
	case 'lostpwd':
		?><form action="?action=lostpwd" method="POST">
		<p><label for="email">email</label><input name="lostpwd"/></p>
		<input type="submit" />
		</form><?php
		break;
	default:
		?>
		<form action="?" method="POST">
		<p><label for="email">Nom d'utilisateur</label><input name="email" /></p>
		<p><label for="password">Mot de passe</label><input name="password" type="password" /></p>
		<input type="submit" />
		</form>
		<?php 
}
?>
		</div>
</fieldset>
		<nav>
			<fieldset><legend>Menu</legend>
			<ul>
			<li><a href="?"					>Accueil</a></li>
			<li><a href="?action=inscr"		>S'inscrire</a></li>
			<li><a href="?action=lostpwd"	>Mot de passe oublié</a></li>
			</ul>
			</fieldset>
		</nav>

		<?php
	}
	//si conecté avec un user valide :
	else{
		$droit = new tDroitsAcces( $_SESSION['idLogin'] , $_SESSION['idOrg'] , $_SESSION['idProfil'] );
//echo '<pre>',print_r($droit->getDroits() ),'</pre>';
		?>
		<nav><fieldset><legend>Menu</legend><ul>
		<?php
			$action=isset($_REQUEST['action'])?$_REQUEST['action']:null;
			$table=isset($_REQUEST['table'])?$_REQUEST['table']:null; 
			$view=isset($_REQUEST['view'])?$_REQUEST['view']:'tab'; 
			$page=isset($_REQUEST['AfficherPage'])?$_REQUEST['AfficherPage']:'0';
			$nbLigneParPage=isset($_REQUEST['NbLigneParPage'])?$_REQUEST['NbLigneParPage']:'10';
			$tableFrom=isset($_REQUEST['tableFrom'])?$_REQUEST['tableFrom']:'panier';
			$tableBck=isset($_REQUEST['tableBck'])?$_REQUEST['tableBck']:$table;
			unset($_REQUEST['tableBck']);
		echo $tables->getMenu($droit,$table);
		?>
		<li><a href="?action=logout">D&eacute;connexion</a></li>
		</ul></fieldset></nav>
		<div id="content">
		<fieldset>
		<legend>Contenu</legend>
		<?php
			foreach( $_REQUEST as $key => $v ){
				switch($key){
					case '60gp':
					case '60gpBAK':
					case '300gp':
					case '300gpBAK':
					case PREFIX.'auth':
					case 'table':
					case 'view':
					case 'action':
					case 'debug':
					case 'debug_sql':
					case 'AfficherPage':
					case 'NbLigneParPage':
					case 'tableFrom':
					case 'tableBck':
						break;
					default:
						if($v)$filter[$key]=$v;
				}
			}
			if($table){
				if($table=='00accueil'){echo 'Mes notifications<br/>'; $filter['id_utilisateur'] = $_SESSION['idLogin'];}

				if($action=='del' && isset($filter['id']) ){
					$filterDel['id']=$filter['id'];
					unset($filter['id']);
				}else
					$filterDel=null;
				$obj=new tObj( $droit , $tables , $table , $filter , true );
				//if( ! $action )

				if($table=='panier')$filter=null;

				echo $obj->getDDLFilter($table , $filter );
				$ret='';
				switch($action){
					case 'new':
					case 'edit':
						$ret= $obj->FormModif($action ,null, $view , $filter , $page , $nbLigneParPage);
						break;
					case 'save':
						$ret= $obj->FormSave( $filter );
						if($ret=='')$ret.= $obj->FormModif( 'new' ,null,null,$pFilter);
						break;
					case 'savenew':
						$ret= $obj->FormSave( $filter );
						if($table=='panier'){
							$newObj = new tObj(  $droit , $tables , 'v_stock_professionnel' , array() , true );
							$ret.=$newObj->FormPrint(null,'tab', $filter);
						}
						else {
							$newObj=$obj;
							$ret.=$obj->FormPrint(null,'tab', $filter);
						}
						break;
					case 'del':
						$ret='';
					//echo print_r($filterDel );
						//echo print_r($filter);
						if($table=='panier'){
							$filterDel['created_by']=$_SESSION['idLogin'];
							$filter='created_by='.$_SESSION['idLogin'];
						}
						$ret= $obj->Suppr( $filterDel , $filter );
						if($table=='panier'){
							$newObj = new tObj(  $droit , $tables , 'v_stock_professionnel' , array() , true );
							$ret.=$newObj->FormPrint(null,'tab', $filter);
						}
						else {
							$newObj=$obj;
							$ret.=$obj->FormPrint(null,'tab', $filter);
						}
						break;
					case 'newpassword':
						if( $table=='utilisateur' )
							SendMailInscr($filter['id']);
						break;
					case 'info':
						phpinfo();
						break;
					case 'transfert':
						$ret= $obj->Transfert($tableFrom,$_SESSION['idLogin'],$table,$_REQUEST['id_colis']); //action=transfert&tableFrom=panier&table=04objet_colis&id_colis='.$_REQUEST['id_colis'].'
						break;
					default:
						$ret= $obj->FormPrint( null,$view , $filter , $page , $nbLigneParPage);
				}
				echo $ret;
			}else{
				switch($action){
					case 'sendmailinscript':
						SendMailInscr($_REQUEST['id']);
						break;
					default:
						echo 'Mes notifications<br/>';
						unset($filter);
						$filter['id_utilisateur'] = $_SESSION['idLogin'];
						$obj=new tObj( $droit , $tables , '00accueil', $filter );
						echo $obj;//->PrintForm('tab');
				}
			}
//echo '<pre>',print_r($_SERVER),'</pre>';
		?>
		</fieldset></div>
		<?php
	}
echo $db;
if(isset($_SESSION['debug'])    && $_SESSION['debug']     ==1 ){$db=t_DB::getInstance(); echo $db.' / Temps de la page:'.(microtime_float()-$iMicroTime);}
if(isset($_SESSION['debug_sql'])&& $_SESSION['debug_sql'] ==1 ){$db=t_DB::getInstance(); echo $db.' / Temps de la page:'.(microtime_float()-$iMicroTime);}

echo'</div>';
	include('mod/page_footer.inc.php');
?>
