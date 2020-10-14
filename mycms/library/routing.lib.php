<?php

	class Routing {

		public function __construct() {
		}

		public static function GetRouting($code) {
			return dbGetRecordFromDb('SELECT Module FROM routing WHERE Code = "' . $code . '"', __FILE__, __LINE__);
		}

		public static function SetRouting($code, $module) {
			if (!dbGetValueFromDb('SELECT COUNT(Id) FROM routing WHERE Code = "' . $code . '"', __FILE__, __LINE__)) {
				dbDoQuery('INSERT INTO routing SET Module = "' . $module . '", Code = "' . $code . '"', __FILE__, __LINE__);
			} else {
				dbDoQuery('UPDATE routing SET Module = "' . $module . '" WHERE Code = "' . $code . '"', __FILE__, __LINE__);
			}
			return true;
		}

		public static function DeleteRouting($code) {
			dbDoQuery('DELETE FROM routing WHERE Code = "' . $code . '"', __FILE__, __LINE__);
		}
	}

?>