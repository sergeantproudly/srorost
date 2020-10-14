<?php

krnLoadLib('define');
krnLoadLib('settings');

class services extends krn_abstract{	

	function __construct(){
		global $_LEVEL;
		parent::__construct();
		
		// категория услуг
		if ($this->categoryCode = $_LEVEL[1]) {
			// хак для лицензирования
			if ($this->categoryCode == 'litsenzirovanie') $this->categoryCode = 'sertifikat-iso';

			// проверяем не является ли полученный код сокращенным (относится не к разделу услуг, а к услуге (как все услуги Допусков СРО))			
			if ($category = $this->db->getRow('SELECT c.Code FROM services s LEFT JOIN services_categories c ON s.CategoryId = c.Id WHERE s.Code = ?s AND s.SeoShortLink = 1', $this->categoryCode)) {
				$this->serviceCode = $_LEVEL[1]; // тогда полученный код - это код услуги
				$this->categoryCode = $category['Code'];
			}

			$query = 'SELECT c.Id, c.Title, c.Header, c.Subheader, c.PriceHeader, c.Button1, c.Button2, '
					.'p.Id AS PageId, p.SeoTitle, p.SeoKeywords, p.SeoDescription, p.TemplateCode, p.ManagerId, p.FormBottomType '
					.'FROM services_categories c '
					.'LEFT JOIN static_pages p ON c.Code = p.Code '
					.'WHERE c.Code = ?s';
			$this->category = $this->db->getRow($query, $this->categoryCode);

			if (!$this->serviceCode) {
				if (!$this->category) {
					$this->notFound = true;
				}

				$this->pageTitle = $this->category['SeoTitle'] ?: $this->category['Title'];
				$this->breadCrumbs = GetBreadCrumbs(array(
					'Главная' => ''),
					$this->pageTitle);
			}
		} 

		// услуга
		if ($this->serviceCode || $_LEVEL[2]) {
			if (!$this->serviceCode) $this->serviceCode = $_LEVEL[2];

			$query = 'SELECT s.Id, s.Title, s.CategoryId, s.Header, s.Subheader, s.PriceHeader, s.Button1, s.Button2, s.Image, '
					.'p.Id AS PageId, p.SeoTitle, p.SeoKeywords, p.SeoDescription, p.TemplateCode, p.ManagerId, p.FormBottomType, '
					.'c.Title AS CategoryTitle, c.Code AS CategoryCode '
					.'FROM services s '
					.'LEFT JOIN static_pages p ON s.Code = p.Code '
					.'LEFT JOIN services_categories c ON s.CategoryId = c.Id ' 
					.'WHERE s.Code = ?s';
			$this->service = $this->db->getRow($query, $this->serviceCode);
			if (!$this->service) {
				$this->notFound = true;
			}

			$this->pageTitle = $this->service['SeoTitle'] ?: $this->service['Title'];
			$this->breadCrumbs = GetBreadCrumbs(array(
				'Главная' => '/',
				$this->service['CategoryTitle'] => $this->service['CategoryCode'] . '/'),
				$this->pageTitle);
		}

		// категория или услуга не найдены
		if (!$this->category && !$this->service) {
			$this->notFound = true;
		}
	}	

	function GetResult() {
		krnLoadLib('modal');
		global $Site;
		$modalConsultation = new Modal('consultation', ['Action' => '/ajax--act-Consultation/']);
		$modalWhatcost = new Modal('whatcost', ['Action' => '/ajax--act-Consultation/']);
		$Site->addModal($modalConsultation->getModal());
		$Site->addModal($modalWhatcost->getModal());

		$this->blocks = krnLoadModuleByName('blocks');

		if ($this->notFound) {
			header('HTTP/1.1 404 Not Found');
			header('Status: 404 Not Found');
			$this->pageTitle = 'Увы, пусто';
			$result = krnLoadPageByTemplate('base_static');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->page['SeoKeywords'],
				'<%META_DESCRIPTION%>'	=> $this->page['SeoDescription'],
				'<%PAGE_TITLE%>'		=> $this->pageTitle,
				'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
				'<%TITLE%>'				=> $this->page['Header'] ?: $this->page['Title'],
				'<%CONTENT%>'			=> LoadTemplate('404'),
				'<%FORM_BOTTOM%>'		=> $this->blocks->BlockFormBottom($this->page['ManagerId'], $this->page['FormBottomType']),
			));
			return $result;
		}

		if ($this->service) {
			$this->content = $this->GetService();
			$result = krnLoadPageByTemplate('base_service');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->service['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->service['SeoDescription'] ?: $Config['Site']['Description'],
	    		'<%PAGE_TITLE%>'		=> $this->service['SeoTitle'] ?: $this->pageTitle,
	    		//'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
	    		'<%BREAD_CRUMBS%>'		=> '',
				'<%CONTENT%>'			=> $this->content,
				'<%BANNER%>'			=> $this->GetBanner($this->service),
				'<%ADVANTAGES%>'		=> $this->GetServiceAdvantages($this->service),
				'<%TITLE%>'				=> $this->pageTitle,
				'<%FORM_BOTTOM%>'		=> $this->blocks->BlockFormBottom($this->service['ManagerId'], $this->service['FormBottomType']),
			));

		} elseif ($this->category) {
			$this->content = $this->GetCategory();	
			$result = krnLoadPageByTemplate('base_service');
			$result = strtr($result, array(
				'<%META_KEYWORDS%>'		=> $this->category['SeoKeywords'] ?: $Config['Site']['Keywords'],
				'<%META_DESCRIPTION%>'	=> $this->category['SeoDescription'] ?: $Config['Site']['Description'],
	    		'<%PAGE_TITLE%>'		=> $this->category['SeoTitle'] ?: $this->pageTitle,
	    		//'<%BREAD_CRUMBS%>'		=> $this->breadCrumbs,
	    		'<%BREAD_CRUMBS%>'		=> '',
	    		'<%TITLE%>'				=> $this->pageTitle,
	    		'<%BANNER%>'			=> $this->GetBanner($this->category),
	    		'<%ADVANTAGES%>'		=> $this->GetCategoryAdvantages($this->category),
				'<%CONTENT%>'			=> $this->content,
				'<%FORM_BOTTOM%>'		=> $this->blocks->BlockFormBottom($this->category['ManagerId'], $this->category['FormBottomType']),
			));
		}
				
		return $result;
	}

	/** Баннер вверху */
	function GetBanner($service) {
		if ($service['Id'] == EDUCATION_SERVICE_ID) {
			$manager = $this->db->getRow('SELECT Name, Surname, Photo4 AS Image FROM managers WHERE Id = ?i', $service['ManagerId']);
			$class = 'type4';
		} else {
			$manager = $this->db->getRow('SELECT Name, Surname, Photo1 AS Image, BannerClass FROM managers WHERE Id = ?i', $service['ManagerId']);
			$class = $manager['BannerClass'];
		}
		
		$result = strtr(LoadTemplate('bl_service_banner'), array(
			'<%IMAGE%>'		=> $manager['Image'],
			'<%ALT%>'		=> htmlspecialchars($manager['Name'] . ' ' . $manager['Surname'], ENT_QUOTES),
			'<%CLASS%>'		=> $class,
			'<%HEADER%>'	=> $service['Header'],
			'<%PRICEHEADER%>'	=> $service['PriceHeader'] ? '<p class="animated fadeInRightSmall price fast delay-03s">' . $service['PriceHeader'] . '</p>' : '',
			'<%SUBHEADER%>'	=> $service['Subheader'] ? '<p class="animated fadeInRightSmall fast delay-0' . ($service['PriceHeader'] ? '6' : '3') . 's">' . $service['Subheader'] . '</p>' : '',
			'<%BUTTON1%>'	=> $service['Button1'] ?: 'Узнать стоимость',
			'<%BUTTON2%>'	=> $service['Button2'] ?: 'Получить консультацию',
		));
		return $result;
	}

	/** Преимущества услуги */
	function GetServiceAdvantages($service) {
		$element = LoadTemplate('bl_services_advantages_el');
		$content = '';

		$advantages = $this->db->getAll('SELECT a.* FROM rel_services_advantages s2a LEFT JOIN advantages a ON s2a.AdvantageId = a.Id WHERE s2a.ServiceId = ?i ORDER BY IF(s2a.`Order`, -10/s2a.`Order`, 0), IF(a.`Order`, -10/a.`Order`, 0)', $service['Id']);
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

	/** Преимущества услуги */
	function GetCategoryAdvantages($service) {
		$element = LoadTemplate('bl_services_advantages_el');
		$content = '';

		$advantages = $this->db->getAll('SELECT a.* FROM rel_services_advantages s2a LEFT JOIN advantages a ON s2a.AdvantageId = a.Id WHERE s2a.ServiceId = ?i ORDER BY IF(s2a.`Order`, -10/s2a.`Order`, 0), IF(a.`Order`, -10/a.`Order`, 0)', $service['Id']);
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

	/** Категория услуг */
	function GetCategory() {
		// хак для изображения сертификата на категории Сертификаты и лицензии
		if ($this->category['Id'] == CERTIFICATES_CATEGORY_ID) {
			$this->category['Image'] = $this->db->getOne('SELECT Image FROM services WHERE Id = ?i', ISO9001_SERVICE_ID);
		}

		$blocks = $this->blocks->GetPageBlocks($this->category['PageId'], $this->category);

		if ($this->category['TemplateCode']) {
			$result = LoadTemplate($this->category['TemplateCode']);
			foreach ($blocks as $i => $block) {
				$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
			}
		} else {
			$result = implode(PHP_EOL, $blocks);
		}

		return $result;
	}
	
	/** Услуга */
	function GetService() {
		$blocks = $this->blocks->GetPageBlocks($this->service['PageId'], $this->service);

		if ($this->service['TemplateCode']) {
			$result = LoadTemplate($this->service['TemplateCode']);
			foreach ($blocks as $i => $block) {
				$result = str_replace('<%BLOCK' . ($i + 1) . '%>', $block, $result);
			}
		} else {
			$result = implode(PHP_EOL, $blocks);
		}

		return $result;
	}
}

?>