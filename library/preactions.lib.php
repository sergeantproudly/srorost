<?php

	class PreActions{

		private $db;
		
		public function __construct(){
			$this->DoActions();
		}
		
		private function DoActions(){
			global $Params;
			global $Config;
			global $Settings;
			global $Routing;
			global $Site;
			global $_LEVEL;
			
			$Params['Db']['Link'] = new SafeMySQL(array(
		   		'host'		=> $Config['Db']['Host'],
		   		'user'		=> $Config['Db']['Login'],
		   		'pass'		=> $Config['Db']['Pswd'],
		   		'db'		=> $Config['Db']['DbName'],
		   		'charset'	=> 'utf8'
		 	));
		 	$this->db = $Params['Db']['Link'];

			krnLoadLib('settings');
		 	$Settings = new Settings();

		 	krnLoadLib('routing');
		 	$Routing = new Routing();

		 	$Site = new Site();

		 	krnLoadLib('define');
		 	krnLoadLib('geoip');
		 	$geo = new GeoIP();

		 	// проверяем является ли первый уровень городом
		 	if ($city = $geo->GetCityByCode($_GET['p_code'])) {
		 		$_SESSION['ClientUser']['City'] = $city;
		 		$_LEVEL[1] = $_GET['p_code2'];
		 		$_LEVEL[2] = $_GET['p_code3'];

		 		// если страница является локальной, убираем город из роутинга
		 		if ($Site->IsPageLocal($Site->GetCurrentPage())) {
		 			$route = '/';
		 			if ($_LEVEL[1]) $route .= $_LEVEL[1] . '/';
			 		if ($_LEVEL[2]) $route .= $_LEVEL[2] . '/';
		 			__Redirect($route);
		 		}

		 	// город в роутинге не указан
		 	} else {
		 		$_LEVEL[1] = $_GET['p_code'];
		 		$_LEVEL[2] = $_GET['p_code2'];

		 		// проверяем не является ли страница локальной
		 		if (!$Site->IsPageLocal($Site->GetCurrentPage())) {
		 			// если город был определен ранее и это не Москва, добавляем его в роутинг и перезагружаем страницу
			 		if (isset($_SESSION['ClientUser']['City']['Id']) && $_SESSION['ClientUser']['City']['Id'] != CITY_MOSCOW_ID && $_LEVEL[1] != 'ajax' && $_LEVEL[1] != 'blocks') {
			 			$route = '/' . $_SESSION['ClientUser']['City']['Code'] . '/';
			 			if ($_LEVEL[1]) $route .= $_LEVEL[1] . '/';
			 			if ($_LEVEL[2]) $route .= $_LEVEL[2] . '/';
			 			__Redirect($route);
			 		}
		 		}
		 	}
		 	
		 	if (!$_SESSION['ClientUser']['City']) {
			    $_SESSION['ClientUser']['City'] = $geo->DetermineClientCity();
		 	}

		} 
		
	}
	
	$PreActions = new PreActions();

?>