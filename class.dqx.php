<?php

class DQX{

	public $sqlConn = null;
	public $sqlSelect = null;
	public $sqlFrom = null;
	public $sqlQuery = null;
	public $sqlWhere = null;
	public $sqlSort = null;
	public $sqlGroup = null;
	public $sqlHaving = null;
	public $sqlLimit = null;
	public $sqlJoin = null;
	public $sqlDeleteFrom = null;
	public $sqlUpdate = null;
	public $sqlInsertInto = null;
	public $sqlSetKeys = null;
	public $sqlSetValues = null;
	public $sqlSet = null;
	public $sqlSetMulti = null;

	public function __construct($connInfo=[]) {
    $this->dbname = isset( $connInfo['dbname'] ) ? $connInfo['dbname'] : 'master';
    $this->dbhost = isset( $connInfo['host'] ) ? $connInfo['host'] : 'localhost';
    $this->dbuser = isset( $connInfo['username'] ) ? $connInfo['username'] : 'root';
    $this->dbpass = isset( $connInfo['password'] ) ? $connInfo['password'] : 'root' ;
    $this->dbsql = isset( $connInfo['sql'] ) ? ( in_array( $connInfo['sql'] , ['pgsql','mssql','mysql','sqlite'] ) ? $connInfo['sql'] : 'mysql' ) : 'mysql' ;
    
    if( $this->dbsql == 'pgsql' ){
    	$this->sqlConn = new PDO( "pgsql:host={$this->dbhost};dbname={$this->dbname}" , $this->dbuser , $this->dbpass ) ;
    }elseif( $this->dbsql == 'mssql' ){
    	$this->sqlConn = new PDO("odbc: Driver={SQL Server};Server={$this->dbhost};Database={$this->dbname};Uid={$this->dbuser};Pwd={$this->dbpass};") ;
    }elseif( $this->dbsql == 'mysql' ){
    	$this->sqlConn = new PDO( "mysql:host={$this->dbhost};dbname={$this->dbname}" , $this->dbuser , $this->dbpass );
    }elseif( $this->dbsql == 'sqlite' ){
			$this->sqlConn = new PDO( "sqlite:".$this->dbhost );
    }
		
	}

	public function sqlSelect($qry = null){
		$this->sqlSelect = $qry ;
	}
	public function sqlFrom($qry){
		$this->sqlFrom = $qry;
	}
	public function sqlJoin($qry){
		$this->sqlJoin .= " {$qry}";
	}
	public function sqlWhere($qry){
		$this->sqlWhere .= " {$qry}";
	}
	public function sqlQuery($qry){
		$this->sqlQuery = $qry;
	}
	public function sqlSort($qry){
		$this->sqlSort .= $this->sqlSort == null ? " {$qry}" : ", {$qry}";
	}
	public function sqlGroup($qry){
		$this->sqlGroup .= $this->sqlGroup == null ? " {$qry}" : ", {$qry}";
	}
	public function sqlHaving($qry){
		$this->sqlHaving .= $this->sqlHaving == null ? " {$qry}" : ", {$qry}";
	}
	public function sqlDeleteFrom($qry){
		$this->sqlDeleteFrom = $qry;
	}
	public function sqlInsertInto($qry){
		$this->sqlInsertInto = $qry;
	}
	public function sqlUpdate($qry){
		$this->sqlUpdate = $qry;
	}
	public function sqlSet($qry){
		foreach($qry as $key=>$value){
			if( $this->sqlUpdate == null ){
				$this->sqlSetKeys = ( $this->sqlSetKeys == null ? '' : ',' ) . $key ;
				$this->sqlSetValues = ( $this->sqlSetValues == null ? '' : ',' ) . "'$value'" ;
			}else{
				$this->sqlSet .= ( $this->sqlSet == null ? '' : ',' ) . "{$key} = '{$value}'" ;
			}
		}
	}
	public function sqlSetMulti($qry){
		foreach($qry as $sets){
			$this->sqlSetKeys = null;
			$this->sqlSetValues = null;
				foreach($sets as $key=>$value){
					$this->sqlSetKeys .= ( $this->sqlSetKeys == null ? '' : ', ' ) . $key ;
					$this->sqlSetValues .= ( $this->sqlSetValues == null ? '' : ', ' ) . "'{$value}'" ;
				}
			$this->sqlSetMulti .= "INSERT INTO {$this->sqlInsertInto} ( {$this->sqlSetKeys} ) VALUES( {$this->sqlSetValues} );";
		}
		
	}
	public function sqlLimit($qry){
		$dqx_syntax = $this->dbsql == 'mssql' ? 'TOP' : 'LIMIT' ;
		$this->sqlLimit .= $qry == null ? null : ( is_numeric($qry) ? "{$dqx_syntax} {$qry}" : null );
	}
	public function execute(){
		$response = [] ;

		try{

			$this->sqlWhere = $this->sqlWhere == null ? null : "WHERE ( " . $this->sqlWhere . " )";
			$this->sqlSort = $this->sqlSort == null ? null : "ORDER BY " . $this->sqlSort ;
			$this->sqlGroup = $this->sqlGroup == null ? null : "GROUP BY " . $this->sqlGroup ;

			if( $this->sqlQuery != null ){
				$buildQuery = $this->sqlQuery ;
				$sqlDDL = false;
			}elseif( $this->sqlDeleteFrom != null ){
				$buildQuery = "DELETE FROM {$this->sqlDeleteFrom} {$this->sqlWhere}";
				$sqlDDL = true;
			}elseif( $this->sqlUpdate != null && $this->sqlWhere != null && $this->sqlSet != null ){
				$buildQuery = "UPDATE {$this->sqlUpdate} SET {$this->sqlSet} {$this->sqlWhere}";
				$sqlDDL = true;
			}elseif( $this->sqlInsertInto != null && $this->sqlSetMulti != null ){
				$buildQuery = "{$this->sqlSetMulti}";
				$sqlDDL = true;
			}elseif( $this->sqlInsertInto != null ){
				$buildQuery = "INSERT INTO {$this->sqlInsertInto} ({$this->sqlSetKeys}) VALUES ({$this->sqlSetValues})";
				$sqlDDL = true;
			}else{
				$this->sqlSelect = $this->sqlSelect == null ? '*' : $this->sqlSelect ;
				if( $this->dbsql == 'mssql' ){
					$buildQuery = "SELECT {$this->sqlLimit} {$this->sqlSelect} FROM {$this->sqlFrom} {$this->sqlJoin} {$this->sqlWhere} {$this->sqlGroup} {$this->sqlHaving} {$this->sqlSort}" ;
				}else{
					$buildQuery = "SELECT {$this->sqlSelect} FROM {$this->sqlFrom} {$this->sqlJoin} {$this->sqlWhere} {$this->sqlGroup} {$this->sqlHaving} {$this->sqlSort} {$this->sqlLimit}" ;
				}
				$sqlDDL = false;
			}
			
			$buildQuery = preg_replace('/\s+/', ' ', $buildQuery);
			$sqlite = $this->sqlConn ;
			$sqlite = $sqlite->prepare( $buildQuery );
			$sqlite->execute();

			if(	$sqlDDL == false ){
				$sqliteRow = $sqlite->fetchAll(PDO::FETCH_ASSOC);

				if( count( $sqliteRow ) > 0){
					$response['data'] = $sqliteRow ;
					$response['sql'] = $buildQuery ;
				}else{
					$response['data'] = [] ;
					$response['sql'] = $buildQuery ;
				}
			}else{
				$response['data'] = 'OK' ;
				$response['sql'] = $buildQuery ;
			}
		}catch(\Throwable $th){

			$response['error'] = $th->getMessage() ;
			$response['sql'] = $buildQuery ;

		}

		return $response ;
	}
}

function uuid($input = null){
	$input = ( $input == null OR !is_array($input) ) ? [15] : $input ;
	$src_str  = ((isset($input[0]) AND $input[0]!='') AND !is_numeric($input[0])) ? $input[0] : '1234567890abcdef';
	$cnt 	  = count($input);
	$chk_last = is_numeric($input[count($input)-1]) ? '' : $input[count($input)-1] ;
	$stt_item = ((isset($input[0]) AND $input[0]!='') AND !is_numeric($input[0])) ? 1 : 0;
	$min_item = is_numeric($input[count($input)-1]) ? 0 : 1 ;
	$input_length = strlen($src_str);
	$rand_string  = '';
	
	for($i = $stt_item ; $i<count($input)-$min_item ; $i++){
		for($x = 0; $x < $input[$i]; $x++){
			$rand_character = $src_str[mt_rand(0, $input_length - 1)];
			$rand_string .= $rand_character;
		}
		$rand_string .= ($i == (count($input)-( $min_item + 1 ))) ? '' : $chk_last ;
	}
	
	return $rand_string;
}
