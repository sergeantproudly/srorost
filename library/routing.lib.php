<?php

	class Routing {

		protected $db;
		protected $settings;

		public function __construct() {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
		}

		public function GetRouting($code) {
			return $this->db->getOne('SELECT Module FROM routing WHERE Code = ?s', $code);
		}

		public function SetRouting($code, $module) {
			if (!$this->db->getOne('SELECT COUNT(Id) FROM routing WHERE Code = ?s', $code)) {
				$this->db->query('INSERT INTO routing SET Module = ?s, Code = ?s', 
					$module,
					$code
				);
			} else {
				$this->db->query('UPDATE routing SET Module = ?s WHERE Code = ?s', 
					$module,
					$code
				);
			}
			return true;
		}

		public function DeleteRouting($code) {
			$this->db->query('DELETE FROM routing WHERE Code = ?s', $code);
		}
	}

?>