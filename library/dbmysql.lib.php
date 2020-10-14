<?php

	// ошибка при запросе к базе данных
	function dbError($file,$line,$message=''){
		if(mysql_errno()){
			$result = '';
			$result .= "Database error in <b>$file</b> at line <b>$line</b><br/>";
			$result .= "MySQL Server message: ".mysql_error().'<br/>';
			$result .= "System message: ".$message.'<br/>';
			die($result);
		}
	}
	
	function dbSelectDb($host,$login,$password,$dbname){
		$link=mysql_connect($host,$login,$password) or die('Не могу установить соединение с MySQL!');
   		mysql_select_db($dbname,$link) or die('Не могу выбрать базу данных!');
   		mysql_query('SET NAMES utf8',$link);
	}

	// запрос к базе данных
    function dbDoQuery($query,$file='',$line=''){
        global $ConfigDB;
        global $link_db;
        if(!($res = @mysql_query($query)))
        	dbError($file,$line,$query);
        return $res;
    }
    
    // запрос на определенное количество записей к базе данных
	function dbDoQueryLimit($query,$limitStart,$limitCount=false,$file='',$line=''){
		if(!$limitStart && !$limitCount)
			return dbDoQuery($query,$file,$line);
		return dbDoquery($query." LIMIT $limitStart".($limitCount?",$limitCount":''),$file,$line);
	}
    
    // количество строк в выборке
    function dbGetNumRows($queryResult){
		return @mysql_num_rows($queryResult);
	}
	
	// последний добавленный id
	function dbGetInsertedId(){
		return @mysql_insert_id();
	}
	
	// запись из бд
    function dbGetRecord($queryResult){
		if($queryResult==null) return null;
		return @mysql_fetch_array($queryResult,MYSQL_ASSOC);
	}
	
	// запись из бд (ассоц массив)
    function dbGetRow($queryResult){
		if($queryResult==null) return null;
		return @mysql_fetch_row($queryResult);
	}
	
	// первая запись выборки
    function dbGetRowFromDb($query,$file='',$line=''){
		$res=dbDoQueryLimit($query,0,1,$file,$line);
		if(dbGetNumRows($res)){
			$row=dbGetRow($res);
			dbFreeResult($res);
			return $row;
		}
		dbFreeResult($res);
		return false;
	}
	
	// первая запись выборки (ассоц массив)
	function dbGetRecordFromDb($query,$file='',$line=''){
		$res = dbDoQueryLimit($query,0,1,$file,$line);
		if(dbGetNumRows($res)){
			$rec = dbGetRecord($res);
			dbFreeResult($res);
			return $rec;
		}
		dbFreeResult($res);
		return false;
	}
	
	// первое поле первой записи выборки
	function dbGetValueFromDb($query,$file='',$line=''){
		if($rec = dbGetRowFromDb($query,$file,$line))
			return $rec[0];
		return false;
	}
	
	// освобождение памяти
	function dbFreeResult($result){
		mysql_free_result($result);
	}
	
	function dbEscapeString($string,$mode=false){
		if($mode=='both')return mysql_escape_string($string);
		else return str_replace('"','\"',$string);
	}

?>