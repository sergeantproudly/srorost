<?php

krnLoadLib('define');
krnLoadLib('settings');

class news extends krn_abstract {

	function __construct() {
		global $_LEVEL;
		parent::__construct();
		
		$this->page = $this->db->getRow('SELECT Title, Code, Content, ManagerId, FormBottomType, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'news');
		
		if ($this->newCode = $_LEVEL[2]) {
			$this->new = $this->db->getRow('SELECT Id, Title, Code, Description AS Text, Date, Image764_436 AS Image, SeoTitle, SeoKeywords, SeoDescription FROM news WHERE Code = ?s', $this->newCode);
			$this->pageTitle = $this->new['Title'];
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

		if ($this->newCode) {
			$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_material');
			$this->content = $this->GetNew();
			
		} else {
			if ($_GET['page']) $this->pageIndex = $_GET['page'];
			$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_useful');
			$this->content = $this->GetNewsList();		
		}
		
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->new['SeoKeywords'] ?: $this->page['SeoKeywords'],
    		'<%META_DESCRIPTION%>'	=> $this->new['SeoDescription'] ?: $this->page['SeoDescription'],
    		'<%PAGE_TITLE%>'		=> $this->new['SeoTitle'] ?: $this->pageTitle,
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
				'<%CLASS%>'	=> $Site->GetPageFromLink($item['Link']) == 'news' ? ' class="active"' : '',
				'<%LINK%>'	=> $item['Link'],
				'<%TITLE%>'	=> $item['Title'],
			));
		}

		$result = SetContent(LoadTemplate('mn_folder'), $content);
		return $result;
	}
	
	/** Список новостей */
	function GetNewsList() {
		$element = LoadTemplate('news_el');
		$elementFst = LoadTemplate('news_el_fst');
		$content = '';

		$recsOnPage = $this->settings->GetSetting('NewsRecsOnPage', 18);
		$query = 'SELECT DISTINCT COUNT(Id) FROM news';
		$this->total = $this->db->getOne($query);

		$news = $this->db->getAll('SELECT Title, Date, Image344_196 AS Image, Image764_436 AS ImageBig, Code, Announce AS Text FROM news ORDER BY Date DESC LIMIT ?i, ?i', ($this->pageIndex-1)*$recsOnPage, $this->pageIndex == 1 ? $recsOnPage+1 : $recsOnPage);
		foreach ($news as $i => $new) {
			$new['Link'] = '/' . $this->page['Code'] . '/' . $new['Code'] . '/';
			if ($new['Image']) $new['Image'] = '<a href="' . $new['Link'] . '" class="photo"><img src="' . $new['Image'] . '" alt="' . htmlspecialchars($new['Title'], ENT_QUOTES) . '" title="' . htmlspecialchars($new['Title'], ENT_QUOTES) . '"/></a>';
			$new['Date'] = ModifiedDate($new['Date']);

			$content .= SetAtribs(($this->pageIndex == 1 && $i == 0) ? $elementFst : $element, $new);
		}
		if ($this->moreOnly) return $content;

		$more = ($recsOnPage < $this->total) ? GetMore('newsMore') : '';
		$pagination = GetPagination($this->total, $recsOnPage, 6, $this->pageIndex);

		$result = strtr(LoadTemplate('news'), array(
			'<%CONTENT%>'		=> $content,
			'<%MORE%>'			=> $recsOnPage * $this->pageIndex >= $this->total ? '' : $more,
			'<%PAGINATION%>'	=> $pagination,
		));
		return $result;
	}

	/** Список новостей - Показать еще */
	function GetMoreByPage() {
		krnLoadLib('settings');
		$this->pageIndex = (int)$_POST['page'];
		
		$recsOnPage = $this->settings->GetSetting('NewsRecsOnPage', 18);
		
		$this->moreOnly = true;
		$list = $this->GetNewsList();
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
	function GetNew() {
		$result = strtr(LoadTemplate('new'), array(
			'<%TITLE%>'			=> $this->new['Title'],
			'<%DATE%>'			=> ModifiedDate($this->new['Date']),
			'<%ALT%>'			=> htmlspecialchars($this->new['Title'], ENT_QUOTES),
			'<%IMAGE%>'			=> $this->new['Image'] ? '<div class="photo"><img src="'.$this->new['Image'].'" alt="'.$this->new['Alt'].'" title="'.$this->new['Alt'].'"></div>' : '',
			'<%TEXT%>'			=> $this->new['Text'],
			'<%SHAREURL%>'		=> urlencode($this->settings->GetSetting('SiteUrl') . '/news/' . $this->new['Code']),
			'<%SHARETITLE%>'	=> htmlspecialchars($this->new['Title'], ENT_QUOTES),
			'<%SHAREIMAGE%>'	=> $this->new['ShareImage'],
			'<%SHARETEXT%>'		=> htmlspecialchars(TrimText(strip_tags(str_replace('<br />', ' ', $this->new['Text'])), 300), ENT_QUOTES),
			'<%OTHERS%>'		=> $this->BlockOtherNews(),
		));
		return $result;
	}
	
	/** Блок - Другие новости */
	function BlockOtherNews() {
		$element = LoadTemplate('bl_news_other_el');
		$content = '';
		
		$recsInBlock = $this->settings->GetSetting('NewsRecsInBlock', 3);
		
		$query = 'SELECT Id, Title, Code, Date, Image344_196 AS Image FROM news WHERE Id <> ?i ORDER BY Date DESC LIMIT ?i, ?i';
		$news = $this->db->getAll($query, $this->new['Id'], 0, $recsInBlock);
		foreach ($news as $new) {
			$new['Link'] = '/news/' . $new['Code'];
			$new['Alt'] = htmlspecialchars($new['Title']);
			$new['Image'] = $new['Image'] ? '<a href="' . $new['Link'] . '" class="photo"><img src="' . $new['Image'] . '" alt="' . $new['Alt'] . '" title="' . $new['Alt'] . '"></a>' : '';
			$new['Date'] = ModifiedDate($new['Date']);
			$content .= SetAtribs($element, $new);
		}
		
		$result = $content ? SetContent(LoadTemplate('bl_news_other'), $content) : '';
		return $result;
	}
}

?>