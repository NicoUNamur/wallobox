<?php
/*
// DSN pour se connecter à MySQL
$dsn = 'mysql:host=localhost;dbname=rpgphp';
// Création d'un objet pour manipuler des requêtes
$dbh = new PDO($dsn, 'rpg', 'test1234');
// Execution d'une requête SELECT

$result = $dbh->query('SELECT * from perso');
// Itération sur les résultats d'une requête
foreach ($result as $row) {
	print_r($row);
}
*/

function microtime_float(){
    list($usec,$sec)=explode(" ",microtime());
    return ((float)$usec+(float)$sec);
}
//----------------------------------------------------------------------

//======================================================================
class t_DB{
	protected static $_instance;
	private $link_db;
	private $last_request;
	private static $nb_req;
	private static $duree_req;
	//public 
	private $MySqlDatabase;
//----------------------------------------------------------------------
	public static function getInstance($MySqlServer='', $MySqlLogin='', $MySqlPass='',$MySqlDatabase=''){
		if(null === self::$_instance){
			self::$_instance = new t_DB($MySqlServer, $MySqlLogin, $MySqlPass,$MySqlDatabase);
		}
		//if( !self::$_instance->IsConnected() ) echo print_r(self::$_instance);
		return self::$_instance->IsConnected()?self::$_instance:null;
	}
//----------------------------------------------------------------------
	function IsConnected(){
		return $this->link_db?true:false;
	}
//----------------------------------------------------------------------
	private function __construct($MySqlServer, $MySqlLogin, $MySqlPass,$MySqlDatabase){ //open_DB
		$this->link_db = @mysqli_connect ($MySqlServer, $MySqlLogin, $MySqlPass);
		$this->MySqlDatabase=$MySqlDatabase;
		mysqli_query($this->link_db,"SET NAMES 'utf8'");
		if($this->link_db){
			mysqli_select_db($this->link_db,$MySqlDatabase);
			self::$nb_req=0;
			self::$duree_req=0;
		}else{
			echo mysqli_error($this->link_db);
		}
	}
//----------------------------------------------------------------------
	function __destruct(){ //close_db
		if($this->link_db)
			mysqli_close($this->link_db);
	}
//----------------------------------------------------------------------
	function __tostring(){
		if( isset($_REQUEST['300gp']) )return '';
		return $this->link_db	?('Connecte a la DB. Nbreq='.self::$nb_req.' duree:'.self::$duree_req)
								:'Pas connecte';
	}
//----------------------------------------------------------------------
	function RequestDB($request,$var='',$log=0){
		$iMicroTime=microtime_float();//temps d exécution
		if(!$this->link_db){
		$db=0;
		}else{
		try{
if(isset($_SESSION['debug_sql']) )echo "\n".'<pre>'.$request.'</pre>'."\n";
			$db=mysqli_query( $this->link_db , $request );
if(isset($_SESSION['debug_sql']) )echo "\n".'<pre>'.mysqli_affected_rows($this->link_db).' rows</pre>'."\n";

if(!$db) throw new Exception( mysqli_error($this->link_db) );
			self::$nb_req++;
			self::$duree_req+=microtime_float()-$iMicroTime;
		}
		catch(Exception $e){
			echo '<span class="erreur">Erreur DB : '.$e->getMessage().' pour '.$request.'</span>';
			//echo '<pre>',print_r($_REQUEST),'</pre>';
			//echo 'ErreurSQL ('.$var.') :'.$request;
			$param=sprinta($_REQUEST);
			$request=addslashes($request);
			$param=addslashes($param);
			$temps=microtime_float()-$iMicroTime;//temps d exécution
			/*$req_log='INSERT logdb (request,temps_exec,param,var) VALUES (\''.$request.' -> '.$e->getMessage().'\','.$temps.',\''.$param.'\',\''.$var.'\');';
			mysqli_query($req_log,$this->link_db)or die ('<br />'.$req_log.'<br /><br /><strong>'.mysqli_error().'</strong><br />');*/
			//throw new Exception('Requete SQL echouee...');
		}
		if($log){
			$param=sprinta($_REQUEST);
			$request=addslashes($request);
			//echo $request."</br></br>\n";
			$param=addslashes($param);
			$temps=microtime_float()-$iMicroTime;//temps d exécution
			$req_log='INSERT logdb (request,temps_exec,param,var) VALUES (\''.$request.'\','.$temps.',\''.$param.'\',\''.$var.'\');';
			mysqli_query( $this->link_db , $req_log )or die ('<br />'.$req_log.'<br /><br /><strong>'.mysqli_error().'</strong><br />');
		}
		$this->last_request=$db;

		}
		return $db;
	}
	//----------------------------------------------------------------------
	function GetLigneDB($db=0){ //retourne l enreg actuel
		return $db?(mysqli_fetch_assoc($db)): ($this->last_request?mysqli_fetch_assoc($this->last_request):0) ;
	}
	//----------------------------------------------------------------------
	function Nb_LignesDB($db=0){ //retourne le nombre d'enreg affectes
		return $db?mysqli_num_rows($db): ($this->last_request?mysqli_num_rows($this->last_request):-1);
	}
	//----------------------------------------------------------------------
	private function __clone(){
	}
	//----------------------------------------------------------------------
	function getDataBase(){
		return $this->MySqlDatabase;
	}
	//----------------------------------------------------------------------
	function Escape( $txt ){
		return mysqli_real_escape_string ( $this->link_db , $txt );
	}
	//----------------------------------------------------------------------
	function LastId( ){
		return mysqli_insert_id( $this->link_db );
	//	$req='SELECT LAST_INSERT_ID() last_inserted FROM '.$tabl;
	}
	//----------------------------------------------------------------------

//if(isset($_SESSION['debug_sql']) )echo "\n".'<pre>'.mysqli_affected_rows($this->link_db).' rows</pre>'."\n";
}
//==========================================================================
class t_DBSqlite{
	protected static $_instance;
	private $link_db;
	private $last_request;
	private static $nb_req;
	private static $duree_req;
//----------------------------------------------------------------------
	public static function getInstance($pFileName){
		if(null === self::$_instance){
			self::$_instance = new t_DBSqlite($pFileName);
		}
		return self::$_instance->IsConnected()?self::$_instance:null;
	}
//----------------------------------------------------------------------
	function IsConnected(){
		return $this->link_db?true:false;
	}
//----------------------------------------------------------------------
	private function __construct($pFileName){ //open_DB
		$this->link_db = new SQLite3($pFileName);
		if($this->link_db){
			self::$nb_req=0;
			self::$duree_req=0;
		}
	}
//----------------------------------------------------------------------
	function __destruct(){ //close_db
	}
//----------------------------------------------------------------------
	function __tostring(){
		return $this->link_db	?('Connecte a la DB. Nbreq='.self::$nb_req.' duree:'.self::$duree_req)
								:'Pas connecte';
	}
//----------------------------------------------------------------------
	function RequestDB($request,$var='',$log=0){
		$iMicroTime=microtime_float();//temps d exécution
		if(!$this->link_db){
		$db=null;
		}else{
if(isset($_SESSION['debug_sql']) )echo "\n".'<pre>'.$request.'</pre>'."\n";
			$db=$this->link_db->query($request);
			$this->last_request=$db;
		
		}

		return $db;
	}
	//----------------------------------------------------------------------
	function GetLigneDB($db=null){ //retourne l enreg actuel
		return $db?$db->fetchArray(SQLITE3_ASSOC): ($this->last_request?$this->last_request->fetchArray(SQLITE3_ASSOC):0) ;
	}
	//----------------------------------------------------------------------
/*	function Nb_LignesDB($db=0){ //retourne le nombre d'enreg affectes
		return $db?mysqli_num_rows($db): ($this->last_request?mysqli_num_rows($this->last_request):-1);
	}*/
	//----------------------------------------------------------------------
	//	$req='SELECT LAST_INSERT_ID() last_inserted FROM '.$tabl;
	//----------------------------------------------------------------------
	private function __clone(){
	}
	//----------------------------------------------------------------------
}

?>