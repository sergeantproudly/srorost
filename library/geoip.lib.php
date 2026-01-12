<?php

	require_once 'vendor/autoload.php';

	//define('GEOAPI_URL', 'https://suggestions.dadata.ru/suggestions/api/4_1/rs/iplocate/address');
	define('GEOAPI_API_KEY', '1fddae94bffaa502c7ffbc28e5ffa738ba0aa235');
	
	class GeoIP {
		
		protected $db;
		protected $settings;
		protected static $tableName = 'geo_city';
		
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

		public static function ProcessCityResult($city) {
			$city = mb_strtolower($city);
			return strtr($city, array(
				'г ' => '',
			));
		}
		
		public function GetCityCodeByIp($ip) {
			$dadata = new \Dadata\DadataClient(GEOAPI_API_KEY, null);

			if ($result = $dadata->iplocate($ip)) {
				if (isset($result['value'])) {
					return self::ProcessCityResult($result['value']);

				} else {
					// город не определен
					return false;
				}

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
			return $this->db->getRow('SELECT * FROM ?n WHERE Id = ?i', self::$tableName, $id);
		}
		
		public function GetCityByTitle($title) {
			return $this->db->getRow('SELECT * FROM ?n WHERE Title = ?s', self::$tableName, $title);
		}

		public function GetCityByName($name) {
			return $this->db->getRow('SELECT * FROM ?n WHERE Name = ?s', self::$tableName, $name);
		}

		public function GetCityByCode($code) {
			return $this->db->getRow('SELECT * FROM ?n WHERE `Code` = ?s', self::$tableName, $code);
		}
		
		public function DetermineClientCity() {
			krnLoadLib('define');

			$ip = $this->GetClientIp();
			if (!getenv('DEV') && ($city_name = $this->GetCityCodeByIp($ip))) {
				if ($city = $this->GetCityByName($city_name)) {
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