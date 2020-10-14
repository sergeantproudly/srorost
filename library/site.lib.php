<?php
    
    class Site {

    	protected $db;
		protected $settings;

		protected $modals;

		public function __construct() {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->settings = $Settings;
		}

    	public function GetCurrentPage() {
			$page = false;
			if (preg_match('/\/([a-zA-Z0-9_\-\-]+)\/?$/', $_SERVER['REQUEST_URI'], $match)) {
				$page = $match[1];
			} elseif (preg_match('/\/$/', $_SERVER['REQUEST_URI'])) {
				$page = '/';
			}
			return $page;
		}

		public function GetPageFromLink($link) {
			$page = false;
			if (preg_match('/\/([a-zA-Z0-9_\-]+)\/?$/', $link, $match)) {
				$page = $match[1];
			} elseif (preg_match('/\/$/', $link)) {
				$page = '/';
			}
			return $page;
		}

		public function SetLinks($html) {
			$result = preg_replace('~<a +href="(?!http[s]?://)([^\>]+)~i', '<a href="/$1', $html);
			return strtr($result, array(
				'<a href="//'		=> '<a href="/',
				'<a href="/#'		=> '<a href="#',
				'<a href="/tel:'	=> '<a href="tel:',
				'<a href="/mailto'	=> '<a href="mailto' 
			));
		}

		public function AddModal($html) {
			$this->modals .= $html;
		}

		public function GetModals() {
			return $this->modals;
		}

		public function GetPage() {
			krnLoadLib('define');
			krnLoadLib('menu');
			krnLoadLib('modal');
			global $krnModule;

			$Blocks = krnLoadModuleByName('blocks');
			$Main = krnLoadModuleByName('main');

			// menus
			$menuMain = new Menu([
				'menuDb'			=> 'menu_items',
				'subMenuDb'			=> 'menu_sub_items',
				'template'			=> 'mn_main',
				'templateEl'		=> 'mn_main_el',
				'templateElAct'		=> 'mn_main_el_act',
				'templateSub'		=> 'mn_sub',
				'templateSubEl'		=> 'mn_sub_el',
				'templateSubElAct'	=> 'mn_sub_el_act',
			]);

			$menuBottom1 = new Menu([
				'menuDb'			=> 'menu_bottom1_items',
				'template'			=> 'mn_bottom',
				'templateEl'		=> 'mn_bottom_el',
				'templateElAct'		=> 'mn_bottom_el_act',
			]);

			$menuBottom2 = new Menu([
				'menuDb'			=> 'menu_bottom2_items',
				'template'			=> 'mn_bottom',
				'templateEl'		=> 'mn_bottom_el',
				'templateElAct'		=> 'mn_bottom_el_act',
			]);

			$menuBottom3 = new Menu([
				'menuDb'			=> 'menu_bottom3_items',
				'template'			=> 'mn_bottom',
				'templateEl'		=> 'mn_bottom_el',
				'templateElAct'		=> 'mn_bottom_el_act',
			]);

			$menuBottomHeaderTemplate = LoadTemplate('mn_bottom_header');
			$menuItemsForHeaders = $this->db->getAll('SELECT Id, Title, Link FROM menu_items WHERE Id IN (?a)', [SRO_ITEM_ID, CERTS_ITEM_ID, SERVICES_ITEM_ID]);
			foreach ($menuItemsForHeaders as $item) {
				$menuBottomHeader[$item['Id']] = $item;
			}

			// contacts
			$contacts = $this->db->getRow('SELECT * FROM contacts WHERE CityId = ?i', $_SESSION['ClientUser']['City']['Id']);
			if (!$contacts) {
				$contacts = $this->db->getRow('SELECT * FROM contacts WHERE CityId = ?i', CITY_MOSCOW_ID);
				$phone1 = $contacts['Phone1'];
				//$phone2 = $contacts['Phone2'];
				$phoneTitle1 = $contacts['PhoneTitle1'];
				//$phoneTitle2 = $contacts['PhoneTitle2'];
			} else {
				$phone1 = $contacts['Phone2'];
				$phone2 = $contacts['Phone1'];
				$phoneTitle1 = $contacts['PhoneTitle2'];
				$phoneTitle2 = $contacts['PhoneTitle1'];
			}

			// tels
			$tels = '';
			if ($phone1 || $phoneTitle1) {
				$tels .= '<a href="tel:' . preg_replace('/[^\d\+]/', '', $phone1) . '"><span>' . $phoneTitle1 . '</span>' . $phone1 . '</a>';
			}
			if ($phone2 || $phoneTitle2) {
				$tels .= '<a href="tel:' . preg_replace('/[^\d\+]/', '', $phone2) . '"><span>' . $phoneTitle2 . '</span>' . $phone2 . '</a>';
			}

			// settings
			$siteTitle = $this->settings->GetSetting('SiteTitle', $Config['Site']['Title']);

			// user agreement and policy
			$files = $this->db->getAll('SELECT Title, Code, File FROM files');
			foreach ($files as $file) {
				if ($file['Code'] == 'agreement' || $file['Code'] == 'policy') $law[$file['Code']] = '<a href="' . $file['File'] . '">' . $file['Title'] . '</a>';
			}

			// sro average sums
			krnLoadLib('sro');
			$sro = new Sro();
			$avsum[SRO_BUILDERS_TYPE_ID] = $sro->GetMinSroSums(SRO_BUILDERS_TYPE_ID, true);
			$avsum[SRO_PROJECTERS_TYPE_ID] = $sro->GetMinSroSums(SRO_PROJECTERS_TYPE_ID, true);
			$avsum[SRO_PROSPECTORS_TYPE_ID] = $sro->GetMinSroSums(SRO_PROSPECTORS_TYPE_ID, true);

			// base modals
			$modalGeo = new Modal('geo');
			$modalFeedback = new Modal('feedback', ['Action' => '/ajax--act-Feedback/']);
			$modalCallback = new Modal('callback', ['Action' => '/ajax--act-Callback/']);
			$modalDone = new Modal('done');
			$this->addModal($modalGeo->GetModal());
			$this->addModal($modalFeedback->GetModal());
			$this->addModal($modalCallback->GetModal());
			$this->addModal($modalDone->GetModal());

			$result = strtr($krnModule->GetResult(), array(
		    	'<%META_KEYWORDS%>'			=> $Config['Site']['Keywords'],
		    	'<%META_DESCRIPTION%>'		=> $Config['Site']['Description'],
		    	'<%META_IMAGE%>'			=> '',
		    	'<%PAGE_TITLE%>'			=> $siteTitle,
		    	'<%SITE_URL%>'				=> $this->settings->GetSetting('SiteUrl', $Config['Site']['Url']),
		    	'<%ADMINEMAIL1%>'			=> $contacts['Email1'],
		    	'<%ADMINEMAIL2%>'			=> $contacts['Email2'],
		    	'<%SITE_TITLE%>'			=> $siteTitle,
		    	'<%SITE_TITLE_ALT%>'		=> htmlspecialchars($siteTitle, ENT_QUOTES),
		    	'<%TELS%>'					=> $tels,
		    	'<%PHONETITLE1%>'			=> $phoneTitle1,
		    	'<%PHONETITLE2%>'			=> $phoneTitle2,
		    	'<%PHONENUMBER1%>'			=> $phone1,
		    	'<%PHONENUMBER2%>'			=> $phone2,
		    	'<%PHONENUMBERLINK1%>'		=> preg_replace('/[^\d\+]/', '', $phone1),
		    	'<%PHONENUMBERLINK2%>'		=> preg_replace('/[^\d\+]/', '', $phone2),
		    	'&lt;%PHONENUMBER1%&gt;'	=> $phone1,
		    	'&lt;%PHONENUMBER2%&gt;'	=> $phone2,
		    	'&lt;%PHONENUMBERLINK1%&gt;'=> preg_replace('/[^\d\+]/', '', $phone1),
		    	'&lt;%PHONENUMBERLINK2%&gt;'=> preg_replace('/[^\d\+]/', '', $phone2),
		    	'<%FEEDBACKTITLE%>'			=> $this->settings->GetSetting('FeedbackTitle'),
		    	'<%META_VERIFICATION%>'		=> $this->settings->GetSetting('MetaVerification'),
		    	'<%YANDEX_METRIKA%>'		=> $this->settings->GetSetting('YandexMetrika'),
		    	'<%MN_MAIN%>'				=> $menuMain->GetMenu(),
		    	'<%BL_CITY%>'				=> $Blocks->BlockCity(),
		    	'<%CITY%>'					=> $_SESSION['ClientUser']['City']['Title'],
		    	'<%CITYGEN%>'				=> $_SESSION['ClientUser']['City']['TitleGen'],
		    	'<%CITYGEN2%>'				=> $_SESSION['ClientUser']['City']['TitleGen2'],
		    	'&lt;%CITY%&gt;'			=> $_SESSION['ClientUser']['City']['Title'],
		    	'&lt;%CITYGEN%&gt;'			=> $_SESSION['ClientUser']['City']['TitleGen'],
		    	'&lt;%CITYGEN2%&gt;'		=> $_SESSION['ClientUser']['City']['TitleGen2'],
		    	'<%AVSUM'.SRO_BUILDERS_TYPE_ID.'%>'				=> $avsum[SRO_BUILDERS_TYPE_ID],
		    	'&lt;%AVSUM'.SRO_BUILDERS_TYPE_ID.'%&gt;'		=> $avsum[SRO_BUILDERS_TYPE_ID],
		    	'<%AVSUM'.SRO_PROJECTERS_TYPE_ID.'%>'			=> $avsum[SRO_PROJECTERS_TYPE_ID],
		    	'&lt;%AVSUM'.SRO_PROJECTERS_TYPE_ID.'%&gt;'		=> $avsum[SRO_PROJECTERS_TYPE_ID],
		    	'<%AVSUM'.SRO_PROSPECTORS_TYPE_ID.'%>'			=> $avsum[SRO_PROSPECTORS_TYPE_ID],
		    	'&lt;%AVSUM'.SRO_PROSPECTORS_TYPE_ID.'%&gt;'	=> $avsum[SRO_PROSPECTORS_TYPE_ID],
		    	'<%BREAD_CRUMBS%>'			=> '',
		    	'<%COPYRIGHT%>'				=> $this->settings->GetSetting('Copyright'),
		    	'<%ADDRESS%>'				=> $contacts['Address'],
		    	'<%SCHEDULE%>'				=> $contacts['Schedule'],
		    	'<%MAP%>'					=> $contacts['MapCode'],
		    	'<%MN_BOTTOM1_HEADER%>'		=> SetAtribs($menuBottomHeaderTemplate, $menuBottomHeader[SRO_ITEM_ID]),
		    	'<%MN_BOTTOM2_HEADER%>'		=> SetAtribs($menuBottomHeaderTemplate, $menuBottomHeader[CERTS_ITEM_ID]),
		    	'<%MN_BOTTOM3_HEADER%>'		=> SetAtribs($menuBottomHeaderTemplate, $menuBottomHeader[SERVICES_ITEM_ID]),
		    	'<%MN_BOTTOM1%>'			=> $menuBottom1->GetMenu(),
		    	'<%MN_BOTTOM2%>'			=> $menuBottom2->GetMenu(),
		    	'<%MN_BOTTOM3%>'			=> $menuBottom3->GetMenu(),
		    	'<%LAW1%>'					=> $law['agreement'],
		    	'<%LAW2%>'					=> $law['policy'],
		    	'<%MODALS%>'				=> $this->GetModals(),
		    	'<%CONSULTANT%>'			=> $this->settings->GetSetting('ConsultantCode'),
		    	'<%ANALYTICS%>'				=> $this->settings->GetSetting('AnalyticsCode'),
		    	'<%BLOCK1%>'				=> '',
		    	'<%BLOCK2%>'				=> '',
		    	'<%BLOCK3%>'				=> '',
		    	'<%BLOCK4%>'				=> '',
		    	'<%BLOCK5%>'				=> '',
		    	'<%BLOCK6%>'				=> '',
		    	'<%BLOCK7%>'				=> '',
		    	'<%BLOCK8%>'				=> '',
		    	'<%BLOCK9%>'				=> '',
		    	'<%BLOCK10%>'				=> '',
		    	'<%BLOCK11%>'				=> '',
		    	'<%BLOCK12%>'				=> '',
		    	'<%BLOCK13%>'				=> '',
		    	'<%BLOCK14%>'				=> '',
		    	'<%BLOCK15%>'				=> '',
		    	'<%BLOCK16%>'				=> '',
		    	'<%BLOCK17%>'				=> '',
		    	'<%BLOCK18%>'				=> '',
		    	'<%BLOCK19%>'				=> '',
		    	'<%BLOCK20%>'				=> '',
			));

			return $this->SetLinks($result);
		}	

	}
	
?>