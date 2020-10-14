<?php

	class Menu {

		protected $params = [];

		protected $menuDb;
		protected $classSubMenu;		
		protected $classCurrPage;

		protected $items;

		private $db;
		private $settings;
		
		public function __construct($params = []) {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;

			$this->init($params);
		}

		protected function init($params = []) {
			$this->params = $params;
			$this->menuDb = $params['menuDb'];
			$this->classSubMenu = $params['classSubMenu'] ? $params['classSubMenu'] : 'has-child';
			$this->classCurrPage = $params['classCurrPage'] ? $params['classCurrPage'] : 'active';
		}

		public function GetMenu($params = []) {
			global $Site;

			if (empty($params)) $params = $this->params;

			$menuDb = $params['menuDb'] ? $params['menuDb'] : $this->menuDb;
			if (isset($params['subMenuDb'])) $subMenuDb = $params['subMenuDb'];
			if (isset($params['template'])) $template = $params['template'];
			if (isset($params['templateEl'])) $templateEl = $params['templateEl'];
			if (isset($params['templateElAct'])) $templateElAct = $params['templateElAct'];
			if (isset($params['parentItemId'])) $parentItemId = $params['parentItemId'];
			if (isset($params['templateSub'])) $templateSub = $params['templateSub'];
			if (isset($params['templateSubEl'])) $templateSubEl = $params['templateSubEl'];
			if (isset($params['templateSubElAct'])) $templateSubElAct = $params['templateSubElAct'];

			$content = '';
			
			$page = $Site->GetCurrentPage();

			if (!$parentItemId) {
				$this->items[$menuDb][0] = $items = $this->db->getAll('SELECT Id, Title, Link FROM ?n ORDER BY IF(`Order`,-1000/`Order`,0) ASC', $menuDb);
			} else {
				$this->items[$menuDb][$itemId] = $items = $this->db->getAll('SELECT Id, Title, Link FROM ?n WHERE ItemId = ?i ORDER BY IF(`Order`,-1000/`Order`,0) ASC', 
					$menuDb, 
					$parentItemId
				);
			}
			foreach ($items as $item) {
				if (preg_match('/([a-zA-Z0-9_\-]+)\/?$/', $item['Link'], $match)) {
					$linkPage = $match[1];
				}

				$curr = ($page == $item['Link'] || (preg_match('/'.$linkPage.'[a-zA-Z0-9_\-\-]*\/?$/', $page) && $item['Link'] != '/'));

				if (isset($subMenuDb) && !empty($subMenuDb)) $submn = $this->GetMenu([
					'menuDb' 		=> $subMenuDb, 
					'parentItemId'	=> $item['Id'],
					'template'		=> $templateSub,
					'templateEl'	=> $templateSubEl,
					'templateElAct'	=> $templateSubElAct,
				]);

				$class = [];
				if ($this->classSubMenu && $submn) $class[] = $this->classSubMenu;
				if ($this->classCurrPage && $curr) $class[] = $this->classCurrPage;

				$content .= strtr(LoadTemplate($curr ? $templateElAct : $templateEl), array(
					'<%LINK%>'		=> $item['Link'],
					'<%TITLE%>'		=> $item['Title'],
					'<%MN_SUB%>'	=> $submn,
					'<%CLASS%>'		=> implode(' ', $class)
				));
			}

			$result = $content ? strtr(LoadTemplate($template), array(
				'<%CONTENT%>'	=> $content
			)) : '';

			return $result;
		}

	}

?>