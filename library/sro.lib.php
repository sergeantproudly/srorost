<?php

	krnLoadLib('xlsx');
	krnLoadLib('define');
	
	class Sro {
		
		protected $db;
		protected $settings;
		
		public function __construct($file = false) {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;

			if ($file) {
				$this->ImportXlsx($file);
			}
		}

		public function ClearTable() {
			$this->db->query('TRUNCATE TABLE sro_companies');
		}

		public function ClearImages() {
			if (file_exists('/uploads/companies/')) {
		        foreach (glob('/uploads/companies/*') as $file) {
		            @unlink($file);
		        }
		    }
		}

		public function ClearAll() {
			$this->ClearTable();
			$this->ClearImages();
		}
		
		public function ImportXlsx($file) {
			$xlsx = new SimpleXLSX((defined('ROOT_DIR') ? ROOT_DIR : '') . $file);
			$sheetsKeys = array_keys($xlsx->sheetNames());
			$sheet = $xlsx->rows($sheetsKeys[0]);

			if (is_array($sheet)) {
				$this->ClearAll();

				foreach ($sheet as $i => $row) {
					// первая строка пропускается
					if ($i > 0) {
						krnLoadLib('images');

						$cityId = method_exists($this->db, 'getOne') ? $this->db->getOne('SELECT Id FROM geo_city WHERE LOWER(Title) = ?s', mb_strtolower($row[0])) : dbGetValueFromDb('SELECT Id FROM geo_city WHERE LOWER(Title) = "' . mb_strtolower($row[0]) . '"');
						if (!$cityId) {
							if ($row[0] == 'МО') $row[0] = 'Московская область';
							elseif ($row[0] == 'ЛО') $row[0] = 'Ленинградская область';
							$regionId = method_exists($this->db, 'getOne') ? $this->db->getOne('SELECT Id FROM geo_regions WHERE LOWER(Title) = ?s', mb_strtolower($row[0])) : dbGetValueFromDb('SELECT Id FROM geo_regions WHERE LOWER(Title) = "' . mb_strtolower($row[0]) . '"');
						} else {
							$regionId = 0;
						}
						$sroTypeId = method_exists($this->db, 'getOne') ? $this->db->getOne('SELECT Id FROM sro_types WHERE LOWER(Title) = ?s', mb_strtolower($row[1])) : dbGetValueFromDb('SELECT Id FROM sro_types WHERE LOWER(Title) = "' . mb_strtolower($row[1]) . '"');

						// отключена загрузка логотипа по ссылке
						/*
						if ($row[4]) {
							$imageContents = file_get_contents($row[4]);
							if ($imageContents) {
								$path = 'uploads/companies/' . strtok(basename($row[4]), '?');
								$abspath = ABS_PATH . (defined('ROOT_DIR') ? ROOT_DIR : '') . $path;
		  						@file_put_contents($abspath, $imageContents);	
		  						$image = $path;
							}
						}
						$image = $path ?: '';

						if ($image) {
							$value = imgFit($abspath, 140, 75, ABS_PATH . (defined('ROOT_DIR') ? ROOT_DIR : '') . 'uploads/companies/');
							$info = flGetInfo($value);
							$image_resized = 'uploads/companies/' . $info['basename'];
						}
						*/
						
						$image = '';
						$image_resized = '';
						

  						if (method_exists($this->db, 'getOne')) {
	  						$query = 'INSERT INTO sro_companies SET Title = ?s, `Number` = ?s, Image = ?s, Image140_75 = ?s, TypeId = ?i, CityId = ?i, RegionId = ?i, Site = ?s, SumJoin = ?i, SumPurpose = ?i, SumMember = ?i, Compfond = ?i';
							$this->db->query($query,
								$row[2],
								$row[3],
								$image,
								$image_resized,
								$sroTypeId ?: 0,
								$cityId ?: 0,
								$regionId ?: 0,
								$row[5],
								(int)$row[6],
								(int)$row[7],
								(int)$row[8],
								(int)$row[9]
							);

  						} else {
  							dbDoQuery('INSERT INTO sro_companies SET `Title` = "' . mysqli_real_escape_string($this->db, $row[2]) . '", '
  									.'`Number` = "' . $row[3] . '", '
  									.'`Image` = "' . $image . '", '
  									.'`Image140_75` = "' . $image_resized . '", '
  									.'`TypeId` = ' . ($sroTypeId ?: 0) . ', '
  									.'`CityId` = ' . ($cityId ?: 0) . ', '
  									.'`RegionId` = ' . ($regionId ?: 0) . ', '
  									.'`Site` = "' . $row[5] . '", '
  									.'`SumJoin` = ' . (int)$row[6] . ', '
  									.'`SumPurpose` = ' . (int)$row[7] . ', '
  									.'`SumMember` = ' . (int)$row[8] . ', '
  									.'`Compfond` = ' . (int)$row[9] . ' '
  								);
  						}
					}
				}
			}
		}

		public function GetMinSroSums($typeId, $compfond = false) {
			if ($typeId == SRO_BUILDERS_TYPE_ID) {
				// есть привязка к городу пользователя
				$query = 'SELECT (SumJoin + SumPurpose + SumMember' . ($compfond ? ' + Compfond' : '') . ') AS SumTotal FROM sro_companies WHERE TypeId = ?i AND CityId = ?i ORDER BY SumTotal';
				$res = $this->db->getOne($query, $typeId, $_SESSION['ClientUser']['City']['Id']);

			} else {
				// нет привязки к городу пользователя
				$query = 'SELECT (SumJoin + SumPurpose + SumMember' . ($compfond ? ' + Compfond' : '') . ') AS SumTotal FROM sro_companies WHERE TypeId = ?i ORDER BY SumTotal';
				$res = $this->db->getOne($query, $typeId);
			}
			
			return $res;
		}

		public function GetCompanies($params = array()) {
			$where = '';
			$filter = array();

			if (isset($params['type_id'])) {
				$filter[] = $this->db->parse('TypeId = ?i', $params['type_id']);
			}
			if (isset($params['city_id'])) {
				$filter[] = $this->db->parse('CityId = ?i', $params['city_id']);
			} elseif (isset($params['region_id'])) {
				$filter[] = $this->db->parse('RegionId = ?i', $params['region_id']);
			}
			if (count($filter)) $where = 'WHERE ' . implode(' AND ', $filter);
			return $this->db->getAll('SELECT * FROM sro_companies ?p ORDER BY IF(`Order`, -1000/`Order`, 0) ASC, (SumJoin + SumPurpose + SumMember) ASC', $where);
		}
		
	}

?>