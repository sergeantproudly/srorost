<?php

krnLoadLib('settings');

class main extends krn_abstract{	

	public function __construct(){
		parent::__construct();
		$this->page = $this->db->getRow('SELECT Id, Title, Content, SeoTitle, SeoKeywords, SeoDescription, ManagerId, FormBottomType FROM static_pages WHERE Code="main"');
		
		global $Config;
		$this->pageTitle = $this->page['Title'] ?: $this->settings->GetSetting('SiteTitle', $Config['Site']['Title'] ?: 'Главная');
	}	

	public function GetResult(){
		krnLoadLib('modal');
		global $Site;
		$modalWhatcost = new Modal('whatcost', ['Action' => '/ajax--act-Consultation/']);
		$Site->addModal($modalWhatcost->getModal());

		global $Config;
		$Blocks = krnLoadModuleByName('blocks');

		$blocks = $Blocks->GetPageBlocks($this->page['Id']);
		
		$result = krnLoadPageByTemplate('base_main');
		$result = strtr($result, array(
			'<%META_KEYWORDS%>'		=> $this->page['Keywords'] ?: $Config['Site']['Keywords'],
			'<%META_DESCRIPTION%>'	=> $this->page['Description'] ?: $Config['Site']['Description'],
			'<%PAGE_TITLE%>'		=> $this->pageTitle,
			'<%SLIDER%>'			=> $this->GetSlider(),
			'<%ADVANTAGES%>'		=> $this->GetAdvantages(),
			'<%FORM_BOTTOM%>'		=> $Blocks->BlockFormBottom($this->page['ManagerId'], $this->page['FormBottomType']),
		));

		foreach ($blocks as $i => $block) {
			$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
		}

		return $result;
	}

	/** Слайдер на главной */
	function GetSlider() {
		$element = LoadTemplate('slider_el');
		$content = '';
		$nav = '';

		$query = 'SELECT s.*, m.Name, m.Surname, m.Photo1 AS Photo, m.BannerClass '
				.'FROM slider s '
				.'LEFT JOIN managers m ON s.ManagerId = m.Id '
				.'ORDER BY IF(s.`Order`,-100/s.`Order`,0)';
		$slider = $this->db->getAll($query);
		foreach ($slider as $i => $slide) {
			$content .= strtr($element, array(
				'<%ACTIVE%>'	=> $i == 0 ? ' class="active"' : '',
				'<%CLASS%>'		=> $slide['BannerClass'],
				'<%HEADER%>'	=> $slide['Header'],
				'<%SUBHEADER%>'	=> $slide['Subheader'],
				'<%LINK%>'		=> $slide['Link'],
				'<%BUTTON1%>'	=> $slide['Button1'] ?: 'Узнать стоимость',
				'<%BUTTON2%>'	=> $slide['Button2'] ?: 'Подробнее',
				'<%IMAGE%>'		=> $slide['Photo'],
				'<%ALT%>'		=> htmlspecialchars($slide['NavTitle'], ENT_QUOTES),
			));

			$nav .= '<li' . ($i == 0 ? ' class="active"' : '') . '>' . $slide['NavTitle'] . '</li>';
		}

		$result = strtr(LoadTemplate('slider'), array(
			'<%CONTENT%>'		=> $content,
			'<%NAV%>'		=> $nav,
			'<%AUTOSECONDS%>'	=> $this->settings->GetSetting('SliderAutoSeconds'),
		));
		return $result;
	}

	/** Преимущества на главной */
	function GetAdvantages() {
		$element = LoadTemplate('bl_services_advantages_el');
		$content = '';

		$advantages = $this->db->getAll('SELECT a.* FROM advantages a WHERE a.OnMain = 1 ORDER BY IF(a.`Order`, -10/a.`Order`, 0) LIMIT 0, 3');
		foreach ($advantages as $num => $advantage) {
			$class = $advantage['Class'] ?: 'advantage'.($num+1);
			$content .= strtr($element, array(
				'<%CLASS%>'	=> $class,
				'<%ICON%>'	=> $advantage['Icon'],
				'<%TITLE%>'	=> $advantage['Title'],
				'<%ALT%>'	=> htmlspecialchars($advantage['Title'], ENT_QUOTES),
			));
		}

		return SetContent(LoadTemplate('bl_services_advantages'), $content);
	}

}
?>