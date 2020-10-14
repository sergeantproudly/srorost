<?php
    
    // абстрактный класс
    abstract class krn_abstract{
		
		protected $db;
		protected $settings;
		protected $routing;
		protected $log;
		protected $pageTitle = '';
		protected $content = '';
		protected $filter;
		protected $pageIndex = 1;
		protected $total;
		
		public function __construct(){
			global $Params;
			global $Settings;
			global $Log; 
			global $Routing;

			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
			$this->routing = $Routing;
			$this->log = $Log;
		}
		
		abstract function GetResult();
		
	}
    
    // код страницы
    function krnGetPageCode(){
    	global $_LEVEL;
		return $_LEVEL['3'] ?: $_LEVEL['2'] ?: $_LEVEL['1'] ?: 'main';
	}
	
	// модуль страницы
	function krnGetPageModule(){
		global $Params;
		global $Routing;
		$module = $Routing->GetRouting($Params['Site']['Page']['Code']);
		return $module ?: ($Params['Site']['Page']['Code'] ?: false);
	}
	
	// загрузка модуля
	function krnLoadModule(){
		global $Params;
		if(!$Params['Site']['Page']['Module'])return false;
		if(!file_exists(MODULE_DIR.$Params['Site']['Page']['Module'].'.php'))
			$Params['Site']['Page']['Module']='_static';
		require_once(MODULE_DIR.$Params['Site']['Page']['Module'].'.php');
		$module=new $Params['Site']['Page']['Module'];
		return $module;
	}
	
	// загрузка модуля по имени
	function krnLoadModuleByName($module_name){
		if(!$module_name || !file_exists(MODULE_DIR.$module_name.'.php'))return false;
		require_once(MODULE_DIR.$module_name.'.php');
		$module=new $module_name;
		return $module;
	}
	
	// загрузка страницы (основоного шаблона)
	function krnLoadPage($templateName=''){
		return LoadTemplate($templateName?$templateName:'base');
	}
	
	// загрузка статической страницы
	function krnLoadPageStatic(){
		$base=krnLoadPage();
		$base=strtr($base,array(
			'<%CONTENT%>'	=> LoadTemplate('base_static')
		));
		return $base;
	}
	
	// загрузка страницы через определенный шаблон
	function krnLoadPageByTemplate($templateName){
		$base=krnLoadPage();
		$base=strtr($base,array(
			'<%CONTENT%>'	=> LoadTemplate($templateName)
		));
		return $base;
	}
	
	// загрузка библиотеки
	function krnLoadLib($libname){
		require_once(file_exists(LIBRARY_DIR.$libname.'.lib.php')?LIBRARY_DIR.$libname.'.lib.php':LIBRARY_DIR.$libname.'.php');
	}
	
	// абстрактный базовый класс
	abstract class krn_class_base{
		
		function __construct($params=array()){
			$this->Set($params);
		}
		
		function Set($params=array()){
			if($params){
				foreach($params as $k=>$v){
					$k{0}=strtolower($k{0});
					if(isset($this->$k)){
						$this->$k=$v;
					}
				}
			}
		}
		
		function Reset(){
			$vars=get_class_vars(get_class($this));
			if($vars){
				foreach($vars as $k=>$v){
					if(is_string($this->$k))$this->$k='';
					elseif(is_array($this->$k))$this->$k=array();
					elseif(is_bool($this->$k))$this->$k=false;
					else $this->$k=0;
				}
			}
		}
		
		function __call($name,$arguments){
			echo 'Вызов несуществующего или недоступного метода '.$name.' внутри класса (файл: '.__FILE__.', строка: '.__LINE__.')';
		}
		
		function __get($name){
			echo 'Обращение к несуществующей или недоступной переменной '.$name.' внутри класса (файл: '.__FILE__.', строка: '.__LINE__.')';
		}
		
		function __set($name,$value){
			echo 'Присвоение значения '.$value.' несуществующей или недоступной переменной '.$name.' внутри класса (файл: '.__FILE__.', строка: '.__LINE__.')';
		}
		
	}

?>