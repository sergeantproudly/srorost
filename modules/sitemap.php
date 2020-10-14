<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('common');

class sitemap extends krn_abstract{

	private $pages = array();
	private $siteUrl;
	
	public function __construct() {
		parent::__construct();

		global $Settings;
		global $Config;
		$this->siteUrl = $Settings->GetSetting('SiteUrl', $Config['Site']['Url']);
		if (function_exists('idn_to_ascii')) $this->siteUrl = punycode_encode($this->siteUrl);
	}
	
	public function GetResult() {
		$this->Generate();
		exit;
	}
	
	protected function GetPages() {
		$time = time();
		
		//statics
		$items = $this->db->getAll('SELECT Code, LastModTime FROM static_pages ORDER BY IF(`Order`,-1000/`Order`,0)');
		foreach ($items as $item){
			if ($item['Code'] != 'services' && $item['Code'] != 'news' && $item['Code'] != 'stati' && $item['Code'] != 'zakonodatelstvo') {
				if ($item['Code'] == 'main') {
					$pages[''] = $item['LastModTime'];

				} else {
					$pages[$item['Code']] = $item['LastModTime'];
				}
			}			
		}

		// services
		$items = $this->db->getAll('SELECT s.Code, c.Code AS CategoryCode, s.SeoShortLink, s.LastModTime FROM services s LEFT JOIN services_categories c ON s.CategoryId = c.Id ORDER BY s.CategoryId, IF(s.`Order`,-1000/s.`Order`,0)');
		foreach ($items as $item) {
			if (!$item['SeoShortLink']) {
				unset($pages[$item['Code']]);
				$pages[$item['CategoryCode'] . '/' . $item['Code']] = $item['LastModTime'];
			} else {
				$pages[$item['Code']] = $item['LastModTime'];
			}			
		}

		// news
		$items = $this->db->getAll('SELECT Code, LastModTime FROM news ORDER BY Date DESC');
		foreach ($items as $item) {
			$pages['news/' . $item['Code']] = $item['LastModTime'];
		}
		
		// statues
		$items = $this->db->getAll('SELECT Code, LastModTime FROM statues ORDER BY Date DESC');
		foreach ($items as $item) {
			$pages['stati/' . $item['Code']] = $item['LastModTime'];
		}
		
		// law
		$tree['zakonodatelstvo'] = $static_pages['zakonodatelstvo'];
		$items = $this->db->getAll('SELECT Code, LastModTime FROM laws ORDER BY IF(`Order`, -1000/`Order`, 0)');
		foreach ($items as $item) {
			$pages['zakonodatelstvo/' . $item['Code']] = $item['LastModTime'];
		}
		return $pages;
	}
	
	protected function GetSitemap($cityCode) {		
		$xml = new DomDocument('1.0', 'utf8');
		
		$urlset = $xml->appendChild($xml->createElement('urlset'));
		$urlset->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
		$urlset->setAttribute('xmlns','http://www.sitemaps.org/schemas/sitemap/0.9');
		$urlset->setAttribute('xsi:schemaLocation','http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd');
		
		foreach ($this->pages as $page => $lastmodtime) {
			$url = $urlset->appendChild($xml->createElement('url'));
			$loc = $url->appendChild($xml->createElement('loc'));
			$loc->appendChild($xml->createTextNode($this->siteUrl . (substr($this->siteUrl, -1) != '/' ? '/' : '') . ($cityCode ? $cityCode . '/' : '') . ($page ? $page . '/' : '')));
			$lastmod = $url->appendChild($xml->createElement('lastmod'));
			$lastmod->appendChild($xml->createTextNode(date('c', $lastmodtime ? $lastmodtime : time())));
			$changefreq = $url->appendChild($xml->createElement('changefreq'));
			$changefreq->appendChild($xml->createTextNode('daily'));
			$priority = $url->appendChild($xml->createElement('priority'));
			$priority->appendChild($xml->createTextNode($page ? '0.9' : '1.0'));
		}
		
		$xml->formatOutput = true;
		$xml->save('sitemaps/' . ($cityCode ? 'sitemap-' . $cityCode : 'sitemap2') . '.xml');
	}

	protected function GetSitemaps() {
		$cities = $this->db->getAll('SELECT Id, Code FROM geo_city ORDER BY IF(`Order`, -2000/`Order`, 0)');
		foreach ($cities as $city) {
			$this->GetSitemap($city['Id'] != CITY_MOSCOW_ID ? $city['Code'] : '');
		}

		$xml = new DomDocument('1.0', 'utf8');
		
		$sitemapindex = $xml->appendChild($xml->createElement('sitemapindex'));
		$sitemapindex->setAttribute('xmlns:xsi','http://www.w3.org/2001/XMLSchema-instance');
		$sitemapindex->setAttribute('xmlns','http://www.sitemaps.org/schemas/sitemap/0.9');
		$sitemapindex->setAttribute('xsi:schemaLocation','http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/siteindex.xsd');
		
		foreach ($cities as $city) {
			$sitemap = $sitemapindex->appendChild($xml->createElement('sitemap'));
			$loc = $sitemap->appendChild($xml->createElement('loc'));
			$loc->appendChild($xml->createTextNode($this->siteUrl . (substr($this->siteUrl, -1) != '/' ? '/' : '') . ($city['Id'] != CITY_MOSCOW_ID ? 'sitemap-' . $city['Code'] : 'sitemap2') . '.xml'));
			$lastmod = $sitemap->appendChild($xml->createElement('lastmod'));
			$lastmod->appendChild($xml->createTextNode(date('c', $lastmodtime ? $lastmodtime : time())));
		}

		$xml->formatOutput = true;
		$xml->save('sitemap.xml');
	}
	
	public function Generate(){
		$this->pages = $this->GetPages();
		$this->GetSitemaps();
	}
	
}

?>