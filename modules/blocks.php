<?php

krnLoadLib('define');
krnLoadLib('settings');

class blocks extends krn_abstract{
	
	private $page_id;
	private $blocks_info = array();
	private $forms_info = array();
	private $rel_codes_methods = array(
		'partners_sro' => 'BlockPartners',
		'partners_controls' => 'BlockPartners',
		'service_what_for2' => 'BlockServiceWhatFor2',
		'service_importants2' => 'BlockServiceWhatFor2',
		'target' => 'BlockTarget',
		'target2' => 'BlockTarget',
		'target3' => 'BlockTarget',
		'target4' => 'BlockTarget',
		'target5' => 'BlockTarget',
	);
	private $flag = false;

	public function __construct() {
		parent::__construct();
		
		global $Params;
		$query = 'SELECT p2b.*, b.Code '
				.'FROM `rel_pages_blocks` AS p2b '
				.'LEFT JOIN `blocks` AS b ON p2b.BlockId = b.Id '
				.'LEFT JOIN `static_pages` AS s ON p2b.PageId = s.Id '
				.'WHERE s.Code = ?s '
				.'ORDER BY IF(p2b.`Order`,-30/p2b.`Order`,0)';
		$blocks = $this->db->getAll($query, $Params['Site']['Page']['Code']);
		foreach ($blocks as $block) {
			$this->blocks_info[$block['Code']] = $block;
		}
		
		$forms = $this->db->getAll('SELECT * FROM `forms`');
		foreach ($forms as $form) {
			$this->forms_info[$form['Code']] = $form;
		}
	}
	
	public function GetResult() {}

	public function GetPageBlocks($pageId, $data = array()) {
		$this->page_id = $pageId;

		$html = [];
		foreach ($this->blocks_info as $code => $block) {
			unset($code_param);
			if (isset($this->rel_codes_methods[$code])) {
				$func = $this->rel_codes_methods[$code];
				$code_param = $code;
			} else {
				$func = 'Block';
				foreach (explode('_', $code) as $fragments) {
					$func .= ucfirst($fragments);
				}
			}
			if (method_exists($this, $func)) {
				if (isset($code_param)) {
					$html[] = $this->$func($code_param, $data);
				} else {
					$html[] = $this->$func($data);
				}
			} else {
				$html[] = $this->BlockText($code);
			}
		}
		return $html;
	}

	public function GetBlockParams($blockCode) {
		$params = [];
		foreach (explode(';', $this->blocks_info[$blockCode]['Params']) as $line) {
			list($param, $value) = explode(':', $line);
			$params[ucfirst(trim($param))] = trim($value);
		}
		return $params;
	}
	
	/** Блок - Текстовый */
	public function BlockText($code) {
		$result = LoadTemplate($code ? 'bl_'.$code : 'bl_text');
		$params = $this->GetBlockParams($code);
		
		$result = strtr($result, array(
			'<%HEADER%>'	=> $this->blocks_info[$code]['Header'],
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content']
		));
		if (isset($params['Layout']) && $params['Layout'] == 'limited') $result = '<div class="holder">' . $result . '</div>';
		return $result;
	}

	/** Блок - Форма */
	public function BlockForm($code) {
		$result = LoadTemplate($code);
		$result = strtr($result, array(
			'<%TITLE%>'	=> $this->forms_info[$code]['Title'],
			'<%TEXT%>'	=> $this->forms_info[$code]['Text'],
			'<%CODE%>'	=> $this->forms_info[$code]['Code']
		));
		return $result;
	}

	/** Блок - Город */
	public function BlockCity() {		
		$result = LoadTemplate('bl_city');
		$result = strtr($result, array(
			'<%ID%>'	=> $_SESSION['ClientUser']['City']['Id'],
			'<%TITLE%>'	=> $_SESSION['ClientUser']['City']['Title'],
		));
		return $result;
	}

	/** Блок - Форма перед футером */
	public function BlockFormBottom($managerId, $typeFull) {
		global $Params;
		$manager = $this->db->getRow('SELECT * FROM `managers` WHERE Id = ?i', $managerId);
		$code = 'feedback';

		if (isset($this->page_id)) {
			$pageFormHeader = $this->db->getOne('SELECT FormBottomHeader FROM static_pages WHERE Code = ?i', $this->page_id);
		} else {
			$pageFormHeader = $this->db->getOne('SELECT FormBottomHeader FROM static_pages WHERE Code = ?s', $Params['Site']['Page']['Code']);
		}

		if (!$typeFull) {
			$result = LoadTemplate($code);
			$result = strtr($result, array(
				'<%CODE%>'			=> $code,
				'<%ID%>'			=> $code,
				'<%ACTION%>'		=> '/ajax--act-Feedback/',
				'<%TITLE%>'			=> $pageFormHeader ?: $this->forms_info[$code]['Title'],
				//'<%TEXT%>'		=> $this->forms_info[$code]['Content'],
				'<%INTRO%>'			=> nl2br($manager['FormIntro']),
				'<%DESCRIPTION%>'	=> nl2br($manager['FormDescription']),
				'<%PHOTO%>'			=> $manager['PhotoMobile'],
				'<%ALT%>'			=> htmlspecialchars($manager['Name'] . ' ' . $manager['Surname'], ENT_QUOTES),
			));

		} else {
			$result = LoadTemplate($code.'2');
			$result = strtr($result, array(
				'<%CODE%>'			=> $code,
				'<%ID%>'			=> $code,
				'<%CLASS%>'			=> $manager['BannerClass'],
				'<%ACTION%>'		=> '/ajax--act-Feedback/',
				'<%TITLE%>'			=> $pageFormHeader ?: $this->forms_info[$code]['Title'],
				//'<%TEXT%>'		=> $this->forms_info[$code]['Content'],
				'<%AUTHOR%>'		=> $manager['Name'] . ' ' . $manager['Surname'],
				'<%DESCRIPTION%>'	=> $manager['Post'] .', ' . $manager['Experience'],
				'<%PHOTO%>'			=> $manager['Photo3'],
				'<%PHOTO_MOBILE%>'	=> $manager['PhotoMobile'],
				'<%ALT%>'			=> htmlspecialchars($manager['Name'] . ' ' . $manager['Surname'], ENT_QUOTES),
			));
		}
		
		return $result;
	}

	/** Блок - Услуги  */
	public function BlockServices() {
		$code = 'services';
		$element = LoadTemplate('bl_services_el');
		$content = '';

		$servicesTree = [];
		$servicesCategories = $this->db->getAll('SELECT * FROM `services_categories` ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($servicesCategories as $serviceCategory) {
			$servicesTree[$serviceCategory['Id']] = $serviceCategory;
		}
		$services = $this->db->getAll('SELECT * FROM `services` WHERE ShowOnMain = 1 ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($services as $service) {
			$servicesTree[$service['CategoryId']]['Services'][] = $service;
		}

		$counter = 0;
		foreach ($servicesTree as $serviceCategory) {
			$counter++;

			$list = '';
			if (isset($serviceCategory['Services'])) {
				foreach ($serviceCategory['Services'] as $service) {
					$list .= '<li><a href="/' . (!$service['SeoShortLink'] ? $serviceCategory['Code'] . '/' : '') . $service['Code'] . '/">' . $service['Title'] .'</a></li>';
				}
			}

			$content .= strtr($element, array(
				'<%CLASS%>'		=> 'sheet' . $counter,
				'<%TITLE%>'		=> $serviceCategory['Title'],
				'<%PRICE%>'		=> $serviceCategory['Price'] ? ('<span class="price">' . $serviceCategory['Price'] . '</span>') : '',
				'<%ALT%>'		=> htmlspecialchars($serviceCategory['Title'], ENT_QUOTES),
				'<%IMAGE%>'		=> $serviceCategory['Image'],
				'<%LINK%>'		=> '/' . $serviceCategory['Code'] . '/',
				'<%CONTENT%>'	=> $list,
			));
		}

		$result = strtr(LoadTemplate('bl_services'), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	/** Блок - Клиенты  */
	public function BlockClients() {
		$code = 'clients';
		$element = LoadTemplate('bl_clients_el');
		$content = '';

		$clients = $this->db->getAll('SELECT *, Image140_100 AS Image FROM `clients` ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($clients as $client) {
			$content .= strtr($element, array(
				'<%TITLE%>'	=> $client['Title'],
				'<%ALT%>'	=> htmlspecialchars($client['Title'], ENT_QUOTES),
				'<%IMAGE%>'	=> $client['Image'],
				'<%LINK%>'	=> $client['Link'] ?: '#',
			));
		}

		$result = strtr(LoadTemplate('bl_clients'), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	/** Блок - Отзывы  */
	public function BlockReviews() {
		$code = 'reviews';
		$element = LoadTemplate('bl_reviews_el');
		$content = '';

		$reviews = $this->db->getAll('SELECT *, Image AS ImageFull, Image173_248 AS Image FROM `reviews` ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($reviews as $review) {
			$content .= strtr($element, array(
				'<%TITLE%>'		=> $review['Title'],
				'<%ALT%>'		=> htmlspecialchars($review['Title'], ENT_QUOTES),
				'<%IMAGE%>'		=> $review['Image'],
				'<%IMAGEFULL%>'	=> $review['ImageFull'],
			));
		}

		$result = strtr(LoadTemplate('bl_reviews'), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	/** Блок - Рекомендательные письма  */
	public function BlockRecomendations() {
		$code = 'recomendations';
		$element = LoadTemplate('bl_recomendations_el');
		$content = '';

		$reviews = $this->db->getAll('SELECT *, Image AS ImageFull, Image173_248 AS Image FROM `recomendations` ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($reviews as $review) {
			$content .= strtr($element, array(
				'<%TITLE%>'		=> $review['Title'],
				'<%ALT%>'		=> htmlspecialchars($review['Title'], ENT_QUOTES),
				'<%IMAGE%>'		=> $review['Image'],
				'<%IMAGEFULL%>'	=> $review['ImageFull'],
			));
		}

		$result = strtr(LoadTemplate('bl_recomendations'), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code]['Content'],
			'<%CONTENT%>'	=> $content,
		));
		return $result;
	}

	/** Блок - Партнеры */
	public function BlockPartners($code) {
		if ($this->flag) {
			$this->flag = false;
			return '';
		}

		$elementFolder = LoadTemplate('bl_partners_folder');
		$element = LoadTemplate('bl_partners_folder_el');
		$content = [];

		if (isset($this->blocks_info['partners_sro'])) {
			$content['partners_sro'] = '';
		}
		if (isset($this->blocks_info['partners_controls'])) {
			$content['partners_controls'] = '';
		}
		$query = 'SELECT Title, Image140_90 AS Image, Link, IsControl FROM partners ' . (!isset($content['partners_sro']) ? 'WHERE IsControl = 1' : (!isset($content['partners_controls']) ? 'WHERE IsControl = 0' : '')) . ' ORDER BY IF(`Order`,-100/`Order`,0), Title';
		$partners = $this->db->getAll($query);
		foreach ($partners as $partner) {
			$content[$partner['IsControl'] ? 'partners_controls' : 'partners_sro'] .= strtr($element, array(
				'<%TITLE%>'	=> $partner['Title'],
				'<%ALT%>'	=> htmlspecialchars($partner['Title'], ENT_QUOTES),
				'<%IMAGE%>'	=> $partner['Image'],
				'<%LINK%>'	=> $partner['Link'],
			));
		}	

		$folders = strtr($elementFolder, array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%TEXT%>'		=> $this->blocks_info[$code]['Content'],
			'<%CONTENT%>'	=> $content[$code],
		));
		if ($code == 'partners_sro' && isset($content['partners_controls'])) {
			$folders .= strtr($elementFolder, array(
				'<%TITLE%>'		=> $this->blocks_info['partners_controls']['Header'],
				'<%TEXT%>'		=> $this->blocks_info['partners_controls']['Content'],
				'<%CONTENT%>'	=> $content['partners_controls'],
			));
			$this->flag = true;
		} elseif ($code == 'partners_controls' && isset($content['partners_sro'])) {
			$folders .= strtr($elementFolder, array(
				'<%TITLE%>'		=> $this->blocks_info['partners_sro']['Header'],
				'<%TEXT%>'		=> $this->blocks_info['partners_sro']['Content'],
				'<%CONTENT%>'	=> $content['partners_sro'],
			));
			$this->flag = true;
		}

		$result = SetContent(LoadTemplate('bl_partners'), $folders);
		return $result;
	}

	/** Блок - Команда  */
	public function BlockTeam() {
		$code = 'team';
		$elementStatistics = LoadTemplate('bl_team_statistics_el');
		$element = LoadTemplate('bl_team_el');		
		$contentStatistics = '';
		$content = '';

		$statistics = $this->db->getAll('SELECT * FROM `statistics` ORDER BY IF(`Order`,-10/`Order`,0)');
		foreach ($statistics as $stat) {
			$contentStatistics .= SetAtribs($elementStatistics, $stat);
		}

		$team = $this->db->getAll('SELECT *, Photo334_334 AS Photo FROM `team` ORDER BY IF(`Order`,-100/`Order`,0)');
		foreach ($team as $person) {
			if ($person['Id'] == TEAM_LEADER_ID) {
				$leader = $person;
			} else {
				$content .= strtr($element, array(
					'<%NAME%>'	=> $person['Name'] . ' ' . $person['Surname'],
					'<%ALT%>'	=> htmlspecialchars($person['Name'] . ' ' . $person['Surname'], ENT_QUOTES),
					'<%PHOTO%>'	=> $person['Photo'],
					'<%POST%>'	=> $person['Post'],
				));
			}
		}
		if (!isset($leader)) $leader = $team[0];

		$result = strtr(LoadTemplate('bl_team'), array(
			'<%TITLE%>'			=> $this->blocks_info[$code]['Header'],
			'<%TEXT%>'			=> $this->blocks_info[$code]['Content'],
			'<%STATISTICS%>'	=> $contentStatistics,
			'<%PHOTO%>'			=> $leader['Photo'],
			'<%NAME%>'			=> $leader['Name'] . ' ' . $leader['Surname'],
			'<%ALT%>'			=> htmlspecialchars($leader['Name'] . ' ' . $leader['Surname'], ENT_QUOTES),
			'<%POST%>'			=> $leader['Post'],
			'<%CONTENT%>'		=> $content,
		));
		return $result;
	}

	/** Блок - Калькулятор */
	public function BlockCalculator($service = array()) {
		krnLoadLib('calculator');
		$code = 'calculator';
		$data = $_POST;

		if (!$data['ajax']) {
			$params = $this->GetBlockParams($code);

			global $Params;
			$query = 'SELECT m.Name, m.NameGen, m.Tel, s.Id AS PageId '
					.'FROM managers AS m '
					.'LEFT JOIN static_pages s ON s.ManagerId = m.Id '
					.'WHERE s.Code = ?s';
			$manager = $this->db->getRow($query, $Params['Site']['Page']['Code']);

			$calc = new Calculator($manager['PageId']);

			$exists = $this->db->getOne('SELECT COUNT(Id) FROM calculator_steps WHERE TemplateId = ?i', $calc->GetTemplateId());

			if ($exists) {
				krnLoadLib('sro');
				$sro = new Sro();

				$sroTypeId = $calc->GetSroTypeId();

				/*
				if ($Params['Site']['Page']['Code'] == SRO_BUILDERS_SERVICE_CODE && $Params['Site']['Page']['Code'] == SRO_GENPODRYAD_SERVICE_CODE) {
					$basesum = $sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID) ?: 10000;
				} elseif ($Params['Site']['Page']['Code'] == SRO_BUILDERS_SERVICE_CODE) {
					$basesum = $sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID) ?: 4167;
				} elseif ($Params['Site']['Page']['Code'] == SRO_BUILDERS_SERVICE_CODE) {
					$basesum = $sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID) ?: 10000;
				}
				*/
				if ($sroTypeId == SRO_BUILDERS_TYPE_ID) {
					$basesum = $sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID) ?: 10000;
				} elseif ($sroTypeId == SRO_PROJECTERS_TYPE_ID) {
					$basesum = $sro->GetMinSroSums(SRO_PROJECTERS_TYPE_ID) ?: 4167;
				} elseif ($sroTypeId == SRO_PROSPECTORS_TYPE_ID) {
					$basesum = $sro->GetMinSroSums(SRO_PROSPECTORS_TYPE_ID) ?: 10000;
				}
			}

			$result = $exists ? strtr(LoadTemplate('bl_calculator'), array(
				'<%TITLE%>'			=> $this->blocks_info[$code]['Header'],
				'<%TEXT%>'			=> $this->blocks_info[$code]['Content'],
				'<%PAGEID%>'		=> $manager['PageId'],
				'<%SERVICEID%>'		=> $sroTypeId,
				'<%BASESUM%>'		=> $basesum,
				'<%BASEOPERATION%>'	=> $calc->GetBaseOperation(),
				'<%BASEADDITIONAL%>'	=> $calc->GetBaseAdditional(),
				'<%BASEEXTENDEDTITLE%>'	=> $calc->GetBaseExtendedTitle(),
				'<%CLASS%>'			=> $params['Class'] ?: 'type1',
				'<%ACTION%>'		=> '/ajax--act-Calculation/',
				'<%NAMEGEN%>'		=> $manager['NameGen'],
				'<%PHONELINK%>'		=> preg_replace('/[^\d\+]/', '', $manager['Tel']),
				'<%PHONE%>'			=> $manager['Tel'],
				'<%FINALHEADER%>'	=> $params['Header'] ?: 'Ориентировочная сумма',
				'<%FINALTEXT%>'		=> $params['Text'] ?: 'Для получения точной стоимости, укажите свой номер телефона — наш специалист свяжется с вами',
				'<%FINALBUTTON%>'	=> $params['Button'] ?: 'Узнать точную стоимость',
				'<%CODE%>'			=> $code,
			)) : '';
			return $result;

		} else {
			$calc = new Calculator($data['page_id']);

			krnLoadLib('sro');
			$sro = new Sro();

			$query = 'SELECT Id, Title, Button, VariantId, `Order` '
					.'FROM calculator_steps '
					.'WHERE TemplateId = ?i '
					.'ORDER BY IF(`Order`, -100/`Order`, 0)';
			$steps = $this->db->getInd('Id', $query,$calc->GetTemplateId());

			$exclusions = $calc->GetStepExclusionIds();

			$query = 'SELECT Id, Title, StepId, Operation, AdditionalAction, ExtendedTitle, `Order` '
					.'FROM calculator_step_variants '
					.'WHERE StepId IN (?a) '
					.'ORDER BY IF(`Order`, -100/`Order`, 0)';
			$variants = $this->db->getAll($query, array_keys($steps));
			foreach ($variants as $variant) {
				if (strpos($variant['Operation'], 'min1') !== false) {
					$variant['Operation'] = str_replace('min1', $sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID) ?: 10000, $variant['Operation']);
				} elseif (strpos($variant['Operation'], 'min2') !== false) {
					$variant['Operation'] = str_replace('min2', $sro->GetMinSroSums(SRO_PROJECTERS_TYPE_ID) ?: 4167, $variant['Operation']);
				} elseif (strpos($variant['Operation'], 'min3') !== false) {
					$variant['Operation'] = str_replace('min3', $sro->GetMinSroSums(SRO_PROSPECTORS_TYPE_ID) ?: 10000, $variant['Operation']);
				}
				$steps[$variant['StepId']]['Variants'][$variant['Id']] = $variant;
			}
			foreach ($steps as $step) {
				if (array_search($step['Id'], $exclusions) !== false) $step['Excluded'] = true;
				$result[$step['Order']][$step['VariantId']] = $step;
			}

			$json = json_encode($result);
			return $json;
		}
	}

	/** Блок - Таблица цен */
	public function BlockServicePricesTable() {
		$code = 'service_prices_table';
		$params = $this->GetBlockParams($code);
		$data = $_POST;

		if (!$data['ajax']) {
			$result = strtr(LoadTemplate('bl_' . $code), array(
				'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
				'<%TEXT%>'		=> $this->blocks_info[$code]['Content'],
			));
			return $result;

		} else {
			if (isset($data['type_id']) && $data['type_id']) {
				krnLoadLib('sro');
				$sro = new Sro();
				
				$companies = $sro->GetCompanies([
					'type_id'	=> $data['type_id'],
					'city_id'		=> $_SESSION['ClientUser']['City']['Id'],
					'region_id'		=> $_SESSION['ClientUser']['City']['RegionId'],
				]);

				$json = json_encode($companies);
				return $json;
			}
		}
	}

	/** Блок - Форма захвата */
	public function BlockTarget($code_param) {
		//$code = 'target';
		$code = $code_param;
		$params = $this->GetBlockParams($code);

		$manager = $this->db->getRow('SELECT Photo2 AS Photo FROM managers AS m LEFT JOIN static_pages p ON m.Id = p.ManagerId WHERE p.Id = ?i', $this->page_id);

		$result = strtr(LoadTemplate('bl_target'), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%ACTION%>'	=> '/ajax--act-Feedback/',
			'<%BUTTON%>'	=> $params['Button'],
			'<%LAW%>'		=> $params['Law'],
			'<%CLASS%>'		=> $params['Class'],
			'<%CODE%>'		=> 'target',
			'<%PHOTO%>'		=> $manager['Photo'],
		));
		return $result;
	}

	/** Блок - Как проходит обучение */
	public function BlockServiceEducationProcess() {
		$code = 'service_education_process';
		$params = $this->GetBlockParams($code);

		//Фотографии c занятий
		if (isset($params['Gallery']) && $params['Gallery']) {			
			$element = LoadTemplate('bl_gallery_el');
			$content = '';

			$photos = $this->db->getAll('SELECT Title, Image284_182 AS Image, Image AS ImageFull FROM education_photos ORDER BY IF(`Order`,-300/`Order`,0)');
			foreach ($photos as $photo) {
				$photo['Alt'] = htmlspecialchars($photo['Title'], ENT_QUOTES);
				$content .= SetAtribs($element, $photo);
			}

			$gallery = $content ? strtr(LoadTemplate('bl_gallery'), array(
				'<%TITLE%>'		=> 'Фотографии c занятий',
				'<%CONTENT%>'	=> $content,
			)) : '';
		}

		$result = strtr(LoadTemplate('bl_' . $code), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%GALLERY%>'	=> $gallery,
		));
		if (isset($params['Layout']) && $params['Layout'] == 'limited') $result = '<div class="holder">' . $result . '</div>';
		return $result;
	}

	/** Блок - Пример диплома */
	public function BlockServiceEducation($service) {
		$code = 'service_education';

		if (isset($service['Image'])) {
			$size = getimagesize($service['Image']);
			if ($size[0] > $size[1]) $horizontal = true;
		}

		$result = strtr(LoadTemplate('bl_' . $code), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%HORIZONTAL%>'=> isset($horizontal) ? ' horizontal' : '',
			'<%EXAMPLE%>'	=> isset($service['Image']) ? '<img src="' . $service['Image'] . '" alt="Пример диплома" title="Пример диплома"/>' : '',
		));
		return $result;
	}

	/** Блок - Пример сертификата ISO */
	public function BlockServiceCert($service) {
		$code = 'service_cert';

		$file = $this->db->getOne('SELECT File FROM files WHERE Code = ?s', 'iso_request');

		if (isset($service['Image'])) {
			$size = getimagesize($service['Image']);
			if ($size[0] > $size[1]) $horizontal = true;
		}

		$result = strtr(LoadTemplate('bl_' . $code), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%HORIZONTAL%>'=> isset($horizontal) ? ' horizontal' : '',
			'<%EXAMPLE%>'	=> isset($service['Image']) ? '<img src="' . $service['Image'] . '" alt="Пример сертификата" title="Пример сертификата"/>' : '',
			'<%FILE%>'		=> $file,
		));
		return $result;
	}

	/** Блок - Пример лицензии */
	public function BlockServiceTextLicense($service) {
		$code = 'service_text_license';

		$result = strtr(LoadTemplate('bl_' . $code), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%EXAMPLE%>'	=> isset($service['Image']) ? '<img src="' . $service['Image'] . '" alt="Пример лицензии" title="Пример лицензии" class="example"/>' : '',
		));
		return $result;
	}

	/** Блок - Необходимые документы */
	public function BlockServiceImportants() {
		$code = 'service_importants';
		$params = $this->GetBlockParams($code);

		$file = $this->db->getOne('SELECT File FROM files WHERE Code = ?s', 'iso_request');

		$result = strtr(LoadTemplate('bl_' . $code), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%CLASS%>'		=> isset($params['Class']) && $params['Class'] ? ' class="' . $params['Class'] . '"' : '',
			'<%FLEX%>'		=> isset($params['Flex']) && $params['Flex'] ? $params['Flex'] : '',
			'<%BOTTOM%>'	=> isset($params['Bottom']) && $params['Bottom'] ? $params['Bottom'] : '',
		));
		if (isset($params['Layout']) && $params['Layout'] == 'limited') $result = '<div class="holder">' . $result . '</div>';
		return $result;
	}

	/** Блок - Для чего нужен/нужна (II) */
	function BlockServiceWhatFor2() {
		if ($this->flag) {
			$this->flag = false;
			return '';
		}

		$code = 'service_what_for2';
		$code_docs = 'service_importants2';

		if (isset($this->blocks_info[$code_docs])) {
			$documents = strtr(LoadTemplate('bl_' . $code_docs), array(
				'<%TITLE%>'		=> $this->blocks_info[$code_docs]['Header'],
				'<%CONTENT%>'	=> $this->blocks_info[$code_docs]['Content'],
			));

			$this->flag = true;
		}

		$result = strtr(LoadTemplate('bl_' . $code), array(
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content'],
			'<%DOCUMENTS%>'	=> isset($documents) ? $documents : '',
		));
		return $result;
	}

	/** Блок - Требования для получения */
	public function BlockServiceTributes() {
		$code = 'service_tributes';
		$result = LoadTemplate('bl_' . $code);
		$params = $this->GetBlockParams($code);
		
		$result = strtr($result, array(
			'<%HEADER%>'	=> $this->blocks_info[$code]['Header'],
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content']
		));

		if (isset($params['Layout']) && $params['Layout'] == 'limited') $result = '<div class="holder">' . $result . '</div>';

		if (isset($this->blocks_info['service_risks'])) {
			$keys = array_keys($this->blocks_info);
			if ($keys[array_search('service_risks', $keys) - 1] == 'service_tributes') {
				$result = '<div class="holder">' . $result;
			}
		}
		return $result;
	}

	/** Блок - Возможные риски */
	public function BlockServiceRisks() {
		$code = 'service_risks';
		$result = LoadTemplate('bl_' . $code);
		$params = $this->GetBlockParams($code);
		
		$result = strtr($result, array(
			'<%HEADER%>'	=> $this->blocks_info[$code]['Header'],
			'<%TITLE%>'		=> $this->blocks_info[$code]['Header'],
			'<%CONTENT%>'	=> $this->blocks_info[$code]['Content']
		));

		if (isset($params['Layout']) && $params['Layout'] == 'limited') $result = '<div class="holder">' . $result . '</div>';

		if (isset($this->blocks_info['service_tributes'])) {
			$keys = array_keys($this->blocks_info);
			if ($keys[array_search('service_tributes', $keys) + 1] == 'service_risks') {
				$result = $result . '</div>';
			}
		}
		return $result;
	}
}
?>