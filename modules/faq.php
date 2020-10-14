<?php

krnLoadLib('define');
krnLoadLib('settings');

class faq extends krn_abstract {

	function __construct(){
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Title, Code, Content, ManagerId, FormBottomType, SeoTitle, SeoKeywords, SeoDescription FROM static_pages WHERE Code = ?s', 'voprosy-otvety');
		
		$this->pageTitle = $this->page['Title'];
		$this->breadCrumbs = GetBreadCrumbs(array(
			'Главная' => '/'),
			$this->pageTitle);
	}	

	function GetResult() {
		$Blocks = krnLoadModuleByName('blocks');

		if ($_GET['page']) $this->pageIndex = $_GET['page'];
		$result = krnLoadPageByTemplate($this->page['TemplateCode'] ?: 'base_useful');
		$this->content = $this->GetFaqList();
		
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
				'<%CLASS%>'	=> $Site->GetPageFromLink($item['Link']) == 'voprosy-otvety' ? ' class="active"' : '',
				'<%LINK%>'	=> $item['Link'],
				'<%TITLE%>'	=> $item['Title'],
			));
		}

		$result = SetContent(LoadTemplate('mn_folder'), $content);
		return $result;
	}
	
	/** Список вопросов */
	function GetFaqList() {
		$element = LoadTemplate('faq_el');
		$content = '';

		$recsOnPage = $this->settings->GetSetting('FaqRecsOnPage', 18);
		$query = 'SELECT DISTINCT COUNT(Id) FROM faq';
		$this->total = $this->db->getOne($query);

		$faq = $this->db->getAll('SELECT Title, Question, Answer FROM faq ORDER BY IF(`Order`,-200/`Order`,0) LIMIT ?i, ?i', ($this->pageIndex-1)*$recsOnPage, $this->pageIndex == 1 ? $recsOnPage+1 : $recsOnPage);
		foreach ($faq as $i => $item) {
			$item['Question'] = nl2br($item['Question']);
			$item['Answer'] = nl2br($item['Answer']);
			$content .= SetAtribs($element, $item);
		}
		if ($this->moreOnly) return $content;

		$more = ($recsOnPage < $this->total) ? GetMore('faqMore') : '';
		$pagination = GetPagination($this->total, $recsOnPage, 6, $this->pageIndex);

		$result = strtr(LoadTemplate('faq'), array(
			'<%CONTENT%>'		=> $content,
			'<%MORE%>'			=> $recsOnPage * $this->pageIndex >= $this->total ? '' : $more,
			'<%PAGINATION%>'	=> $this->total > $recsOnPage ? $pagination : '',
		));
		return $result;
	}

	/** Список вопросов - Показать еще */
	function GetMoreByPage() {
		krnLoadLib('settings');
		$this->pageIndex = (int)$_POST['page'];
		
		$recsOnPage = $this->settings->GetSetting('FaqRecsOnPage', 18);
		
		$this->moreOnly = true;
		$list = $this->GetFaqList();
		$pagination = GetPagination($this->total, $recsOnPage, 6, $this->pageIndex);
		
		$json = array(
			'status' => true,
			'list' => $list,
			'pagination' => $pagination,
			'last' => ($recsOnPage * $this->pageIndex >= $this->total) ? 1 : 0
		);
		return json_encode($json);
	}
}

?>