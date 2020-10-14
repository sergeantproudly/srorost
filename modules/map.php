<?php

class map extends krn_abstract{	

	function __construct() {
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Title, Header, Content, SeoTitle, SeoKeywords, SeoDescription, ManagerId, FormBottomType FROM static_pages WHERE Code = ?s', 'karta-sayta');
		
		$this->pageTitle = $this->page['Title'];
		$this->breadCrumbs = GetBreadCrumbs(array(
			'Главная' => ''),
			$this->pageTitle);
	}	

	function GetResult(){
		$Blocks = krnLoadModuleByName('blocks');

		$this->content = $this->GetMap();

		$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_static');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->pageTitle,
    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
			'<%CONTENT%>'			=> $this->content,
			'<%FORM_BOTTOM%>'		=> $Blocks->BlockFormBottom($this->page['ManagerId'], $this->page['FormBottomType']),
		));
		return $result;
	}
	
	function GetMap() {		
		$arr = $this->db->getAll('SELECT Title, Code FROM static_pages ORDER BY IF(`Order`,-1000/`Order`,0)');
		foreach ($arr as $static_page){
			$static_pages[$static_page['Code']] = $static_page;
		}

		$tree = array();

		//statics
		$items =$this->db->getAll('SELECT Title, Code FROM static_pages ORDER BY IF(`Order`,-1000/`Order`,0)');
		foreach ($items as $item) {
			if ($item['Code'] != 'services' && $item['Code'] != 'news' && $item['Code'] != 'stati' && $item['Code'] != 'zakonodatelstvo') {
				if ($item['Code'] == 'main') $item['Code'] = '/';
				$tree[$item['Code']] = $item;
			}
		}
		
		// services
		$items = $this->db->getAll('SELECT s.Title, s.Code, c.Code AS CategoryCode, s.SeoShortLink FROM services s LEFT JOIN services_categories c ON s.CategoryId = c.Id ORDER BY s.CategoryId, IF(s.`Order`,-1000/s.`Order`,0)');
		foreach ($items as $item) {
			if (!$item['SeoShortLink']) {
				$tree[$item['Code']] = array(
					'Title'	=> $item['Title'],
					'Code'	=> $item['CategoryCode'] . '/' . $item['Code'],
				);
			} else {
				$tree[$item['Code']] = $item;
			}			
		}

		// news
		$tree['news'] = $static_pages['news'];
		$items = $this->db->getAll('SELECT Title, Code FROM news ORDER BY Date DESC');
		foreach ($items as $item) {
			$tree['news']['pages'][$item['Code']] = $item;
		}
		
		// statues
		$tree['stati'] = $static_pages['stati'];
		$items = $this->db->getAll('SELECT Title, Code FROM statues ORDER BY Date DESC');
		foreach ($items as $item) {
			$tree['stati']['pages'][$item['Code']] = $item;
		}
		
		// law
		$tree['zakonodatelstvo'] = $static_pages['zakonodatelstvo'];
		$items = $this->db->getAll('SELECT Title, Code FROM laws ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($items as $item) {
			$tree['zakonodatelstvo']['pages'][$item['Code']] = $item;
		}
		
		$content = '';
		foreach ($tree as $code => $item) {
			$sub = '';
			if ($item['pages']) {
				foreach ($item['pages'] as $sub_code => $sub_item) {
					$sub .= '<li><a href="/'.$code.'/'.$sub_code.'/">' . $sub_item['Title'] . '</a></li>';
				}
				$sub = '<ul>' . $sub . '</ul>';
			}

			if ($code != '/') {
				$content .= '<li><a href="/'.$item['Code'].'/">'.$item['Title'].'</a>'.$sub.'</li>';
			} else {
				$content .= '<li><a href="'.$item['Code'].'">'.$item['Title'].'</a>'.$sub.'</li>';
			}
		}

		$result = SetContent(LoadTemplate('map'), $content);
		return $result;
	}

}

?>