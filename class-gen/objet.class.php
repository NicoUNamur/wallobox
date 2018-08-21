<?php
require_once('class-gen/db.class.php');
class tTable{
	/****************************************************************************
	*	Class tTable
	*****************************************************************************
	*	Description :
	*		Lien entre la structure des tables de la base de donnée et un objet
	*
	*****************************************************************************
	*	Fonctions :
	*		$data 							: données de la table (indice 1 : nom de la table, indice 2 = id, indice 3 = champ)
	*		__construct( $pTable=null )		: constructeur ayant en paramètre le nom d'une table
	*		getC( $pTableName , $pParent )	: lien entre une colonne d'une table et la table
	*		getMenu($pDroit)				: affiche la liste des tables (menu) auquel l'utilisateur à accès
	*		getComment($pTable)				: affiche le nom littéraire d'une table (basé sur le commentaire de la table)
	*		isView($pTable)					: renvoie un booléen, vrai si la table est une vue
	*		__toString()					: non utilisé : sert au debug
	*
	*
	*
	****************************************************************************/

	static private $data;
//----------------------------------------------------------------------
	function __construct( $pTable=null ){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$db=t_DB::getInstance();
		if( !$pTable || !isset(self::$data[$pTable] ) ){
			if($pTable)
				$req=sprintf("SHOW TABLE STATUS WHERE name='".PREFIX."%s';", $pTable );
			else
				$req="SHOW TABLE STATUS WHERE name like '".PREFIX."%';";
			$db_var=$db->RequestDB($req,'req_'.$pTable );
			while( $res_var=$db->GetLigneDB($db_var) ){
				$res_var['Name']=substr($res_var['Name'], strlen( PREFIX ) );
				self::$data[ $res_var['Name'] ]=$res_var;
			}
		}
	}
//----------------------------------------------------------------------
	function getC( $pTableName , $pParent ){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( ! ($pParent?isset( self::$data[ $pTableName ]['columnsP']):isset(self::$data[ $pTableName ]['columns']) ) ){
			//charge les colonnes lorsqu'on en a besoin
			self::$data[$pTableName][($pParent?'columnsP':'columns')] = new tColumn( $pTableName , $this->isView($pTableName) );
		}
		return $pParent? ( self::$data[ $pTableName ]['columnsP']) : ( self::$data[ $pTableName ]['columns']) ;
	}
//----------------------------------------------------------------------
	function getMenu($pDroit,$table){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';
		foreach( self::$data as $k => $v ){
		//echo'<pre>',print_r($pDroit->getDroitsAcces( $k )),'</pre>';
			if( $pDroit->getDroitsAcces( $k ) )
			//if( $this->getDroitOnTable( $k ) )
				$ret.='<li'.(($table==$k || $table=='' && $k == '00accueil' )?' class="selnav"':'').'><a href="?table='. $k .'">'.$this->getComment($k).'</a></li>'."\n";
		}	
		return $ret;
	}
//----------------------------------------------------------------------
	function getComment($pTable){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		new tTable($pTable);
		if( isset(self::$data[$pTable]['Comment']) )
			return $this->isView($pTable)?substr(self::$data[$pTable]['Name'],2):self::$data[$pTable]['Comment'];
		else
			return null;
	}
//----------------------------------------------------------------------
	function isView($pTable){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( isset(self::$data[$pTable]['Comment']) )
			return self::$data[$pTable]['Comment']=='VIEW';
		else
			return null;
	}
//----------------------------------------------------------------------
	function __toString(){
		//echo '<pre>',print_r(self::$data),'</pre>';
		foreach( self::$data as $k => $v )
			if(isset(self::$data[$k]['columns']) )
				echo self::$data[$k]['columns'];
				//foreach( self::$data[$k]['columns'] as $key => $v2 )
				//	echo $key.' -> '.$v2;
		return __METHOD__ ;
	}
//----------------------------------------------------------------------
}

class tColumn{
	/****************************************************************************
	*	Class tColumn
	*****************************************************************************
	*	Description :
	*		Lien entre la structure d'une colonne d'une table de la base de donnée et un objet
	*
	*****************************************************************************
	*	Fonctions :
	*		$table							: nom de la table concernée
	*		$data 							: données des colonnes (indice 1 : ['column'], indice 2 : nom de la table, indice 3 = champ)
	*		__construct( $pTable )			: constructeur ayant en paramètre le nom d'une table
	*		get($pColumnName=null,$pParent=false)	
	*										: retourne les informations concernant une colonne (ou si null, toutes les colonnes), 
	*											et la contrainte de cette colonne si pParent est spécifiée.
	*		__toString()					: non utilisé : sert au debug
	*
	*
	*
	****************************************************************************/
	private $table;
	static private $data;
//----------------------------------------------------------------------
	function __construct( $pTable ,$pView=false ){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$this->table = $pTable;

		if( ! ( isset(self::$data['column'][$this->table]) ) ){
			$db=t_DB::getInstance();
			$req=sprintf("SHOW full columns from %s.`".PREFIX."%s`;",$db->getDataBase(), $this->table );
			$db_var=$db->RequestDB($req,'req_'. $this->table );
			$req2=sprintf("SELECT TABLE_NAME,COLUMN_NAME,REFERENCED_TABLE_NAME,REFERENCED_COLUMN_NAME
					FROM information_schema.KEY_COLUMN_USAGE k WHERE k.table_schema = '%s' 
					AND '".PREFIX."%s' IN (k.REFERENCED_TABLE_NAME , k.TABLE_NAME) "
					."AND k.REFERENCED_TABLE_SCHEMA is not null
					;",$db->getDataBase(), $this->table );
			$db_var2=$db->RequestDB($req2,'req_'. $this->table );
			
			$res_var2=array();
			for($k=0;$res_var2[$k]=$db->GetLigneDB($db_var2); $k++);


			$missingColumn['last_update_by']	=true;
			$missingColumn['created_by']		=true;
			$missingColumn['last_update_time']	=true;
			$missingColumn['created_time']		=true;
			while( $res_var=$db->GetLigneDB($db_var) ){
			
				//if(1==1){
				switch($res_var['Field']){
					case 'created_by':
					case 'last_update_by':
					case 'created_time':
					case 'last_update_time':
						$missingColumn[$res_var['Field']]=false;
					default:
				foreach( $res_var2 as $k2 => $v ){
					if(	   ( $v['REFERENCED_TABLE_NAME'] ==PREFIX.$this->table && $v['REFERENCED_COLUMN_NAME']==$res_var['Field'] )
						|| ( $v['TABLE_NAME']            ==PREFIX.$this->table && $v['COLUMN_NAME']           ==$res_var['Field'] )
					){
						$res_var['TABLE_NAME'][$k2]				=$v['TABLE_NAME'];
						$res_var['COLUMN_NAME'][$k2]			=$v['COLUMN_NAME'];
						$res_var['REFERENCED_TABLE_NAME'][$k2]	=$v['REFERENCED_TABLE_NAME'];
						$res_var['REFERENCED_COLUMN_NAME'][$k2]	=$v['REFERENCED_COLUMN_NAME'];

						if( $v['TABLE_NAME'] && !$res_var['Comment'] ){
							$sql_parent=sprintf("SHOW full columns FROM %s.`%s` WHERE Field='%s';",$db->getDataBase(),$v['TABLE_NAME'],$v['COLUMN_NAME']);
							$db_var_p=$db->RequestDB($sql_parent,'req_'.$v['TABLE_NAME'] );
							if( $res_var_p=$db->GetLigneDB($db_var_p) ){
								$res_var['CommentP'][$k2]=$res_var_p['Comment'];
							}
						}
						elseif( $v['TABLE_NAME'] && $res_var['Comment'] ) $res_var['CommentP'][$k2]=$res_var['Comment'];
/*
		$req3=sprintf("SHOW INDEX FROM %s.`%s`;",$db->getDataBase(), 
						($v['REFERENCED_TABLE_NAME'] ==PREFIX.$this->table && $v['REFERENCED_COLUMN_NAME']==$res_var['Field'])
						?$res_var['TABLE_NAME'][$k2]
						:$res_var['REFERENCED_TABLE_NAME'][$k2]
						);
		$db_var3=$db->RequestDB($req3,'req_'. $this->table );
		while( $res_var3 = $db->GetLigneDB($db_var3) )if( $res_var3['Column_name'] == $v['COLUMN_NAME'] )$res_var['genre'][$k2] = $res_var3['Index_comment'];
*/
						if(	$v['REFERENCED_TABLE_NAME'] ==PREFIX.$this->table && $v['REFERENCED_COLUMN_NAME']==$res_var['Field']){
							self::$data['columnP'][$this->table][ $res_var['Field'] ]=$res_var;
						}


					}
				}
				self::$data['column' ][$this->table][ $res_var['Field'] ]=$res_var;
				}
			}
		}
//Créer les colonnes manquantes + indexes + contraintes référentielles (lien vers l'utilisateur)
		if( !$pView )
		if( isset($missingColumn) && is_array($missingColumn) )
		foreach($missingColumn as $k=>$v){
			if($v){
				$req=sprintf('ALTER TABLE %s ADD %s '.(substr($k,strlen($k)-strlen( '_time' ),strlen( '_time' ))!='_time'?'INT NOT NULL DEFAULT \'1\'':('TIMESTAMP NOT NULL')).';', PREFIX.$this->table , $k );
				$db_var=$db->RequestDB($req,'req_'. $this->table );

				if( $this->table == '03colis' || $this->table == 'panier' ){
					switch($k){
						case 'created_by':
						case 'last_update_by':
							$req=sprintf('UPDATE `%s` SET `%s`=`id_utilisateur`;', PREFIX.$this->table , $k );
							break;
						case 'created_time':
						case 'last_update_time':
							if( $this->table == '03colis' )
								$req=sprintf('UPDATE `%s` SET `%s`=`date`;', PREFIX.$this->table , $k );
							break;
						default:
					}
					$db_var=$db->RequestDB($req,'req_'. $this->table );
				}

				if(substr($k,strlen($k)-strlen( '_by' ),strlen( '_by' ))=='_by'){
					$req=sprintf('ALTER TABLE `%s` ADD INDEX(`%s`);', PREFIX.$this->table , $k );
					$db_var=$db->RequestDB($req,'req_'. $this->table );
					$req=sprintf('ALTER TABLE `%s` ADD  FOREIGN KEY (`%s`) REFERENCES `'.PREFIX.'utilisateur`(`id`) ON DELETE RESTRICT ON UPDATE RESTRICT;', PREFIX.$this->table , $k );
					$db_var=$db->RequestDB($req,'req_'. $this->table );
				}
			}
		}
	}
//----------------------------------------------------------------------
	function get($pColumnName=null,$pParent=false){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( isset(self::$data[$pParent?'columnP':'column']) ){
			//echo ($pParent?'columnP':'column').'->'.$this->table.'<br/>'."\n";
//echo $pColumnName.'<br/>';
			switch($pColumnName){
				default:
					if(                      isset(self::$data[$pParent?'columnP':'column'][$this->table][ $pColumnName ]) 
					    || (!$pColumnName && isset(self::$data[$pParent?'columnP':'column'][$this->table]) ) ){
						//echo ($pParent?'columnP':'column') . ' '. $this->table.' '.$pColumnName.'<br/>';
						return //$ret. 
								($pColumnName
								?self::$data[$pParent?'columnP':'column'][$this->table][ $pColumnName ]
								:self::$data[$pParent?'columnP':'column'][$this->table]
								//:@self::$data[$pParent?'columnP':'column'][$this->table]
								);
					}
			}
		}
		return null;
	}
//----------------------------------------------------------------------
	function __toString(){
		//echo '<pre>',print_r(self::$data),'</pre>';
		return __METHOD__ ;
	}
//----------------------------------------------------------------------
}

class tObj{
	/****************************************************************************
	*	Class tObj
	*****************************************************************************
	*	Description :
	*		Lien entre un objet et la base de données, en se servant de tTable et tColumn comme structure, et en filtrant avec tDroitsAcces
	*
	*****************************************************************************
	*	Variables
	*		$nomTable						: nom de la table concernée
	*		$data 							: données des colonnes (indice 1 : ['column'], indice 2 : nom de la table, indice 3 = champ)
	*		$structTables					: structure des tables
	*		$filterTab						: filtre sous forme de tableau
	*		$filter							: filtre sous forme de texte
	*		$req							: requêtes ayant servi à constituer cet objet
	*		$sum							: sommes des colonnes étant des doubles
	*		$droitAll						: tous les droits
	*		$droit							: droits concernant les colonnes de cette table
	*		$nbLigneParPage					: nombre de lignes affichée par page
	*****************************************************************************
	*	Fonctions :
	*		__construct( $pDroit , $pStruct , $pTable , $pFilter = null , $pMerge=false)
	*										: constructeur chargeant juste les données nécessaires
	*		__toString()					: affiche l'objet sous forme HTML (table)
	*		loadFromDB( )					: charge des informations (pour la 1ere fois) d'une DB
	*		mergeLoadFromDB( )				: complète les informations chargées depuis la DB
	*		reloadFromDB( )					: vide le cache et re-charge les données à partir de la DB
	*		FormatInput($pVal,$pType,$pName): mise en forme d'une entrée selon le type/nom (vers la DB)
	*		FormatOutput($id,$key,$pType,$pMode)	
	*										: mise en forme d'une informe depuis la DB pour être affichée
	*		PrintField($lCol,$key,$id,$pEcriture,$pFilter,$pTypeAff='form')
	*										: affiche le champ selon formulaire ou table, et si modifiable ou pas
	*		FormPrint( $pParent=null,$pView='tab' , $pFilter=null , $pPage=0 , $pNbLigneParPage = 10 )
	*										: Imprime FormModif + bouton
	*		getNom($pId)					: Affiche le nom litérraire de la colonne (tirée du commentaire)
	*		getPhoto($pId)					: Affiche la photo si existante
	*		getParent($pId)					: à spécifier
	*		getLink($pId)					: Affiche le libellé/lien vers un objet en fonction des droits
	*		getDdl($pId,$pTable,$pNomTable,$pTableFrom=null,$pToutesVal=null)
	*										: Affiche une Drop Down List
	*		getOptions($pId)				: Affiche une option de DDL
	*		FormSave($pChamps , $pFilter=null , $pFile=null )
	*										: sauve/crée un enregistrement en fonction des droits
	*		Suppr( $pFilter,$pFilterAff )	: supprime un enregistrement en fonction des droits
	*		getFilter()						: filtre qui a servi a constituer l'objet
	*		getDroitOnThis($pId,$pCol)		: renvoie le droit le plus haut que l'on a sur une colonne, en fct la portée
	*		getDroitOnLine($pId)			: renvoie le scope le plus haut que l'on a sur une ligne
	*		getTableParentBouton( $pFlag , $pId )
	*										: Affiche un bouton en relation avec la table et les droits que l'on a dessus (ajout, modifier, ...)
	*		PrintValue($lCol , $pEcriture , $id, $key )
	*										: choisi la manière d'afficher un champ input (texte area ou input, mot de passe + confirmation, date, ...)
	*		FormModif($pEcriture=null,$pParent=null,$pVue='tab' , $pFilter=null , $pPage=0 , $pNbLigneParPage )
	*										: Affiche le formulaire complet, ou liste (basé sur une table DB)
	*		getNbResult()					: nombre de lignes impactées
	*		getId()							: retourne l'identifiant
	*		getDdlFilter($pTableFrom , $pFilter=null)
	*										: filtre sur base des éléments filtrables (est parent de...)
	*		getAjoutBouton( $pFilter=null )	: bouton 'ajouter' avec texte complémentaire sur base du filtre
	*		getDroits($pTable=null)			: 
	*
	*		PrintSelect($enum , $lCol , $id , $key)
	*										: affiche enum sous forme de DDL
	*		PrintRadio($enum , $lCol , $id , $key)
	*										: affiche enum sous forme de Radio
	***spécifique
	*		getOrg()						: organisme
	*		getProfil()						: profil
	*		getQt($pId=null)				: quantité (qt)
	****************************************************************************/
	private $structTables;
	private $struct;
	static private $data;
	static private $dataFork;
	private $fork;
	private $nomTable;
	private $filterTab;
	private $filter;
	private $req;
	
	private $sum;

	private $droitAll;
	private $droit;
	private $currentDroit;
	
	private $nbLigneParPage;
//	private $tableFrom;
//----------------------------------------------------------------------
	function __construct( $pDroit , $pStruct , $pTable , $pFilter = null , $pMerge=false , $pFork=false , $pPage=0 , $pNbLigneParPage=50000 ){
		//pMerge : ajouter résult courant aux data actuelles
		//pFork : Stocke dans un autre enreg les données
		if( isset($_SESSION['debug']) && $_SESSION['debug'] )
			echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';

		$this->nomTable		= $pTable;
		$this->structTables	= $pStruct;
		$this->struct 		= $this->structTables->getC($this->nomTable , false );
		$this->droitAll		= $pDroit;
		$this->fork			= $pFork;

		$this->filter='';
		if( is_array($pFilter) )
			foreach( $pFilter as $key => $v ){
				switch($key){
					case 'order_by':
					case 'password2':
					case 'AfficherPage':
						break;
					case 'password':
						if($this->filter!='')
							$this->filter.=' AND ';
						else
							$this->filter='';
						$this->filter .= '`'.$key.'`=PASSWORD(\''.$v.'\') ';
						break;
					default:
					if($this->filter!='')
							$this->filter.=' AND ';
						else
							$this->filter='';
						if( is_array( $v ) ){
							if( count($v) > 0 ){
								$this->filter .='`'.$key.'` '.'IN (\'0\'';
								foreach( $v as $kf => $v2 )
									$this->filter .=',\''.$v2.'\'';
								$this->filter .=') ';
							}
						}else{
							$this->filter .='`'.(substr($key,0,4)=='nom_'?'id':$key).'`'.($v?'=\''.$v.'\' ':' IS NULL ');
						}
						$this->filterTab[$key]	=$v;
						if( $key=='id' && !$v )return null;
				}
			}
		if( $this->filter != '' )
			$this->req = sprintf("SELECT * FROM `".PREFIX."%s` WHERE %s ", $this->nomTable, $this->filter );
		else
			$this->req = sprintf("SELECT * FROM `".PREFIX."%s` ", $this->nomTable);
		$this->req.=( /*isset($pFilter['table']) && $this->nomTable==$pFilter['table'] &&*/ isset($pFilter['order_by']) )?(' ORDER BY `'.$pFilter['order_by'].'`'):'';
		if($this->req!='')
			$this->req.=' LIMIT '.($pPage*$pNbLigneParPage).' , '.$pNbLigneParPage.';';

		if( $pMerge )
			$this->mergeLoadFromDB();
		else
			if($this->nomTable=='droit_acces')$this->reloadFromDB();
			else $this->loadFromDB();
//echo '<pre>',$this->req,'</pre>'."\n";
		$lCol = $this->struct->get();
		foreach( $lCol as $key => $v){
			preg_match('/^(double).*$/', $v['Type'], $matches);
			if(is_array($matches) && count($matches) ) $align=' class="number_to_sum"';
			else $align='';
		}
	}
//----------------------------------------------------------------------
	function mergeLoadFromDB( ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$db=t_DB::getInstance();
		$db_var=$db->RequestDB($this->req,'req_'.$this->nomTable );
		while( $res_var=$db->GetLigneDB($db_var) ){
			if( $this->fork )
				self::$dataFork[$this->nomTable][$res_var['id']]=$res_var;
			else	
				self::$data[$this->nomTable][$res_var['id']]=$res_var;
		}
	}
//----------------------------------------------------------------------
	function loadFromDB( ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if(  $this->fork? isset(self::$dataFork[$this->nomTable]) : isset(self::$data[$this->nomTable]) ){
			return null;
		}
		$this->mergeLoadFromDB( );
	}
//----------------------------------------------------------------------
	function reloadFromDB( ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( $this->fork )
			unset( self::$dataFork[$this->nomTable] );
		else	
			unset( self::$data[$this->nomTable] );

		$this->mergeLoadFromDB( );
	}
//----------------------------------------------------------------------
	function __toString(){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		return $this->FormPrint( null,'tab' );
	}
//----------------------------------------------------------------------
	function FormatInput($pVal,$pType,$pName){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		switch($pType){
			case 'timestamp':
			case 'datetime':
			case 'date':
				if(substr($pVal,4,1)=='-' && substr($pVal,7,1) =='-' )
					$ret="'".$pVal."'";
				else 
					$ret="'".substr($pVal,6,4).'-'.substr($pVal,3,2).'-'.substr($pVal,0,2).' '.substr($pVal,11) ."'";
				break;
			default:
				switch($pName){
					case 'file':
					case 'fichier':
					case 'photo':
					case 'upload':
						$db=t_DB::getInstance();
						$ret='';
						//if(!isset($_REQUEST[$pName]))break;
						$target_dir = "uploads/";
						$target_file = $target_dir . $this->nomTable.'.'.$pName.'.'.
										(isset($this->filterTab['id'])?$this->filterTab['id']: $db->LastId() );
										//$target_dir . basename($_FILES[$pName]["name"]);
						$uploadOk = 1;
						$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
						// Check if image file is a actual image or fake image
						echo $target_file.' file name :'.$pVal["tmp_name"].'<br>';
						$check = getimagesize($pVal["tmp_name"]);
						if($check != false) {
							echo "File is an image - " . $check["mime"] . ".";
							//print_r($check);
							$uploadOk = 1;
						} else {
							echo "File is not an image.";
							$uploadOk = 0;
							break;
						}
						// Check if file already exists
						if (file_exists($target_file)) {
							echo "Sorry, file already exists.";
							$uploadOk = 0;
							break;
						}
						// Check file size
/*						if( ! isset($check["size"]) ) {
							echo "Taille du fichier non définie";
							$uploadOk = 0;
							break;
						}
						if( $check["size"] > 200000) {
							echo "Sorry, your file is too large.";
							$uploadOk = 0;
							break;
						}
	*/
						// Allow certain file formats
						/*if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
						&& $imageFileType != "gif" ) {*/
						if($check["mime"] !='image/jpeg' ){
							echo "Sorry, only JPEG files are allowed.";
							$uploadOk = 0;
							break;
						}
						else{
							$target_file.='.jpg';
						}
						// Check if $uploadOk is set to 0 by an error
						if ($uploadOk == 0) {
							echo "Sorry, your file was not uploaded.";
						// if everything is ok, try to upload file
						} else {
							if (move_uploaded_file($pVal["tmp_name"], $target_file)) {
								chmod($target_file , 0755);
								//echo "The file ". basename( $pVal["name"]). " has been uploaded.";
							} else {
						//echo print_r($pVal);
								echo "Sorry, there was an error uploading your file.";
							}
						}
						$ret="'".$target_file."'";
						break;
					default:
						$db=t_DB::getInstance();
						$ret="'".$db->Escape($pVal)."'";
				}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function FormatOutput($id,$key,$pType,$pMode='tab'){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( $this->fork )
			if( !isset(self::$dataFork[$this->nomTable][$id]) )return '';
		else	
			if( !isset(self::$data[$this->nomTable][$id]) )return '';
		switch($pType){
			case 'timestamp':
			case 'datetime':
			case 'date':
				if( ( $this->fork && isset(self::$dataFork[$this->nomTable][$id][$key]) ) 
				 || (!$this->fork && isset(self::$data[    $this->nomTable][$id][$key]) ) )
					$ret=/*substr( $this->fork
										?self::$dataFork[$this->nomTable][$id][$key]
										:self::$data[	 $this->nomTable][$id][$key]
									,8,2).'-'.
						 substr( $this->fork
										?self::$dataFork[$this->nomTable][$id][$key]
										:self::$data[	 $this->nomTable][$id][$key]
									,5,2).'-'.
						 substr( $this->fork
										?self::$dataFork[$this->nomTable][$id][$key]
										:self::$data[	 $this->nomTable][$id][$key]
									,0,4).' '.
						 substr( $this->fork
										?self::$dataFork[$this->nomTable][$id][$key]
										:self::$data[	 $this->nomTable][$id][$key]
									,11)*/
							$this->fork
										?self::$dataFork[$this->nomTable][$id][$key]
										:self::$data[	 $this->nomTable][$id][$key]
						;
				else 
					$ret='';
				break;
			default:
				switch($key){
				case 'photo':
				case 'file':
				case 'fichier':
					if($this->fork ?(isset(self::$dataFork[$this->nomTable][$id][$key])?self::$dataFork[  $this->nomTable][$id][$key]:'')
												  :(isset(self::$data[	  $this->nomTable][$id][$key])?self::$data[		 $this->nomTable][$id][$key]:'')
									)
						$ret='<img src="'.($this->fork ?(isset(self::$dataFork[$this->nomTable][$id][$key])?self::$dataFork[  $this->nomTable][$id][$key]:'')
												  :(isset(self::$data[	  $this->nomTable][$id][$key])?self::$data[		 $this->nomTable][$id][$key]:'')
									)
									//envoie en parametre du fichier sa date (résoud prob de version)
									.'?'.(@filemtime ( ($this->fork ?(isset(self::$dataFork[$this->nomTable][$id][$key])?self::$dataFork[  $this->nomTable][$id][$key]:'')
												  :(isset(self::$data[	  $this->nomTable][$id][$key])?self::$data[		 $this->nomTable][$id][$key]:'') ) )
									)
									//change la taille d'affichage en fonction de la vue (tableau ou formulaire)
									.'" '.($pMode=='tab'?'class="vignette"':'class="grandeImage"').' />';
					else
						$ret='';
					break;
			default:
				$ret=$key=='password'?'********'
									:($this->fork ?(isset(self::$dataFork[$this->nomTable][$id][$key])?self::$dataFork[  $this->nomTable][$id][$key]:'')
												  :(isset(self::$data[	  $this->nomTable][$id][$key])?self::$data[		 $this->nomTable][$id][$key]:'')
									);
				}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function PrintField($lCol,$key,$id,$pEcriture,$pFilter,$pTypeAff='form'){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';
		$align='';

		if($pTypeAff=='form')
			$ret.='<p class="clearboth"><span class="label"><span>'.($lCol['Comment']
							?$lCol['Comment']
							:$lCol['Field']).($lCol['Type']=='timestamp'||$lCol['Type']=='datetime'?' (ex:2014-12-31 14:00)':'')
											.($lCol['Type']=='date'?' (ex:2014-12-31)':'')
							.'</span></span>'
				.'<span class="value"><span>';
		else{
			preg_match('/^(double).*$/', $lCol['Type'], $matches);
			if(is_array($matches) && count($matches) ) $align=' class="number_to_sum"';
			$ret.='<td'.($align).'>';
		}
		
		if( isset($lCol['REFERENCED_TABLE_NAME']) && is_array($lCol['REFERENCED_TABLE_NAME']) ){
			foreach( $lCol['REFERENCED_TABLE_NAME'] as $k => $v ){
				$obj=new tObj( $this->droitAll , $this->structTables , substr($v,strlen( PREFIX )) , null , null , true ); // pas de filtre, pas de merge, fork = true
//semi spécifique
				$drt=$this->getDroitOnCell($id,$key);
				if( (($key=='created_by' && $drt=='Voir') || isset($pFilter[$key])) && !$id )
					$ret.=$obj->getLink( $key=='created_by' && $drt=='Voir'?$_SESSION['idLogin']:$pFilter[$key] )
						.'<input type="hidden" name="'.$key.'" value="'.($key=='created_by'&& $drt=='Voir'?$_SESSION['idLogin']:$pFilter[$key]).'" />'
//fin semi spécifique
					;
				else{
					$ret.=($pEcriture && $drt!='' &&$drt!='Voir' && !isset($pFilter[$key]) )
						?($obj->getDDL( $this->fork? isset(self::$dataFork[$this->nomTable][$id][$key]):isset(self::$data[$this->nomTable][$id][$key])?$this->fork?self::$dataFork[$this->nomTable][$id][$key]:self::$data[$this->nomTable][$id][$key]:null , $key , substr($v,strlen( PREFIX )) , null , $lCol['Null']=='YES'?'Aucun':'' ) )
						:($obj->getLink($this->fork? isset(self::$dataFork[$this->nomTable][$id][$key]):isset(self::$data[$this->nomTable][$id][$key])?$this->fork?self::$dataFork[$this->nomTable][$id][$key]:self::$data[$this->nomTable][$id][$key]:null ));
				}
			}
		}
		else{
			$ret.=$this->PrintValue($lCol , $pEcriture , $id, $key , $pTypeAff );
//if($key=='email')echo 'PrintValue(lCol=',print_r($lCol),' ,pEcriture='. $pEcriture .',id='. $id.',key='. $key .',typeAff='. $pTypeAff.')<br/>';
			if($align!='')
				$this->sum[$key]=(isset($this->sum[$key])?$this->sum[$key]:0) 
								  + ($this->fork ? self::$dataFork[$this->nomTable][$id][$key]
												 : self::$data[    $this->nomTable][$id][$key]
									);
		}
		if($pTypeAff=='form')
		$ret.='</span></span></p>'."\n";
		else $ret.='</td>';
		return $ret;
	}
//---------------------------------------------------------------------
	private function getDroitPriority($pDest,$pNomCol){
		if( isset($_SESSION['debug']) && $_SESSION['debug'] )
			echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		return $this->currentDroit[$pDest];
	}
//----------------------------------------------------------------------
	private function setDroitPriority($pDroit=null,$pDest,$pNomCol='%'){
		if( isset($_SESSION['debug']) && $_SESSION['debug'] )
			echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		switch( $pDroit ){
			case 'Supprimer':	
				$this->currentDroit[$pDest][$pNomCol] = $pDroit;
				break;
			case 'Ajouter':		
				$this->currentDroit[$pDest][$pNomCol]  =(($this->currentDroit[$pDest][$pNomCol]=='Supprimer'
													 )?$this->currentDroit[$pDest]
													 :$pDroit);
				break;
			case 'Modifier':	
				$this->currentDroit[$pDest][$pNomCol]  =(($this->currentDroit[$pDest][$pNomCol]=='Supprimer'
													||$this->currentDroit[$pDest][$pNomCol]=='Ajouter'						
													 )?$this->currentDroit[$pDest][$pNomCol]
													 :$pDroit);
				break;
			case 'Voir':
			if(! isset($this->currentDroit[$pDest][$pNomCol]) )$this->currentDroit[$pDest][$pNomCol]=$pDroit;
				$this->currentDroit[$pDest][$pNomCol]  =(($this->currentDroit[$pDest][$pNomCol]=='Supprimer'
													||$this->currentDroit[$pDest][$pNomCol]=='Ajouter'
													||$this->currentDroit[$pDest][$pNomCol]=='Modifier'
													 )?$this->currentDroit[$pDest][$pNomCol]
													 :$pDroit);
				break;
			case 'reset':
				$this->currentDroit[$pDest]=null;
				break;
			default: //echo 'default setDroitPriority:',print_r($pDroit);
		}
	}
//----------------------------------------------------------------------
	private function getDroitOnTable($pTable=null){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r( func_get_args() ),')<br/>';
		$table=isset($pTable)?$pTable:$this->nomTable;
		$this->droit=$this->droitAll->getDroitsAcces( $table );
//echo '<pre>',print_r($this->droit),'</pre><br>';
		$drt=array();
		foreach($this->droit as $k => $v){
			$drt=$v['type_acces'];
		}
//echo $table.'<pre>',print_r($drt),'</pre><br>';
		return $drt;
		
	}
//----------------------------------------------------------------------
	private function getDroitOnCol($pCol,$pTable=null){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r( func_get_args() ),')<br/>';
		$table=isset($pTable)?$pTable:$this->nomTable;
		$this->droit=$this->droitAll->getDroitsAcces( $table );
		$drt='';
		foreach($this->droit as $k => $v){
//echo '<pre>',print_r($v),'</pre><br>';
			//if($v[]
			if($pCol==$v['COLUMN_NAME'] || '%'==$v['COLUMN_NAME'])
			$drt=$v['type_acces'];
//echo '<pre>'.$drt.'</pre><br>';
		}
//echo $table.'<pre>',print_r($drt),'</pre><br>';
		return $drt;

	}
//----------------------------------------------------------------------
	private function getDroitOnRow($pId,$pTable=null,$pCreatedBy=null){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r( func_get_args() ),')<br/>';
		$table=isset($pTable)?$pTable:$this->nomTable;
		$this->droit=$this->droitAll->getDroitsAcces( $table );
		$drt='';
		foreach($this->droit as $k => $v){
			if( ((tDroitsAcces::getId() == $pId && $table=='utilisateur' && $v['dest']=='Moi') || (tDroitsAcces::getId() == $pCreatedBy && $v['dest']=='Moi')) ){
				$drt=$v['type_acces'];
				//echo '<pre>Moi:'.$drt.'</pre><br>';
			}
			if($v['dest']=='Organisme'){
				if( $this->sameOrg($pCreatedBy) ){
					$drt=$v['type_acces'];
					//echo '<pre>Org:'.$drt.'</pre><br>';
				}
			}
			if($v['dest']=='Tout'){
				$drt=$v['type_acces'];
				//echo '<pre>Tout:'.$drt.'</pre><br>';
			}
		}
		return $drt;
	}
//----------------------------------------------------------------------
	private function getDroitOnCell($pId,$pCol,$pTable=null,$pCreatedBy=null){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r( func_get_args() ),')<br/>';
		$table=isset($pTable)?$pTable:$this->nomTable;
		$this->droit=$this->droitAll->getDroitsAcces( $table );
		$drt=array();
//if(! $this->fork?isset(self::$dataFork[$table]):isset(self::$data[$table]) ){
		if(! isset(self::$data[$table]) ){
			$obj=new tObj( $this->droitAll , $this->structTables , $table , null , null , false ); // pas de filtre, pas de merge, fork = true
		}

		foreach($this->droit as $k => $v){
			if($pCol==$v['COLUMN_NAME'] || '%'==$v['COLUMN_NAME']){
				if( !$this->structTables->isView($table) && ((tDroitsAcces::getId() == $pId && $table=='utilisateur') || (tDroitsAcces::getId() == self::$data[$table][$pId]['created_by'] && $v['dest']=='Moi')) 
				  ||($v['dest']=='Organisme' && $this->sameOrg(self::$data[$table][$pId]['created_by']) )
				  ||$v['dest']=='Tout'
				){
					$drt=$v['type_acces'];
		if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo '<pre>'.$drt.'</pre><br>';
				}
			}
		}
if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo $table.'<pre>',print_r($drt),'</pre><br>';
		return $drt;
	}
//----------------------------------------------------------------------
	function Transfert( $tableFrom , $idMoi , $table , $id_colis ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$db=t_DB::getInstance();
		//echo $this->nomTable.' / '.$table.' / '.$tableFrom
		$req='INSERT INTO '.PREFIX.$table.' (id_colis,id_objet,quantite,commentaire,created_by,created_time) SELECT '.$id_colis.' as id_colis,id_objet,quantite,commentaire,created_by,created_time FROM '.PREFIX.$tableFrom.' WHERE created_by='.$idMoi.';';
		$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
		$req='DELETE FROM '.PREFIX.$tableFrom.' WHERE created_by='.$idMoi.';';
		$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
		$this->reloadFromDB();
	}
//----------------------------------------------------------------------
	function FormPrint( $pParent=null, $pView='tab' , $pFilter=null , $pPage=0 , $pNbLigneParPage=10 ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';

		$drt=$this->getDroitOnTable();
		if( $drt!='' ){
			$ret=$this->FormModif( null ,$pParent, $pView , $pFilter , $pPage , $pNbLigneParPage );
			if( ! $this->structTables->isView($this->nomTable) && $pView=='tab' )
				$ret.=$this->getAjoutBouton($pFilter);
		}
		else $ret='<div class="erreur">Vous n\'avez pas accès à ces informations</div>';
		return $ret;
	}
//----------------------------------------------------------------------
	function getNom($pId){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( ! ($this->fork?isset(self::$dataFork[$this->nomTable][$pId]):isset(self::$data[$this->nomTable][$pId]) ) ){
			$this->reloadFromDB();
		}
		if( $this->fork?isset(self::$dataFork[$this->nomTable][$pId]['nom']):isset(self::$data[$this->nomTable][$pId]['nom']) )
			return $this->fork?self::$dataFork[$this->nomTable][$pId]['nom']:self::$data[$this->nomTable][$pId]['nom'];
		else
			return $this->structTables->getComment($this->nomTable).' n°'.$pId;
	}
//----------------------------------------------------------------------
	function getPhoto($pId, $mini=true){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( ! ($this->fork?isset(self::$dataFork[$this->nomTable][$pId]):isset(self::$data[$this->nomTable][$pId]) ) ){
			$this->reloadFromDB();
		}
		if( $this->fork?isset(self::$dataFork[$this->nomTable][$pId]['photo']):isset(self::$data[$this->nomTable][$pId]['photo']) )
			return ($this->fork?self::$dataFork[$this->nomTable][$pId]['photo']:self::$data[$this->nomTable][$pId]['photo']);
		else
			return '';
	}
//----------------------------------------------------------------------
	function getParent($pId){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( ! ($this->fork? isset(self::$dataFork[$this->nomTable][$pId]):isset(self::$data[$this->nomTable][$pId]) ) ){
			$this->reloadFromDB();
		}
		if( array_key_exists('id_parent' , $this->fork?self::$dataFork[$this->nomTable][$pId]:self::$data[$this->nomTable][$pId]) )
			return $this->fork?self::$dataFork[$this->nomTable][$pId]['id_parent']:self::$data[$this->nomTable][$pId]['id_parent'];
		else
			return -2;
	}
//----------------------------------------------------------------------
	function getLink($pId){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$drt=$this->getDroitOnTable();
		
		if( $drt!='' )
			$ret=$pId?'<a href="?table='.$this->nomTable.'&id='.$pId.'">'.$this->getNom($pId).'</a>':'Aucun';
		else
			$ret=$pId?$this->getNom($pId):'Aucun';
		return $ret;
	}
//----------------------------------------------------------------------
	function getDDL($pId,$lColName,$pNomTable,$pTableFrom=null,$pToutesVal=null,$pAll=false , $pFilter=null){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
	echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$options = new tTable( $pNomTable );

//compte le nombre d'options
		$obj=new tObj( 'Voir' , $options , $pNomTable , null , null , true ); //null=reprendre toutes les valeurs, pas de merge , fork=true
		$nbObj=$obj->getNbOptions($pId,$pAll);
		
		$txtFilter='';
		if( is_array($pFilter) ){
			foreach( $pFilter as $k => $v ){
				$txtFilter.='&'.$k.'='.$v;
			}
		}
		$ret = ''; //'nb obj : '.$nbObj;

		/*if($nbObj<5){
			$ret.=$obj->getRadios($pId,$pAll);
		}
		else*/
		if($nbObj<10){
			$ret.='<select name="'.$lColName.'" id="'.($pTableFrom?'filter_':'').$lColName.'" '.( $pToutesVal && $pTableFrom ?(
					'onChange="window.location.href=\'?'.$txtFilter.'&table='.$pTableFrom.'&'.($lColName).'=\'+this[this.selectedIndex].value"'
					):'').'>';
				$ret.=$pToutesVal?'<option value="">'.$pToutesVal.'</option>':'';
				$ret.=$obj->getOptions($pId,$pAll);
				//$obj=new tObj( $this->droitAll , $options , $pNomTable , null , true ); //null=reprendre toutes les valeurs, true = merge
			$ret.='</select>';
		}else{ //autocomplétion
			$ret.=( $pToutesVal && $pTableFrom ?('<form action="?table='.$pTableFrom.'&'.$txtFilter.'" method="POST">'):'') 
				.'<input type="hidden" name="'.$lColName.'" id="'.($pTableFrom?'filter_':'').$lColName.'" value="'.$pId.'" />'
				.'<input type="text" id="'.($pTableFrom?'filter_':'').'edt'.$lColName.'" value="'.($pId?$obj->getNom($pId):'').'" class="'.($pTableFrom?'filter_':'').'autocompl" />%'
				.( $pToutesVal && $pTableFrom ?('<input type="submit" value="Filtrer"></form>'):'')
				;
		}
		return $ret;
	}
//----------------------------------------------------------------------
	/*function PrintAutoCompl($enum , $lCol , $id , $key){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='PrintAutoCompl('.$enum.' , '.$lCol.' , '.$id .', '.$key.')';
		$ret.='<input type="text" id="edtTest'.'" name="edt'.$pNomTable.'" /><div id="lib_suggestions" class="autocompl"></div>';
		return $ret;
	}
	*/
//----------------------------------------------------------------------
	function getRadios($pId,$pAll=false){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='<ul>';
		if( array_key_exists($this->nomTable,$this->fork?self::$dataFork : self::$data ) 
			&& is_array($this->fork?self::$dataFork[$this->nomTable] : self::$data[$this->nomTable]) 
			){
			// spécifique
			if($this->nomTable=='objet'){
				$tab = new tTable( 'v_stock_professionnel' );
				$obj=new tObj( $this->droitAll , $tab, 'v_stock_professionnel' );
			}
			// fin spécifique
			foreach( $this->fork ? self::$dataFork[$this->nomTable] : self::$data[$this->nomTable] as $id => $v){
				// spécifique
				if($this->nomTable=='objet'){
					$val=' (reste : '.$obj->getQt($id).')';
				}
				else $val='';
				// fin spécifique
				$id_parent=$this->getParent($id);
				if( $pAll || ($pId==$id || $pId==$id_parent || $id_parent==-2) )
					$ret.='<li><input type="radio" value="'.$id.'" '.($pId==$id?'checked="checked"':'').
					'>'.$this->getNom($id).$val.'</li>';
			}
		}
		return $ret.'</ul>';
	}
//----------------------------------------------------------------------
	function getOptions($pId,$pAll=false){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';
		if( array_key_exists($this->nomTable,$this->fork?self::$dataFork : self::$data ) 
			&& is_array($this->fork?self::$dataFork[$this->nomTable] : self::$data[$this->nomTable]) 
			){
			// spécifique
			if($this->nomTable=='objet'){
				$tab = new tTable( 'v_stock_professionnel' );
				$obj=new tObj( $this->droitAll , $tab, 'v_stock_professionnel' );
			}
			// fin spécifique
			foreach( $this->fork ? self::$dataFork[$this->nomTable] : self::$data[$this->nomTable] as $id => $v){
				// spécifique
				if($this->nomTable=='objet'){
					$val=' (reste : '.$obj->getQt($id).')';
				}
				else $val='';
				// fin spécifique
				$id_parent=$this->getParent($id);
				if( $pAll || ($pId==$id || $pId==$id_parent || $id_parent==-2) )
					$ret.='<option value="'.$id.'" '.($pId==$id?'selected="selected"':'').
					'>'.$this->getNom($id).$val.'</option>';
			}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function getNbOptions($pId,$pAll=false){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret=0;
		if( array_key_exists($this->nomTable,$this->fork?self::$dataFork : self::$data ) 
			&& is_array($this->fork?self::$dataFork[$this->nomTable] : self::$data[$this->nomTable]) 
			){
			// spécifique
			if($this->nomTable=='objet'){
				$tab = new tTable( 'v_stock_professionnel' );
				$obj=new tObj( $this->droitAll , $tab, 'v_stock_professionnel' );
			}
			// fin spécifique
			$ret= count( $this->fork ? self::$dataFork[$this->nomTable] : self::$data[$this->nomTable] );
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function FormSave( $pFilter=null ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
	echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
	$file= isset($_FILES)?$_FILES:null;
		$ret='';
		$alert='';
		if( isset($_REQUEST['id'] ) ){
			$id=$_REQUEST['id'];
			$req="UPDATE `".PREFIX.$this->nomTable.'` SET id='.$id;
			foreach( $_REQUEST as $key => $v ){
				switch( $key ){
					case 'id':
					case 'table':
					case 'action':
					case '':
					case 'password2':
					case 'AfficherPage':
						break;
					default:
						$drt=$this->getDroitOnCell($id,$key);
						if($drt=='Modifier' || $drt=='Ajouter' || $drt=='Supprimer'){
							//$struct = $this->structTables->getC($this->nomTable , false );
							if( $lCol = $this->struct->get($key) ){
								if($key=='password'){
									if($v !='' ){
										if($v == $_REQUEST[$key.'2'] )
											$req.=',`'.$key."`=PASSWORD('".$v."')";
										else 
											$alert.='<div class="erreur">Attention, la confirmation du mot de passe est incorrecte</div>';
									}
								}
								else{
									if($lCol['Null']=='NO' && $v=='' )
										$alert.='<div class="erreur">Attention, champ <strong>'.$key.'</strong> est obligatoire</div>';
									else{
							//echo  'update v:'.$v .', type='.$lCol['Type'].' , key='.$key .'<br>';
										$req.=',`'.$key."`=".($v == ''?'null':$this->FormatInput( $v , $lCol['Type'] , $key ) );
									}
								}
							}
						}
				}
			}
			//echo '<pre>',print_r($file),'</pre>';
			foreach( $file as $key => $v ){
				$drt=$this->getDroitOnCell($id,$key);
				if($drt=='Modifier' || $drt=='Ajouter' || $drt=='Supprimer'){
					//$struct = $this->structTables->getC($this->nomTable , false );
					if( $lCol = $this->struct->get($key) ){
						if($lCol['Null']=='NO' && $v=='' )
							$alert.='<div class="erreur">Attention, champ <strong>'.$key.'</strong> est obligatoire</div>';
						else{
							//echo  'insert v:'.$v .', type='.$lCol['Type'].' , key='.$key .'<br>';
							if( $key=='file' || $key=='fichier' || $key=='photo' ){
								if($v['size']!=0)
									$req.=',`'.$key."`=".($v == ''?'null':$this->FormatInput( $v , $lCol['Type'] , $key ) );
							}
							else
								$req.=',`'.$key."`=".($v == ''?'null':$this->FormatInput( $v , $lCol['Type'] , $key ) );
						}
					}
				}
			}
			$req.=' WHERE id=\''.$id.'\';';
			if($alert){
				$ret= '<div class="erreur">Erreur : '.$alert.$req.'</div>'.$this->FormModif( 1 ,null,null,$pFilter);
			}
			else{
				$ret='';//.'<div class="erreur">REQUETE : '.$req.'</div>'.$this->FormModif( 1 ,null,null,$pFilter);
				foreach( $_REQUEST as $key => $v ){
					switch( $key ){
						case 'id':
						case 'table':
						case 'action':
						case '':
						case 'password2':
						case 'AfficherPage':
							break;
						default:
							$drt=$this->getDroitOnCell($id,$key);
							if($drt=='Modifier' && $drt=='Ajouter' || $drt=='Supprimer')
								$this->fork?self::$dataFork[$this->nomTable][$key]:self::$data[$this->nomTable][$key]=$v;
					}
				}
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo $req;
				$db=t_DB::getInstance();
				//$ret.=$req;
				$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
				$this->reloadFromDB();
				$ret.=$this->FormPrint(null,'tab', $pFilter);
			}
		}
		else{
			$drt=$this->getDroitOnTable();
			if($drt=='Ajouter' || $drt=='Supprimer'){
			$req="INSERT INTO `".PREFIX.$this->nomTable.'` ';
			$col='';
			$val='';
			foreach( $_REQUEST as $key => $v){
				switch( $key ){
					case 'id':
					case 'table':
					case 'action':
					case '':
					case 'password2':
					case 'AfficherPage':
					case 'ape_auth':
					case '60gpBAK':
					case '60gp':
					case '300gpBAK':
					case '300gp':
						break;
					default:
						if($col!=''){
							$col.=',';
							$val.=',';
						}
						//$struct = $this->structTables->getC($this->nomTable , false );
						if( $lCol = $this->struct->get($key) ){
							if($key=='password'){
								if($v !='' && $v == $_REQUEST[$key.'2'] ){
									$col.='`'.$key.'`';
									$val.="PASSWORD('".$v."')";
								}
								else
									$alert.='<div class="erreur">Attention, la confirmation du mot de passe est incorrecte</div>';
							}
							else{
								if($lCol['Null']=='NO' && $v=='' )
									$alert.='<div class="erreur">Attention, champ <strong>'.$key.'</strong> est obligatoire</div>';
								else{
									switch($key){
										case 'photo':
										case '':
											break;
										default:
											//echo print_r($lCol),'<br>';
											$col.='`'.$key.'`';
									//echo  'insert2 v:'.$v .', type='.$lCol['Type'].' , key='.$key .'<br>';
											$val.=$v == ''?'null':$this->FormatInput( $v , $lCol['Type'] ,$key ) ;
									}
								}
							}
						}
				}
			}
			$req.='('.$col.',`created_by`,`created_time`) VALUES ('.$val.','.tDroitsAcces::getId().',NOW() );';
			}else{
				$alert='Vous n\'avez pas le droit d\'insérer de données dans cette table';
			}
			if($alert){
				$ret= '<div class="erreur">Erreur : '.$alert.' pour <pre>'.$req.'</pre></div>';
				//.$this->FormModif( 'new' ,null,null,$pFilter);
			}
			else{
//lors d'un INSERT prendre le nom du fichier en upload, le modifier avec le dernier identifiant inséré, et mettre à jour.
				$db=t_DB::getInstance();
				$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
				if( !isset($_REQUEST['id']) )
					$id=$db->LastId();

				$req="UPDATE `".PREFIX.$this->nomTable.'` SET last_update_time=NOW(),last_update_by='.tDroitsAcces::getId();

				foreach( $file as $key => $v ){
					$drt=$this->getDroitOnCell($id,$key);
					if($drt=='Modifier' || $drt=='Ajouter' || $drt=='Supprimer'){
						if( $lCol = $this->struct->get($key) ){
							if($lCol['Null']=='NO' && $v=='' )
								$alert.='<div class="erreur">Attention, champ <strong>'.$key.'</strong> est obligatoire</div>';
							else
								if( $key=='file' || $key=='fichier' || $key=='photo' )
									if($v['size']!=0)
										$req.=',`'.$key."`=".($v == ''?'null':$this->FormatInput( $v , $lCol['Type'] , $key ) );
						}
					}
				}
				$req.=' WHERE id=\''.$id.'\';';
				$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
				$this->reloadFromDB();
			}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function Suppr( $pFilter,$pFilterAff ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		//$listDel='0';
		$ret='';
		$drt=$this->getDroitOnTable();
		if( $drt=='Supprimer' ){
		if( array_key_exists( $this->nomTable , $this->fork?self::$dataFork:self::$data ) && is_array($this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable]) ){
			/*foreach( $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $v ){
				$listDel.=','.$id;
			}*/
			if( isset($pFilter['created_by']) ){
				$listDel=$pFilter['created_by'];
				$req="DELETE FROM `".PREFIX.$this->nomTable.'` WHERE created_by IN ('.$listDel.');';
			}
			if( isset($pFilter['id']) ){
				$listDel=$pFilter['id'];
				$req="DELETE FROM `".PREFIX.$this->nomTable.'` WHERE id IN ('.$listDel.');';
			}
//echo $req;
			$db=t_DB::getInstance();
			$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
			if(isset($this->filterTab['id']) )unset($this->filterTab['id']);
			$this->reloadFromDB();
		}
		}
		else{
			$ret='<div class="erreur">Vous n\'avez pas les droits pour supprimer ces données</div>';
		}
		return $ret;
		//.$this->FormPrint( null,'tab' , $pFilterAff );
	}
//----------------------------------------------------------------------
	function getFilter(){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		return $this->filter;
	}
//----------------------------------------------------------------------
	function getTableParentBouton( $pFlag , $pId ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
//print_r($this->nomTable);
		static $nb;
		$ret='';
		$structParent = $this->structTables->getC($this->nomTable , $pFlag );
		$lColP = $structParent->get(null,true);
		if( is_array($lColP) ){
			foreach( $lColP as $key => $v ){
				if( array_key_exists( 'REFERENCED_TABLE_NAME' , $v ) && is_array($v['REFERENCED_TABLE_NAME']) ){
					foreach( $v['REFERENCED_TABLE_NAME'] as $k => $v2){
//$ret.= '<h2>'.$v2.'</h2>';
						$tabParent = substr($v['TABLE_NAME'][$k],strlen( PREFIX ));
						$colParent = $v['COLUMN_NAME'][$k];
						$relParent = isset($v['CommentP'])?$v['CommentP'][$k]:'n/a';
						if( ! isset($nb[$tabParent][$colParent]) ){
							if($tabParent=='panier')
								$req='SELECT id,`'.$colParent.'` as ref,COUNT(*) cnt FROM `'.PREFIX.$tabParent.'` WHERE created_by='.tDroitsAcces::getId().';';
							else
								$req='SELECT `'.$colParent.'` as ref,COUNT(*) cnt FROM `'.PREFIX.$tabParent.'` GROUP BY `'.$colParent.'`;';
//$ret.= $req;
							$db=t_DB::getInstance();
							$db_var=$db->RequestDB($req,'req_'.$tabParent );
							$nb[$tabParent][$colParent]=array();
							while( $res_var=$db->GetLigneDB($db_var) ){
								$nb[$tabParent][$colParent][$res_var['ref']]=$res_var['cnt'];
//$ret.= '<h3>'.$tabParent.'</h3>';
								if($tabParent=='panier')
									$id[$tabParent][$colParent][$res_var['ref']]=$res_var['id'];
							}
						}
						$nb2=0;
						if( isset($nb[$tabParent]) && isset($nb[$tabParent][$colParent]) )
//echo'<h2>'.$tabParent.'/'.$colParent.'</h2>';
							foreach( $nb[$tabParent][$colParent] as $kId => $vId){
//echo'<span>'.$kId .'/'. $pId.'</span>&nbsp;';
								if( $kId == $pId ){
									$nb2=$vId;
//$ret.='<strong style="color:red;">'.$vId .'</strong>-';
									if(isset( $id ) && isset($id[$tabParent]) ){
//$ret.=$tabParent.'/'.$colParent.'/'.$kId.'<br/>';
//$ret.= print_r($id),'<br/>';
										$id2=$id[$tabParent][$colParent][$kId];
//$ret.= print_r($id2),'<br/>';
									}
									break;
								}
							}
						$drt=$this->getDroitOnTable($tabParent);
						switch( $drt!='' ){
							case null:
								break;
							case '':
								$ret.='<div>'.$nb2.' '.$this->structTables->getComment( $tabParent ) .' pour ce '
											//.$v['genre'][$k].' '
											.$relParent.'</div>';
								break;
							default:
								switch($tabParent){
									case 'panier':
/*										if($this->nomTable=='objet'){
											if($nb2==0){
												$ret.='<div><a href="?table='.$tabParent.($pId?('&'.$colParent.'='.$pId):'').'&quantite=1&action=savenew" >'
														.'Ajouter cet '.$relParent
														.' au '.$this->structTables->getComment( $tabParent )
														.'</a></div>';
											}else{
//bug : identifiant de la ligne du panier
												$ret.='<div><a href="?table='.$tabParent.'&action=del&id='.$id2.'" >'
														.'Enlever cet '.$relParent
														.' du '.$this->structTables->getComment( $tabParent )
														.'</a></div>';
											}
											}
*/										break; 
									default:
										if($relParent!='')
										$ret.='<div><a href="?table='.$tabParent.($pId?('&'.$colParent.'='.$pId):'').'" >'.
													'Voir '.$this->structTables->getComment( $tabParent ) .' pour ce '
													//.$v['genre'][$k].' '
													.($relParent!=''?$relParent:$colParent).(isset($nb[$tabParent]) && isset($nb[$tabParent][$colParent])?'('.$nb2.')':'')
													.'</a></div>';
								}
						}
					}
				}else{
					echo 'not exist REFERENCED_TABLE_NAME';
				}
			}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function PrintValue($lCol , $pEcriture , $id, $key , $pTypeAff){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';

		preg_match('/^varchar\((.*)\)$/', $lCol['Type'], $matchesvarchar);
		if( count($matchesvarchar)>1 )$size=$matchesvarchar[1];
		else $size=0;
		//$ret.=$pEcriture;

		$drt=$this->getDroitOnCell($id,$key);

		preg_match('/^enum\((.*)\)$/', $lCol['Type'], $matches);
		if(is_array($matches) && count($matches) && $pEcriture && $drt!='Voir' ){
			unset($enum);
			foreach( explode(',', $matches[1]) as $value )$enum[] = trim( $value, "'" );

			//if(count($enum) >9 )
			//	 $ret=$this->PrintAutoCompl($enum , $lCol , $id , $key);
			//else
			if(count($enum) >3 )
				 $ret.=$this->PrintSelect($enum , $lCol , $id , $key);
			else $ret.=$this->PrintRadio($enum , $lCol , $id , $key);
		}
//simplifier ici???

		if(!$pEcriture && $key=='email')$ret.='<a href="mailto:';
		$ret.=( $pEcriture && $drt!='Voir' )
				?
				($ret?'':
					(($key=='photo'||$key=='file'||$key=='fichier')?'<input type="file" id="'.$key.'" name="'.$key.'"/>'.$this->FormatOutput( $id , $key , $lCol['Type'] , 'tab' )
						:($size>128
						?'<textarea name="'.$key.'" cols="50" rows="5" >'.(
							($this->fork?isset(self::$dataFork[$this->nomTable][$id][$key]):isset(self::$data[$this->nomTable][$id][$key]))
							?self::$data[$this->nomTable][$id][$key]:'').'</textarea>'
							:($key=='password'&&$id!=tDroitsAcces::getId()?('<a href="?action=newpassword&table=utilisateur&id='.$id.'">Générer nouveau mot de passe</a>')
								:('<input name="'.$key.'" value="'.($key=='password'?'':$this->FormatOutput( $id , $key , $lCol['Type'] , $pTypeAff ) ).'" '
									.($lCol['Type']=='date'||$lCol['Type']=='datetime'||$lCol['Type']=='time'?'class="jq'.$lCol['Type'].'" ':'')
									.($lCol['Type']=='timestamp'?'class="jqdatetime" ':'')
									//.($lCol['Type']=='date'||$lCol['Type']=='datetime'?('type="'.$lCol['Type'].'" class="jqdate" '):'')
									//.($lCol['Type']=='date'||$lCol['Type']=='datetime'?'type="'.$lCol['Type'].'" ':'')
									//TODO//.($lCol['Type']=='date'||$lCol['Type']=='datetime'?'format="dd/mm/yyyy" ':'')
									.($key=='password'?'type="password" ':'')
									.'/>'
									.($lCol['Null']=='NO'&&$pEcriture!='new'&&$key=='password'?'#': ($lCol['Null']=='NO'?'*':'') )
								)
							)
						)
					)
				)
			:$this->FormatOutput( $id , $key , $lCol['Type'] , $pTypeAff ).'&nbsp;' 
			;
		if(!$pEcriture && $key=='email')$ret.='?subject=[WalloBox]">Envoi</a>';

			if($pEcriture && $key=='password' && $drt!='Voir' && $id==tDroitsAcces::getId() && $this->nomTable=='utilisateur' ){
				$ret.=($pTypeAff!='tab'?'</span></p>'."\n"
										.'<p class="clearboth">'
					.'<span class="label">'
					.'<span>Confirmation</span></span>'
										:'<br />')
					.'<span class="value"><span><input name="'.$key.'2" type="password" />'.($lCol['Null']=='NO'&&$pEcriture!='new'?'#': ($lCol['Null']=='NO'?'*':'') )
					;
			}
		return $ret;
	}
//----------------------------------------------------------------------
	private function sameOrg($pId){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if($pId){
			foreach(self::$data['organisme'] as $k => $v);
			$db=t_DB::getInstance();
			$req='SELECT id_organisme FROM '.PREFIX.'utilisateur WHERE id=\''.$pId.'\'';
			$db_var=$db->RequestDB($req,'req_'.$this->nomTable );
			while( $res=$db->GetLigneDB($db_var) ){
				if($k== $res['id_organisme'])
					return true;
			}		
		}
		return false;
	}
//----------------------------------------------------------------------
	private function FPModif($pVue,$pNbLigneParPage,$pFilter,$pPage,$pEcriture,$filterTxt/*,$nb,$idP*/){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';
		$drt=null;
		if( ($pVue=='tab' || count($this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable])>1) && !isset( $pFilter['id'] ) )$vue='tab';
		else $vue='form';
		$action='save';

		if($vue=='tab'){
			$ret.='Nombre de lignes par page : <select name="NbLigneParPage" onChange="window.location.href=\''.$_SERVER['REQUEST_URI'].'&NbLigneParPage=\'+this[this.selectedIndex].value">'
				.'<option value="10"'. ($pNbLigneParPage==10?' selected':'').'>10</option>'
				.'<option value="25"'. ($pNbLigneParPage==25?' selected':'').'>25</option>'
				.'<option value="100"'.($pNbLigneParPage==100?' selected':'').'>100</option>'
				.'<option value="0"'.  ($pNbLigneParPage==0?' selected':'').'>Toutes</option>'
				.'</select><br />';
			$ret.='<table class="result">';
			$th=0;
		}
		$nbLigneAff=0;
		$lastPage=0;
		$retPage='';
		if( array_key_exists( $this->nomTable , $this->fork?self::$dataFork:self::$data ) )
		foreach( $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $v){
			$testFilter=true;
			if(is_array($pFilter) )
				foreach( $pFilter as $kf => $vf )
					if( /*(( $this->fork && isset(self::$dataFork[$this->nomTable][$id][$kf]) ) ||
						(!$this->fork && isset(self::$data[    $this->nomTable][$id][$kf]) ))*/
						isset( $v[$kf] )
						&&
						$v[$kf] == $vf || $kf=='order_by'){
					
					}
					else{$testFilter=false;}

			if( !$this->nbLigneParPage || ($nbLigneAff>=$pPage*$this->nbLigneParPage && $nbLigneAff<($pPage+1)*$this->nbLigneParPage) ){
				if($testFilter)
				if( is_array( $v ) && (!isset( $pFilter['id'] ) || (isset( $pFilter['id'] ) && $pFilter['id']==$id ) ) ){
					//if( is_array( $this->fork?self::$dataFork[$this->nomTable][$id]:self::$data[$this->nomTable][$id]) ){
						if( $vue=='tab' && !$th ){/* entêtes colonnes */
							$ret.='<tr>';
							//foreach( $this->fork?self::$dataFork[$this->nomTable][$id]:self::$data[$this->nomTable][$id] as $key => $vId ){
							foreach( $v as $key => $vId ){
								$drt=$this->getDroitOnCell($id,$key);
//echo $key,print_r($drt);
								if( $drt!='' ){
									switch($key){
									case 'id':
										break;
									default:
										$lCol = $this->struct->get($key);
//echo '<pre>',print_r($lCol),'</pre><br/>';
										//afficher élément parent (masquer colonne id)
										if( !( isset($pFilter[$lCol['Field']]) && isset($lCol['REFERENCED_COLUMN_NAME']) ) ){
											if( ! ( substr($lCol['Field'],0,3) == 'id_' && $this->structTables->isView($this->nomTable) ) ){
												switch($key){
													case 'id':	
														break;
													//masquer l'utilisateur référent
													case 'created_by':
													case 'id_utilisateur':
														if(isset($drt['Moi']))
														//if($fScope=='Moi')
															break;
													default	:	
														if($lCol['Comment']!='' || $this->structTables->isView($this->nomTable) )
														$ret.='<th><a href="'.$_SERVER['REQUEST_URI'].'&order_by='.$lCol['Field'].'">'.($lCol['Comment']!=''?$lCol['Comment']:$lCol['Field']).'</a>'.($pEcriture&&($lCol['Null']=='NO')?'*':'').'</th>';
												}
												$th=1;
											}
										}
										$th=1;
									}
								}
							}
							$ret.='<th>Action</th>'
								.'</tr>';
						}

					$drtRow=$this->getDroitOnRow($id,null,isset($v['created_by'])?$v['created_by']:null);

					if($drtRow!=''){
						if($pEcriture)$ret.='<form action="?table='.$this->nomTable.'&action='.$action.'&id='.$id.'" method="POST" enctype="multipart/form-data">';
	
						if($vue=='tab')$ret.='<tr>';
						//else $ret.='<fieldset>';

						$afficher=false;
						foreach( $v as $key => $v2){
							$drt=$this->getDroitOnCell($id,$key);							
							if( $drt!=''){
									$afficher=true;
							}
							//else $afficher=true;
						}

						foreach( $v as $key => $v2){
							$drt=$this->getDroitOnCell($id,$key);

							if( $afficher && $drt!='' ){
								switch($key){
									case 'id':
										break;
									case 'created_by':
									case 'created_time':
									case 'last_update_by':
									case 'last_update_time':
										if($pEcriture)
											break;
									default:
										$lCol = $this->struct->get($key);
		//afficher élément parent (masquer colonne) -> aussi si view && référence (id)
										if(	!(	isset($pFilter[$lCol['Field']]) 
												&&	isset($lCol['REFERENCED_COLUMN_NAME'])
											)
											&&!(substr($lCol['Field'],0,3) == 'id_'
												&& $this->structTables->isView($this->nomTable) 
												) 
											){
												switch($key){
													case 'id':	            $ret.=$pEcriture?'<input type="hidden" name="id" value="'.$id.'" />':''; break;
													//masquer l'utilisateur référent (TODO si droit = seulement MOI)
													case 'created_by':
													case 'id_utilisateur':	if(isset($drt['Moi'])){
																				$ret.=$pEcriture?'<input type="hidden" name="'.$key.'" value="'. tDroitsAcces::getId() .'" />':''; 
																				break;
																			}
													default	:	
														if($lCol['Comment']!=''  || $this->structTables->isView($this->nomTable) )
														$ret.=$this->PrintField($lCol,$key,$id,$pEcriture,$pFilter, $vue);
												}
												//$ret.= $lCol['REFERENCED_COLUMN_NAME'];
										}
								}
							}
						}
					//}

				if($afficher && isset($drt) ){
					if($vue=='tab')	$ret.='<td>';
					else			
					if(! $pEcriture)
									$ret.='<p class="clearboth"><span class="label"><span>Action</span></span>';
					$drt=$this->getDroitOnRow($id,null,isset($v['created_by'])?$v['created_by']:null);

					//spécifique panier						
					if('v_stock_professionnel'==$this->nomTable){
						$nb2=0;
						$req='SELECT id,id_objet as ref,quantite cnt FROM `'.PREFIX.'panier` WHERE created_by='.tDroitsAcces::getId().';';							
						$db=t_DB::getInstance();
						$db_var=$db->RequestDB($req,'req_cntPanier' );
						while( $res_var=$db->GetLigneDB($db_var) ){
							$nb['panier']['id_objet'][$res_var['ref']]=$res_var['cnt'];
							$idP['panier']['id_objet'][$res_var['ref']]=$res_var['id'];
						}
						if( isset($nb['panier']) && isset($nb['panier']['id_objet']) )
							foreach( $nb['panier']['id_objet'] as $kId => $vId)
								if( $kId == $id ){
									$nb2=$vId;
									$id2=$idP['panier']['id_objet'][$kId];
									//echo print_r($id),'<br/>';
								}
						if($nb2==0){
						$ret2='<div><form action="?table=panier&id_objet='.$id.'&action=savenew&tableBck='.$this->nomTable.'" method="POST">'
							.'<input type="text" name="quantite" id="quantite" value="1" />'
							.'<input type="submit" value="Ajouter au Panier" /></form></div>';
						}else{
							$ret2='<div><a href="?table=panier&action=del&id='.$id2.'&tableBck='.$this->nomTable.'" >'
							.'Enlever du Panier</a></div>';
						}
					}
//echo '<pre>',print_r($drt),'</pre>'.'/'.$id;
//foreach($fDroit as $kCol=>$vCol)foreach($fDroit as $kDest=>$vDest)$droit=$vDest;
//BUG TODO

					$ret.=$pEcriture?('<span class="value"><input type="reset" /><input type="submit" />'
										.'<span> * = Obligatoire</span>'
										.'<span> % = Liste autocomplétion</span>'.
										( ($this->fork? isset(self::$dataFork[$this->nomTable][$id]['password']):isset(self::$data[$this->nomTable][$id]['password']) )&&$pEcriture=='edit'?'<span> # = Laissez vide pour ne pas modifier</span>':'')."</span></p>\n")
									:( ! $this->structTables->isView($this->nomTable)
											?(
												($drt=='Supprimer'||$drt=='Modifier'||$drt=='Ajouter'
													?('<a href="?table='.$this->nomTable.'&id='.$id.'&action=edit" class="action">Modifier</a>&nbsp;')
													:'')
											.(isset($fDroit['Supprimer'])
													?('<a href="?table='.$this->nomTable.'&id='.$id.'&action=del'.$filterTxt.'" class="action" onClick="return confirm(\'Confirmation de suppression\')">Supprimer</a>'):'')
											.$this->getTableParentBouton( true , $id ) 
											)
/*spécifique, lien panier*/							:('v_stock_professionnel'==$this->nomTable
													?$ret2//enlever
													:'-'
											)
									);
					if($vue=='tab')	$ret.='</td>';
					else			$ret.='</span></p>';
//fin action possibles
					if($vue=='tab')	$ret.='</tr>';
					//else $ret.='</fieldset>';
					else 			$ret.='<hr />';

					if($pEcriture)$ret.='</form>';
					$ret.="\n";
				}
}else{$nbLigneAff--;}
}
				//$retPage.= $vue=='form' || $nbLigneAff%$this->nbLigneParPage?'': ( 'Page:'.(($nbLigneAff/$this->nbLigneParPage)+1).' ' );
				if($this->nbLigneParPage)$retPage.= $vue=='form' || $nbLigneAff%$this->nbLigneParPage?'': ( '<option value="'.($nbLigneAff/$this->nbLigneParPage).'" selected="selected">'.(($nbLigneAff/$this->nbLigneParPage)+1).'</option>' );
			}
			else{
				//$retPage.= $nbLigneAff%$this->nbLigneParPage?'': ( '<a href="'.$_SERVER['REQUEST_URI'].'&AfficherPage='.($nbLigneAff/$this->nbLigneParPage).'">Page:'.(($nbLigneAff/$this->nbLigneParPage)+1) .'</a> ' );
				if($this->nbLigneParPage)$retPage.= $vue=='form' || $nbLigneAff%$this->nbLigneParPage?'': ( '<option value="'.($nbLigneAff/$this->nbLigneParPage).'">'.(($nbLigneAff/$this->nbLigneParPage)+1).'</option>' );
			}
			if( $this->nbLigneParPage && $testFilter){
				$lastPage= ($vue=='form' || $nbLigneAff%$this->nbLigneParPage?$lastPage: ( ($nbLigneAff/$this->nbLigneParPage)+1 ));
				$nbLigneAff++;
			}
		}
/*		if( $vue=='tab' && isset($this->sum) && is_array($this->sum) && count($this->sum) ){
			$ret.='<tr>';
			$th=false;
//TODO : 
			foreach( $this->fork?self::$dataFork[$this->nomTable][$id]:self::$data[$this->nomTable][$id] as $key => $v2)
				if($key!='id'){
					$lCol = $this->struct->get($key);
					//echo '<pre>',print_r($lCol),'</pre>';
					//afficher élément parent (masquer colonne)
					if( isset($pFilter[$lCol['Field']]) && $pFilter[$lCol['Field']] && isset($lCol['REFERENCED_COLUMN_NAME']) ){
					}
					else
						if( substr($lCol['Field'],0,3) != 'id_' || !$this->structTables->isView($this->nomTable) )
						if( isset($this->sum[$lCol['Field']]) )
							$ret.='<td class="number_to_sum">'.$this->sum[$lCol['Field']].'</td>';
						else{
							$ret.='<td>'.($th?'&nbsp;':'Total').'</td>';
							$th=true;
						}
				};
			$ret.='</tr>';
		}*/
		if($vue=='tab') $ret.='</table>'.($this->nbLigneParPage?('Page <select name="AfficherPage" onChange="window.location.href=\''.$_SERVER['REQUEST_URI']
							.'&AfficherPage=\'+this[this.selectedIndex].value">'.$retPage.'</select>/'.$lastPage):'');
		return $ret;
	}
//----------------------------------------------------------------------
	private function FPNew($pFilter,$pEcriture,&$alert){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
	echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';
		$filter='';
		$drt=$this->getDroitOnTable();
		if($drt=='Ajouter' || $drt=='Supprimer'){
			if(is_array($this->filterTab))
				foreach( $this->filterTab as $flt => $v )$filter.='&'.$flt.'='.$v;
			$action='savenew';
			$colTab=$this->struct->get();
		//if( isset($colTab) )
			foreach( $colTab as $key => $v){
				$drt=$this->getDroitOnCol($key);
				if($drt!=''){
					switch($key){
						case 'id':	
						case 'created_by':
						case 'created_time':
						case 'last_update_by':
						case 'last_update_time':
							break;
						default:$ret.=$this->PrintField($v,$key,null,$pEcriture,$pFilter, 'form' );
					}
				}
			}
			$ret.='<p class="clearboth">'
					.'<span class="label"><span>Action</span></span>'
					.'<span class="value"><input type="reset" /><input type="submit" />'
						.'<span> * = obligatoire</span>'
						.'<span> % = Liste autocomplétion</span>'
					.'</span>'
					.'</p>'."\n";

			$lnkFilter='';
			if( isset($pFilter) )foreach( $pFilter as $kf => $v )$lnkFilter.='&'.$kf.'='.$v;

			if($pEcriture)$ret='<form action="?table='.$this->nomTable.'&action='.$action.$lnkFilter.'" method="POST" enctype="multipart/form-data">'
							.$ret
							.'</form>';
		}else{
			$alert='Vous n\'avez pas le droit d\'insérer de données dans cette table';
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function FormModif($pEcriture=null,$pParent=null , $pVue='tab' , $pFilter=null , $pPage=0 , $pNbLigneParPage=10){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
		echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
	$this->nbLigneParPage = $pNbLigneParPage;
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
	echo'&gt;&gt;'. __METHOD__ .'( table='.$this->nomTable.', pParent='.$pParent.', pEcriture='.$pEcriture.', pVue='.$pVue.', pFilter=',print_r($pFilter),', pPage='.$pPage.' )<br/>';
		$ret='';
		$retP='';
		$filterTxt='';
		$alert='';
$lastPage=0;		
		//filtre dans GET
		if( isset($this->filterTab) && is_array($this->filterTab) )
			foreach( $this->filterTab as $kf => $v )$filterTxt.='&'.$kf.'='.$v;

		//$struct = $this->structTables->getC($this->nomTable , false );
		
		//Si table pas chargée, alors on charge
		if( ! array_key_exists( $this->nomTable, $this->fork?self::$dataFork :self::$data) ){
			//$lCol = $this->struct->get();
			$lCol = $this->struct->get(null,null,$pPage,$pNbLigneParPage );
			if( is_array($pFilter) )
			foreach( $pFilter as $k => $v ){
				if( isset( $lCol[$k]['REFERENCED_TABLE_NAME'] ) ){
					foreach( $lCol[$k]['REFERENCED_TABLE_NAME'] as $kr => $vr ){
						$tableNameP=substr($vr,strlen( PREFIX ));
						$filterP[$lCol[$k]['REFERENCED_COLUMN_NAME'][$kr]] = $v;
						$newObj = new tObj( $this->droitAll , $this->structTables , $tableNameP , $filterP , true );
						$retP=$newObj->FormPrint( null,'form' , $filterP , $pPage , $pNbLigneParPage );
					}
				}
			}
		}
		//sinon on cherche si une colonne est parente d'une autre table
		else{
			foreach(  $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $v){
//echo '<p>test:'.$this->nomTable.'/'.$id.'pour '.$filterTxt.'</p>';
				if( is_array( $v ) ){
					foreach( $v as $key => $vCol ){
						switch($key){
						case 'id':
							break;
						default:
							$lCol = $this->struct->get($key);
							//afficher élément parent
							if( isset($pFilter[$lCol['Field']]) && $pFilter[$lCol['Field']] && isset($lCol['REFERENCED_COLUMN_NAME']) ){
								foreach( $lCol['REFERENCED_COLUMN_NAME'] as $idk => $vId){
									$filterP[$vId]=$pFilter[$lCol['Field']];
									$objP=new tObj($this->droitAll , $this->structTables , substr($lCol['REFERENCED_TABLE_NAME'][$idk],strlen( PREFIX )) , $filterP , true );
									$retPtab[$filterP['id']]=$objP->FormPrint($lCol['Comment'],'form', $filterP );//pas de page ici, car enreg parent
								}
							}
						}
					}
				}
			}
			if( isset($retPtab) )
				foreach( $retPtab as $k => $v)
					$retP .= $v;
		}

		if( ! ($this->fork? isset(self::$dataFork[$this->nomTable]):isset(self::$data[$this->nomTable]) ) && $pEcriture!='new'){
			$alert.='<div class="erreur">Liste vide (rien à afficher)</div>';
		}
		else{
			if( $pEcriture!='new' ){//pas nouveau enreg
				//echo 'FormPrintModif('.$pVue.','.$pNbLigneParPage.','.$pFilter.','.$pPage.','.$pEcriture.')';
				$ret.= $this->FPModif($pVue,$pNbLigneParPage,$pFilter,$pPage,$pEcriture,$filterTxt/*,$nb,$idP*/);
			}
			else{//nouveau enreg
				$ret.= $this->FPNew($pFilter,$pEcriture,$alert);
			}
		}

		$lCol = $this->struct->get();
//TODO : sélectionner ici la bonne joiture (du parent) --> ajouter parametre parent?
		if( isset($pParent) ) $test=' en tant que '.$pParent;
		else $test='';
		return $retP.'<fieldset><legend>'.($pEcriture?($pEcriture=='new'?'Ajouter ':'Modifier '):'')
		            .$this->structTables->getComment($this->nomTable).($test).'</legend>'.$alert.$ret.'</fieldset>';
	}
//----------------------------------------------------------------------
	function getNbResult(){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		return count($this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable]);
	}
//----------------------------------------------------------------------
	function getId(){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		foreach( $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $vId ){
			return $id;
		}
		return 0;
	}
//----------------------------------------------------------------------
	function getOrg(){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		foreach( $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $org => $vOrg ){
			return $vOrg['id_organisme'];
		}
		return 0;
	}
//----------------------------------------------------------------------
	function getProfil(){
	//if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$profil=array();
		foreach( $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $v ){
			//prendre profil de l'utilisateur
			return $v['id_profil'];
			//attention, également reprendre profil de l'org
		}
		//echo '<pre>',print_r($profil),'</pre>';
		//return $profil;
		return null;
	}
//----------------------------------------------------------------------
	function isActif(){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		foreach( $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $v ){
			return $v['actif'] == 'Oui';
		}
		return null;
	}
//----------------------------------------------------------------------
//spécifique
	function getQt($pId=null){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		if( $this->fork?isset(self::$dataFork[$this->nomTable]):isset(self::$data[$this->nomTable]) && is_array($this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable]) ){
			//echo '<pre>',print_r($this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable]),'</pre>';
			if( $this->fork?isset(self::$dataFork[$this->nomTable][$pId]):isset(self::$data[$this->nomTable][$pId]) )
				return $this->fork?self::$dataFork[$this->nomTable][$pId]['qt_total']:self::$data[$this->nomTable][$pId]['qt_total'];
			/*foreach(  $this->fork?self::$dataFork[$this->nomTable]:self::$data[$this->nomTable] as $id => $v ){
				echo $pId.'=='.$id.'<br/>';
				if($pId==$id)
					return $v['qt_total'];
			}*/
		}
		return 0;
	}
//----------------------------------------------------------------------
	function getDDLFilter($pTableFrom , $pFilter=null){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )
	echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret='';
		$drt=$this->getDroitOnTable();
		if( $drt!='' ){
			//$struct = $this->structTables->getC($this->nomTable , false );
			if(! isset($this->struct) ) return null;
			$lCol = $this->struct->get();
			if($pTableFrom!='panier' && ! $this->structTables->isView($pTableFrom) )
			$ret.='<form action="?table='.$pTableFrom.'" method="POST"><ul><li>Filtrer sur '.(isset($lCol['nom'])?$lCol['nom']['Comment']:'').'</li></ul>'
				.$this->getDDL( 0 , 'nom_'.$pTableFrom, $pTableFrom , null , 'Tous', true , $pFilter ).'<input type="submit" value="Filtrer"/></form>';
			
			if( is_array($lCol) )
			foreach( $lCol as $key => $v ){
				$drt=$this->getDroitOnCol($key);
				if( is_array($drt) ){
					if( $key != 'id' && isset($v['REFERENCED_TABLE_NAME']) && is_array($v['REFERENCED_TABLE_NAME']) ){
						$ret.='<ul>';
						foreach( $v['REFERENCED_TABLE_NAME'] as $kr => $vr ){
							if($v['CommentP'][$kr]!=''){
								$ret.='<li>';
								$ret.=$v['CommentP'][$kr];
								if( isset($pFilter[$key]) )$id = $pFilter[$key]; else $id=null;
								$ret.=$this->getDDL( $id , $key , substr($vr,strlen( PREFIX )) , $pTableFrom , 'Tous', true , $pFilter );
								$ret.='</li>';
							}
						}
						$ret.='</ul>';

					}
				}
			}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function getAjoutBouton( $pFilter=null ){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$libFilter='';
		$prefix='';
		$ret='';
		if( $pFilter ){		
			//$struct		=$this->structTables->getC($this->nomTable , false );
			$lCol = $this->struct->get();
			foreach( $lCol as $key => $v ){
				if( isset($v['REFERENCED_TABLE_NAME']) && is_array($v['REFERENCED_TABLE_NAME']) && $v['Key']!='PRI' )
					foreach( $v['REFERENCED_TABLE_NAME'] as $k => $vr ){
						if( isset($pFilter[$key]) )$id = $pFilter[$key]; else $id=null;
						if($id){
							$prefix='la liste des ';
							$libFilter.=' ayant ';
							$filter['id']=$id;
							$obj=new tObj( $this->droitAll , $this->structTables , substr($vr,strlen( PREFIX )) , $filter );
							$libFilter.='"'.$obj->getNom($id).'"';
							$libFilter.=' comme ';
							$libFilter.=$v['Comment'];
						}
					}
			}
		}else{
			$prefix='la liste des ';
		}
		
		$drt=$this->getDroitOnTable();

		switch( $drt ){
			case 'Supprimer':
			case 'Ajouter':
				$ret.='<a class="boutton" href="?table='.$this->nomTable;
				if( $pFilter )	
					foreach( $pFilter as $keyF => $vf )
						$ret.='&'.$keyF.'='.$vf;
				$ret.='&action=new">Ajouter '.$this->structTables->getComment($this->nomTable);
				$ret.=$libFilter;
				$ret.='</a>';
			case 'Modifier':
				$ret.='<a class="boutton" href="?table='.$this->nomTable;
				if( $pFilter )	
					foreach( $pFilter as $keyF => $vf )
						$ret.='&'.$keyF.'='.$vf;
				$ret.='&action=edit&view=tab">Modifier '.$prefix.$this->structTables->getComment($this->nomTable).$libFilter.'</a>';
				if($this->nomTable=='utilisateur')
					$ret.='<a class="boutton" href="mailto:'.$this->getAllMail().'?subject=[KidStock]">Envoyer un mail à tout le monde</a>';
				
				//TODO condition colis chez le préparateur, et nb ligne du colis==0
				if($this->nomTable=='04objet_colis' && isset($_REQUEST['id_colis']) /* && $statut=1 && $cnt==0 */ )
					$ret.='<a class="boutton" href="?action=transfert&tableFrom=panier&table=04objet_colis&id_colis='.$_REQUEST['id_colis'].'">Transférer le contenu du panier dans ce colis'
						 .'</a>';
				if($this->nomTable=='panier' )
					$ret.='<a class="boutton" href="?table='.$this->nomTable.'&action=del">Vider le panier</a>';
				break;
			default:
				$ret='';
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function getAllMail(){
		$ret='';
		foreach( self::$data[$this->nomTable] as $k => $v )
			if( self::$data[$this->nomTable][$k]['actif'] == 'Oui' )$ret.= self::$data[$this->nomTable][$k]['email'].';';
		return $ret;
	}
//----------------------------------------------------------------------
	function getDroits($pTable=null){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$ret=array();
		$table=isset($pTable)?$pTable:$this->nomTable;
		//echo '///'.(isset($pTable)?$pTable:'').'+'.$table;
		if( $this->fork? isset(self::$dataFork[$table]):isset(self::$data[$table]) && is_array($this->fork?self::$dataFork[$table]:self::$data[$table]) )
		foreach( $this->fork?self::$dataFork[$table]:self::$data[$table] as $id => $v ){
//echo '<pre>',print_r($v),'</pre>';
		//echo '///'.$pTable.'+'.$table.'/'.$v['TABLE_NAME'];
			if( $pTable==null || $pTable == $v['TABLE_NAME']) {
//echo '<pre>'.$table,print_r($v),'</pre>';
//echo '<pre>',print_r(self::$data[$table][$id]),'</pre>';
				$ret[$id]['TABLE_NAME']	=$v['TABLE_NAME'];
				$ret[$id]['COLUMN_NAME']=$v['COLUMN_NAME'];
				$ret[$id]['type_acces']	=$v['type_acces'];
				$ret[$id]['dest']		=$v['dest'];
//echo $ret[$id]['TABLE_NAME'].'>'.$ret[$id]['COLUMN_NAME'].'>'.$ret[$id]['type_acces'].'<br />'."\n";
			}
		}
		return $ret;
	}
//----------------------------------------------------------------------
	function PrintSelect($enum , $lCol , $id , $key){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
			$ret='<select name="'.$lCol['Field'].'">';
			if($lCol['Null']!='NO')$ret.='<option value="">Non applicable</option>';
			foreach( $enum as $k => $v )
				$ret.='<option value="'.$v.'" '
					.($v==($id?$this->fork?self::$dataFork[$this->nomTable][$id][$key]:self::$data[$this->nomTable][$id][$key]:null)?'selected="selected" ':'').'>'
					.$v.'</option>';
			return $ret.'</select>';
	}
//----------------------------------------------------------------------
	function PrintRadio($enum , $lCol , $id , $key){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
			$ret='';
			if($lCol['Null']!='NO')$ret.='<input type="radio" name="'.$lCol['Field'].'" value="" checked="checked" >Non applicable'.'<br />';
			foreach( $enum as $k => $v )
				$ret.='<input type="radio" name="'.$lCol['Field'].'" value="'.$v.'" '
					.($v==($id?
										($this->fork?self::$dataFork[$this->nomTable][$id][$key]
													:self::$data[$this->nomTable][$id][$key]
										):null)?'checked="checked" ':'').'/>'
					.$v.'<br />';
			return $ret;
	}
//----------------------------------------------------------------------
}

class tDroitsAcces{
	/****************************************************************************
	*	Class tTable
	*****************************************************************************
	*	Description :
	*		Lien entre la structure des tables de la base de donnée et un objet
	*
	*****************************************************************************
	*	Fonctions :
	*		$droit 							: données de la table (indice 1 : nom de la table, indice 2 = id, indice 3 = champ)
	*		__construct( $pIdLogin=null , $pIdOrg=null , $pIdProfil=null )
	*										: constructeur ayant en paramètre l'identifiant du login, un organisme et un profil
	*		__toString()					: non utilisé : sert au debug
	*		getDroitsAcces( $pTable=null )		: retourne les droits pour une table si spécifiée, sinon tout
	*		reset()							: remet à zéro tous les droits (dans le cadre d'un rechargement)
	*
	*
	****************************************************************************/
	static private $droit;
	static private $idLogin;
	static private $idOrg;
	static private $idProfil;
//----------------------------------------------------------------------
	static function getId(){
		return self::$idLogin;
	}
//----------------------------------------------------------------------
	static function getOrg(){
		return self::$idOrg;
	}
//----------------------------------------------------------------------
	function __construct( $pIdLogin=null , $pIdOrg=null , $pIdProfil=null ){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
//$_SESSION['debug_sql']=true;
		if( ! self::$droit ){
			if( isset($pIdLogin) )
				self::$idLogin	= $pIdLogin;
			if( isset($pIdOrg) )
				self::$idOrg	= $pIdOrg;
			$struct = new tTable( 'droit_acces' );
			self::$droit = array();
			$droitAcces = array();
			if( $pIdLogin!=null || $pIdOrg!=null || $pIdProfil!=null )
				$droitAcces=new tDroitsAcces();
			
			if( $pIdLogin!=null ){
				$filter['id_utilisateur']=$pIdLogin;
				$droit = new tObj( $droitAcces , $struct , 'droit_acces' , $filter , true ,true);
				unset( $filter['id_utilisateur'] );
			}
			if( $pIdOrg!=null ){
				$filter['id_organisme']=$pIdOrg;
				$droit = new tObj( $droitAcces , $struct , 'droit_acces' , $filter , true  ,true);
				$filter2['id']=$pIdOrg;
				$objOrg=new tObj( $droitAcces , $struct , 'organisme' , $filter2 );
				unset( $filter['id_organisme'] );
				$filter['id_profil']['org']=$objOrg->getProfil();
			}
			if( $pIdProfil!=null || isset($filter['id_profil']) ){
				$filter['id_profil']['usr']=$pIdProfil['usr'];
				self::$idProfil=$filter['id_profil'];
				$droit = new tObj( $droitAcces , $struct , 'droit_acces' , $filter , true ,true);
				unset($filter['id_profil']);
			}

			$filter['id_utilisateur']=null;
			$filter['id_organisme']=null;
			$filter['id_profil']=null;
			$droit = new tObj( $droitAcces , $struct , 'droit_acces' , $filter , true ,true );
			if( isset($droit) )
			self::$droit= $droit->getDroits() ;
		}
	}
//----------------------------------------------------------------------
	function getDroitsAcces( $pTable=null ){
//	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
		$droit=array();
		if( is_array(self::$droit) )
			foreach( self::$droit as $key => $v ){
//echo "\n".'getDroits:'.$key.' '.$pTable.'/'.$v['TABLE_NAME'].'<br/>'."\n";
				if( isset($v['TABLE_NAME']) && $v['TABLE_NAME']==$pTable ){
					$droit[$key]=$v;
//					echo "\n".'getDroits:'.$key.' '.$pTable.'/',print_r($v),'<br/>'."\n";
//					echo "\n".'getDroits:'.$pTable.'/',print_r($v['type_acces']),'/'.$v['dest'].'<br/>'."\n";
//					echo "\n".'getDroits:'.$pTable.'/'.$v['type_acces'].'/'.$v['COLUMN_NAME'].'/'.$v['dest'].'<br/>'."\n";
				}
			}
		return $droit;
	}	
//----------------------------------------------------------------------
	function __toString(){
		//echo '<pre>',print_r(self::$droit),'</pre>';
		return __METHOD__ ;
	}
//----------------------------------------------------------------------
    function reset() {
		self::$droit=null;
    }
//----------------------------------------------------------------------
}
//======================================================================
function SendMailInscript($pId){
	$db=t_DB::getInstance();

	$db_req_perso=$db->RequestDB("SELECT id,nom,email FROM `".PREFIX."utilisateur` WHERE id=".$pId.";",'req_email');
	while($res=$db->GetLigneDB($db_req_perso) ){
		//print_r($res);
		$password=genererMDP(8);
		$db->RequestDB("UPDATE `".PREFIX."utilisateur` SET password=PASSWORD('".$password."') WHERE id=".$pId.";",'updt_passwd');

		$body="<html><body>
Bonjour ".$res['nom'].",<br />
<br />
Bienvenue dans le programme KidStock.<br />
<br />
Votre identifiant est votre adresse email ( ".$res['email']." ).<br/>
<br />
Votre mot de passe est le suivant pour le serveur de test: test<br />Vous pourrez en changer dans l'application.<br />
L'adresse d'accès au programme en version de test/formation est : <a href=\"http://www.ma-soiree.be/ape\">http://www.ma-soiree.be/ape</a><br />
<br />
Votre mot de passe est le suivant pour le serveur de production: ".$password."<br />Vous pourrez en changer dans l'application.<br />
L'adresse d'accès au programme en version de production (attention, données réelles) est : <a href=\"http://www.kidstock.be/\">http://www.kidstock.be</a><br />
<br />
Pour toute question d'organisation, vous pouvez contacter : <a href=\"mailto:escayere9@gmail.com\">André</a><br />
Pour toute question technique concernant le programme, les fonctionnalités, les bugs, vous pouvez contacter : <a href=\"mailto:kidstock@ma-soiree.be\">Nicolas</a><br />
<br />
Cordialement<br />
<br />
Nicolas<br />
<br />
</body></html>";

		$headers  = 'MIME-Version: 1.0' . "\r\n";
		$headers .= 'Content-type: text/html; charset=UTF-8' . "\r\n";
		$headers .= 'From: kidstock@ma-soiree.be' . "\r\n";
//		echo $body;

		mail($res['email'],"[KidStock] Bienvenue",$body,$headers);
	}

}
//----------------------------------------------------------
function genererMDP ($longueur = 8){
	if( isset($_SESSION['debug']) && $_SESSION['debug'] )echo'&gt;&gt;'. __METHOD__ .'(',print_r(func_get_args() ),')<br/>';
// initialiser la variable $mdp
$mdp = "";
 
// Définir tout les caractères possibles dans le mot de passe,
// Il est possible de rajouter des voyelles ou bien des caractères spéciaux
$possible = "2346789bcdfghjkmnpqrtvwxyzBCDFGHJKLMNPQRTVWXYZ";
 
// obtenir le nombre de caractères dans la chaîne précédente
// cette valeur sera utilisé plus tard
$longueurMax = strlen($possible);
 
if ($longueur > $longueurMax) {
$longueur = $longueurMax;
}
 
// initialiser le compteur
$i = 0;
 
// ajouter un caractère aléatoire à $mdp jusqu'à ce que $longueur soit atteint
while ($i < $longueur) {
// prendre un caractère aléatoire
$caractere = substr($possible, mt_rand(0, $longueurMax-1), 1);
 
// vérifier si le caractère est déjà utilisé dans $mdp
if (!strstr($mdp, $caractere)) {
// Si non, ajouter le caractère à $mdp et augmenter le compteur
$mdp .= $caractere;
$i++;
}
}
 
// retourner le résultat final
return $mdp;
}
//----------------------------------------------------------
function CallAPI($method, $url, $data = false , $credential=null){
    $curl = curl_init();
    switch ($method){
        case "POST":
            curl_setopt($curl, CURLOPT_POST, 1);

            if ($data)
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            break;
        case "PUT":
            curl_setopt($curl, CURLOPT_PUT, 1);
            break;
        default:
            if ($data)
                $url = sprintf("%s?%s", $url, http_build_query($data));
    }

    // Optional Authentication:
    curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
    curl_setopt($curl, CURLOPT_USERPWD, $credential);
    
	curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true	);
	curl_setopt($curl, CURLOPT_CAINFO ,__DIR__ .'\\nrn.crt');
	curl_setopt($curl, CURLOPT_CAPATH, __DIR__ );
	
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

    $result = curl_exec($curl);
	$err     = curl_errno( $curl );
    $errmsg  = curl_error( $curl );
    echo  $errmsg;
	curl_close($curl);

    return $result;
}
?>
