<?php

krnLoadLib('define');
krnLoadLib('settings');

class law extends krn_abstract {

	function __construct() {
		global $_LEVEL;
		parent::__construct();

		$this->page = $this->db->getRow('SELECT Title, Header, Code, Content, ManagerId, FormBottomType, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'zakonodatelstvo');
		
		if ($this->lawCode = $_LEVEL[2]) {
			$this->law = $this->db->getRow('SELECT Id, Title, Code, Text, SeoTitle, SeoKeywords, SeoDescription FROM laws WHERE Code = ?s', $this->lawCode);
			$this->pageTitle = $this->law['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/',
				$this->page['Title'] => $this->page['Code'] . '/'),
				$this->pageTitle);

		} else {
			$this->pageTitle = $this->page['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/'),
				$this->pageTitle);
		}	
	}	

	function GetResult() {
		$Blocks = krnLoadModuleByName('blocks');

		if ($this->lawCode) {
			$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_material');
			$this->content = $this->GetLaw();
			
		} else {
			if ($_GET['page']) $this->pageIndex = $_GET['page'];
			$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_useful');
			$this->content = $this->GetLawsList();		
		}
		
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->law['SeoKeywords'] ?: $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->law['SeoDescription'] ?: $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->law['SeoTitle'] ?: $this->pageTitle,
    		'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
    		'<%MN_FOLDER%>'			=> $this->GetFolderMenu(),
    		'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
			'<%CONTENT%>'			=> $this->content,
			'<%FORM_BOTTOM%>'		=> $Blocks->BlockFormBottom($this->page['ManagerId'], $this->page['FormBottomType']),
		));
		
		$this->blocks = $Blocks->GetPageBlocks($this->page['Id']);
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}

	/** Меню разделов */
	function GetFolderMenu() {
		global $Site;
		$element = LoadTemplate('mn_folder_el');
		$content = '';

		$items = $this->db->getAll('SELECT * FROM menu_folder ORDER BY IF(`Order`,-10/`Order`,0)');
		foreach ($items as $item) {
			$content .= strtr($element, array(
				'<%CLASS%>'	=> $Site->GetPageFromLink($item['Link']) == 'zakonodatelstvo' ? ' class="active"' : '',
				'<%LINK%>'	=> $item['Link'],
				'<%TITLE%>'	=> $item['Title'],
			));
		}

		$result = SetContent(LoadTemplate('mn_folder'), $content);
		return $result;
	}
	
	/** Список законов */
	function GetLawsList() {
		$element = LoadTemplate('laws_el');
		$content = '';

		$laws = $this->db->getAll('SELECT Title, Code FROM laws ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($laws as $i => $law) {
			$law['Link'] = '/' . $this->page['Code'] . '/' . $law['Code'] . '/';

			$content .= SetAtribs($element, $law);
		}

		$result = strtr(LoadTemplate('laws'), array(
			'<%TITLE%>'		=> $this->page['Header'],
			'<%TEXT%>'		=> $this->page['Content'],
			'<%CONTENT%>'	=> $content
		));
		return $result;
	}

	/** Закон */
	function GetLaw() {
		$result = strtr(LoadTemplate('law'), array(
			'<%TITLE%>'			=> $this->law['Title'],
			'<%TEXT%>'			=> $this->law['Text'],
			'<%SHAREURL%>'		=> urlencode($this->settings->GetSetting('SiteUrl') . '/laws/' . $this->law['Code']),
			'<%SHARETITLE%>'	=> htmlspecialchars($this->law['Title'], ENT_QUOTES),
			'<%SHARETEXT%>'		=> htmlspecialchars(TrimText(strip_tags(str_replace('<br />', ' ', $this->law['Text'])), 300), ENT_QUOTES),
		));
		return $result;
	}
}

?>