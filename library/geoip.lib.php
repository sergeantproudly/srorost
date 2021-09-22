<?php

	define('GEOAPI_URL','https://ipgeobase.ru:7020/geo?ip=');
	
	class GeoIP {
		
		protected $db;
		protected $settings;
		
		public function __construct() {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
		}
		
		public function GetCityTitleByIp($ip) {
			$xml = new DOMDocument();
			if (@$xml->load(GEOAPI_URL . $ip)) {
				return $xml->documentElement->getElementsByTagName('city')->item(0)->nodeValue;
				
			} else {
				// город не определен
				return false;
			}
		}
		
		private function GetClientIp() {
		    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		        $ip = $_SERVER['HTTP_CLIENT_IP'];
		    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		        $ip = preg_replace('/,([0-9,\. ]+)$/', '', $_SERVER['HTTP_X_FORWARDED_FOR']);
		    } else {
		        $ip = $_SERVER['REMOTE_ADDR'];
		    }
		    return $ip;
		}

		public function GetCityById($id) {
			return $this->db->getRow('SELECT * FROM geo_city WHERE Id = ?i', $id);
		}
		
		public function GetCityByTitle($title) {
			return $this->db->getRow('SELECT * FROM geo_city WHERE Title = ?s', $title);
		}

		public function GetCityByCode($code) {
			return $this->db->getRow('SELECT * FROM geo_city WHERE `Code` = ?s', $code);
		}
		
		public function DetermineClientCity() {
			krnLoadLib('define');

			$ip = $this->GetClientIp();
			if ($city_title = $this->GetCityTitleByIp($ip)) {
				if ($city = $this->GetCityByTitle($city_title)) {
					// город найден в базе данных
					return $city;
					
				}else{
					// город не найден в базе данных
					// подставляем Москву
					return $this->GetCityById(CITY_MOSCOW_ID);
				}
			}else{
				// город не найден в базе данных
				// подставляем Москву
				return $this->GetCityById(CITY_MOSCOW_ID);
			}
		}
		
	}

?>