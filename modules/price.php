<?php

class price extends krn_abstract{	

	function __construct() {
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Title, Header, Content, SeoTitle, SeoKeywords, SeoDescription, ManagerId, FormBottomType FROM static_pages WHERE Code = ?s', 'price-sro');
		
		$this->pageTitle = $this->page['Title'];
		$this->breadCrumbs = GetBreadCrumbs(array(
			'Главная' => ''),
			$this->pageTitle);
	}	

	function GetResult(){
		$Blocks = krnLoadModuleByName('blocks');

		$this->content = $this->GetPrice();

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

		$this->blocks = $Blocks->GetPageBlocks($this->page['Id']);
		foreach ($this->blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}
		return $result;
	}

	public function GetPrice() {
		krnLoadLib('sro');
		$sro = new Sro();

		$elementHeader = LoadTemplate('price_header_el');
		$element = LoadTemplate('price_el');
		$content = '';

		$categories = $this->db->getInd('Id', 'SELECT Id, Title FROM price_categories ORDER BY IF(`Order`, -1000/`Order`, 0)');
		$items = $this->db->getAll('SELECT Title, CategoryId, Price FROM price ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($items as $item) {
			$categories[$item['CategoryId']]['Items'][] = $item;
		}

		foreach ($categories as $category) {
			$content .= SetAtribs($elementHeader, $category);

			if (isset($category['Items'])) {
				foreach ($category['Items'] as $item) {
					if (strpos($item['Price'], 'min1') !== false) {
						$item['Price'] = str_replace('min1', number_format($sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID, true) ?: 110000, 0, '', ' '), $item['Price']);
					} elseif (strpos($item['Price'], 'min2') !== false) {
						$item['Price'] = str_replace('min2', number_format($sro->GetMinSroSums(SRO_PROJECTERS_TYPE_ID, true) ?: 54167, 0, '', ' '), $item['Price']);
					} elseif (strpos($item['Price'], 'min3') !== false) {
						$item['Price'] = str_replace('min3', number_format($sro->GetMinSroSums(SRO_PROSPECTORS_TYPE_ID, true) ?: 60000, 0, '', ' '), $item['Price']);
					}
					$content .= strtr($element, array(
						'<%TITLE%>'	=> $item['Title'],
						'<%PRICE%>'	=> $item['Price'],
						'<%HREF%>'	=> PAYMENT_LINK,
					));
				}
			}
		}

		$result = strtr(LoadTemplate('price'), array(
			'<%TEXT%>'	=> $this->page['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}
}

?>