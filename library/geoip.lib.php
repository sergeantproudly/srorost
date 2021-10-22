<?php

	define('GEOAPI_URL', 'http://api.ipstack.com/%IP%?access_key=%API_KEY%');
	define('GEOAPI_API_KEY', '22f1e254a30de5da0370a918c72e2a6c');
	
	class GeoIP {
		
		protected $db;
		protected $settings;
		
		public function __construct() {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
		}

		protected function GetUrl($ip) {
			return strtr(GEOAPI_URL, [
				'%IP%' => $ip,
				'%API_KEY%' => GEOAPI_API_KEY,
			]);
		}
		
		public function GetCityCodeByIp($ip) {
			$url = $this->GetUrl($ip);
			if (@$json = json_decode(file_get_contents($url), true)) {
				return strtolower($json['city']);
				
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
			if ($city_code = $this->GetCityCodeByIp($ip)) {
				if ($city = $this->GetCityByCode($city_code)) {
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