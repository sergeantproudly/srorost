<?php

krnLoadLib('define');
krnLoadLib('settings');

class statues extends krn_abstract {

	function __construct() {
		global $_LEVEL;
		parent::__construct();
		
		$this->page = $this->db->getRow('SELECT Title, Code, Content, ManagerId, FormBottomType, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'stati');
		
		if ($this->statueCode = $_LEVEL[2]) {
			$this->statue = $this->db->getRow('SELECT Id, Title, Code, Description AS Text, Date, Image764_436 AS Image, SeoTitle, SeoKeywords, SeoDescription FROM statues WHERE Code = ?s', $this->statueCode);
			$this->pageTitle = $this->statue['Title'];
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

		if ($this->statueCode) {
			$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_material');
			$this->content = $this->GetStatue();
			
		} else {
			if ($_GET['page']) $this->pageIndex = $_GET['page'];
			$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_useful');
			$this->content = $this->GetStatuesList();		
		}
		
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->statue['SeoKeywords'] ?: $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->statue['SeoDescription'] ?: $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->statue['SeoTitle'] ?: $this->pageTitle,
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
				'<%CLASS%>'	=> $Site->GetPageFromLink($item['Link']) == 'stati' ? ' class="active"' : '',
				'<%LINK%>'	=> $item['Link'],
				'<%TITLE%>'	=> $item['Title'],
			));
		}

		$result = SetContent(LoadTemplate('mn_folder'), $content);
		return $result;
	}
	
	/** Список статей */
	function GetStatuesList() {
		$element = LoadTemplate('statues_el');
		$elementFst = LoadTemplate('statues_el_fst');
		$content = '';

		$recsOnPage = $this->settings->GetSetting('statuesRecsOnPage', 18);
		$query = 'SELECT DISTINCT COUNT(Id) FROM statues';
		$this->total = $this->db->getOne($query);

		$statues = $this->db->getAll('SELECT Title, Date, Image344_196 AS Image, Image764_436 AS ImageBig, Code, Announce AS Text FROM statues ORDER BY Date DESC LIMIT ?i, ?i', ($this->pageIndex-1)*$recsOnPage, $this->pageIndex == 1 ? $recsOnPage+1 : $recsOnPage);
		foreach ($statues as $i => $statue) {
			$statue['Link'] = '/' . $this->page['Code'] . '/' . $statue['Code'] . '/';
			if ($statue['Image']) $statue['Image'] = '<a href="' . $statue['Link'] . '" class="photo"><img src="' . $statue['Image'] . '" alt="' . htmlspecialchars($statue['Title'], ENT_QUOTES) . '" title="' . htmlspecialchars($statue['Title'], ENT_QUOTES) . '"/></a>';
			$statue['Date'] = ModifiedDate($statue['Date']);

			$content .= SetAtribs(($this->pageIndex == 1 && $i == 0) ? $elementFst : $element, $statue);
		}
		if ($this->moreOnly) return $content;

		$more = ($recsOnPage < $this->total) ? GetMore('statuesMore') : '';
		$pagination = GetPagination($this->total, $recsOnPage, 6, $this->pageIndex);

		$result = strtr(LoadTemplate('statues'), array(
			'<%CONTENT%>'		=> $content,
			'<%MORE%>'			=> $recsOnPage * $this->pageIndex >= $this->total ? '' : $more,
			'<%PAGINATION%>'	=> $pagination,
		));
		return $result;
	}

	/** Список статей - Показать еще */
	function GetMoreByPage() {
		krnLoadLib('settings');
		$this->pageIndex = (int)$_POST['page'];
		
		$recsOnPage = $this->settings->GetSetting('StatuesRecsOnPage', 18);
		
		$this->moreOnly = true;
		$list = $this->GetStatuesList();
		$pagination = GetPagination($this->total, $recsOnPage, 6, $this->pageIndex);
		
		$json = array(
			'status' => true,
			'list' => $list,
			'pagination' => $pagination,
			'last' => ($recsOnPage * $this->pageIndex >= $this->total) ? 1 : 0
		);
		return json_encode($json);
	}

	/** Новость */
	function GetStatue() {
		$result = strtr(LoadTemplate('statue'), array(
			'<%TITLE%>'			=> $this->statue['Title'],
			'<%DATE%>'			=> ModifiedDate($this->statue['Date']),
			'<%ALT%>'			=> htmlspecialchars($this->statue['Title'], ENT_QUOTES),
			'<%IMAGE%>'			=> $this->statue['Image'] ? '<div class="photo"><img src="'.$this->statue['Image'].'" alt="'.$this->statue['Alt'].'" title="'.$this->statue['Alt'].'"></div>' : '',
			'<%TEXT%>'			=> $this->statue['Text'],
			'<%SHAREURL%>'		=> urlencode($this->settings->GetSetting('SiteUrl') . '/statues/' . $this->statue['Code']),
			'<%SHARETITLE%>'	=> htmlspecialchars($this->statue['Title'], ENT_QUOTES),
			'<%SHAREIMAGE%>'	=> $this->statue['ShareImage'],
			'<%SHARETEXT%>'		=> htmlspecialchars(TrimText(strip_tags(str_replace('<br />', ' ', $this->statue['Text'])), 300), ENT_QUOTES),
			'<%OTHERS%>'		=> $this->BlockOtherstatues(),
		));
		return $result;
	}
	
	/** Блок - Другие статьи */
	function BlockOtherStatues() {
		$element = LoadTemplate('bl_statues_other_el');
		$content = '';
		
		$recsInBlock = $this->settings->GetSetting('StatuesRecsInBlock', 3);
		
		$query = 'SELECT Id, Title, Code, Date, Image344_196 AS Image FROM statues WHERE Id <> ?i ORDER BY Date DESC LIMIT ?i, ?i';
		$statues = $this->db->getAll($query, $this->statue['Id'], 0, $recsInBlock);
		foreach ($statues as $statue) {
			$statue['Link'] = '/statues/' . $statue['Code'];
			$statue['Alt'] = htmlspecialchars($statue['Title']);
			$statue['Image'] = $statue['Image'] ? '<a href="' . $statue['Link'] . '" class="photo"><img src="' . $statue['Image'] . '" alt="' . $statue['Alt'] . '" title="' . $statue['Alt'] . '"></a>' : '';
			$statue['Date'] = ModifiedDate($statue['Date']);
			$content .= SetAtribs($element, $statue);
		}
		
		$result = $content ? SetContent(LoadTemplate('bl_statues_other'), $content) : '';
		return $result;
	}
}

?>