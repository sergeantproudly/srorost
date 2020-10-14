<?php

krnLoadLib('mail');
krnLoadLib('settings');
krnLoadLib('define');

class ajax extends krn_abstract{

	function __construct($params=array()) {
		parent::__construct();
	}
	
	function GetResult() {
		if ($_POST['act'] && method_exists($this, $_POST['act'])) {
			echo $this->$_POST['act'];
		}
		exit;
	}	

	/** Модальное окно */
	function GetModal() {
		krnLoadLib('modal');
		$modalCode = $_POST['code'];
		$modal = new Modal($modalCode, $_POST['data']);
		return $modal->GetModal();
	}
	
	/** Загрузчик файлов */
	function GetUploader() {
		krnLoadLib('uploader');
		$uploaderCode = $_POST['code'];
		$func = 'Uploader';
		$r = explode('_',$uploaderCode);
		foreach ($r as $k) {
			$k{0} = strtoupper($k{0});
			$func .= $k;
		}
		if (function_exists($func)) return $func();
		return false;
	}

	/** Обратная связь */
	function Feedback() {
		$name = trim($_POST['name']);
		$tel = trim($_POST['tel']);
		$text = $_POST['text'];
		$code = $_POST['code'];

		$capcha = $_POST['capcha'];

		// проверка на спамбота
		// основывается на сверки user agent-ов
		if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
			if ($tel) {				
				$form = $this->db->getRow('SELECT Title, SuccessHeader, Success FROM forms WHERE Code=?s', $code);				
				$request = '';
				if ($name) $request .= "Имя: $name\r\n";
				if ($email) $request .= "E-mail: $email\r\n";
				if ($tel) $request .= "Телефон: $tel\r\n";
				if ($text) $request .= 'Текст:'."\r\n$text\r\n";
				$this->db->query('INSERT INTO requests SET DateTime=NOW(), Form=?s, Name=?s, Tel=?s, Text=?s, RefererPage=?s, IsSet=0',
					$form ? $form['Title'] : '',
				 	$name,
				 	$tel,
					str_replace('"', '\"', $request),
					$_SERVER['HTTP_REFERER']
				);
					
				global $Config;
				$siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
				$siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
				$adminTitle = 'Администратор';
				$adminEmail = $this->db->getOne('SELECT Email1 FROM contacts WHERE CityId=?i OR CityId=?i', $_SESSION['ClientUser']['City']['Id'], CITY_MOSCOW_ID);
					
				$letter['subject'] = $form['Title'].' с сайта "'.$siteTitle.'"';
				$letter['html'] = '<b>'.$form['Title'].'</b><br/><br/>';
				$letter['html'] .= str_replace("\r\n", '<br/>', $request);
				$mail = new Mail();
				$mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
											
				$json = array(
					'status' => true,
					'header' => $form['SuccessHeader'],
					'message' => $form['Success']
				);

			} else {
				$json = array(
					'status' => false,
					'message' => 'Серверная ошибка. При повторном возникновении, пожалуйста, обратитесь к администратору.'
				);
			}
		} else {
			$json = array(
				'status' => false,
				'message' => 'Сработал антиспам. При повторном возникновении, пожалуйста, обратитесь к администратору.'
			);
		}

		return json_encode($json);
	}

	/** Обратный звонок */
	function Callback() {
		$name = trim($_POST['name']);
		$tel = trim($_POST['tel']);
		$text = $_POST['text'];
		$code = $_POST['code'];

		$capcha = $_POST['capcha'];

		// проверка на спамбота
		// основывается на сверки user agent-ов
		if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
			if ($tel) {				
				$form = $this->db->getRow('SELECT Title, SuccessHeader, Success FROM forms WHERE Code=?s', $code);				
				$request = '';
				if ($name) $request .= "Имя: $name\r\n";
				if ($tel) $request .= "Телефон: $tel\r\n";
				$this->db->query('INSERT INTO requests SET DateTime=NOW(), Form=?s, Name=?s, Tel=?s, Text=?s, RefererPage=?s, IsSet=0',
					$form ? $form['Title'] : '',
				 	$name,
				 	$tel,
					str_replace('"', '\"', $request),
					$_SERVER['HTTP_REFERER']
				);
					
				global $Config;
				$siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
				$siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
				$adminTitle = 'Администратор';
				$adminEmail = $this->db->getOne('SELECT Email1 FROM contacts WHERE CityId=?i OR CityId=?i', $_SESSION['ClientUser']['City']['Id'], CITY_MOSCOW_ID);
					
				$letter['subject'] = $form['Title'].' с сайта "'.$siteTitle.'"';
				$letter['html'] = '<b>'.$form['Title'].'</b><br/><br/>';
				$letter['html'] .= str_replace("\r\n", '<br/>', $request);
				$mail = new Mail();
				$mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
											
				$json = array(
					'status' => true,
					'header' => $form['SuccessHeader'],
					'message' => $form['Success']
				);

			} else {
				$json = array(
					'status' => false,
					'message' => 'Серверная ошибка. При повторном возникновении, пожалуйста, обратитесь к администратору.'
				);
			}
		} else {
			$json = array(
				'status' => false,
				'message' => 'Сработал антиспам. При повторном возникновении, пожалуйста, обратитесь к администратору.'
			);
		}

		return json_encode($json);
	}

	/** Получить консультацию */
	function Consultation() {
		$name = trim($_POST['name']);
		$tel = trim($_POST['tel']);
		$code = $_POST['code'];

		$capcha = $_POST['capcha'];

		// проверка на спамбота
		// основывается на сверки user agent-ов
		if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
			if ($tel) {				
				$form = $this->db->getRow('SELECT Title, SuccessHeader, Success FROM forms WHERE Code=?s', $code);				
				$request = '';
				if ($name) $request .= "Имя: $name\r\n";
				if ($tel) $request .= "Телефон: $tel\r\n";
				$this->db->query('INSERT INTO requests SET DateTime=NOW(), Form=?s, Name=?s, Tel=?s, Text=?s, RefererPage=?s, IsSet=0',
					$form ? $form['Title'] : '',
				 	$name,
				 	$tel,
					str_replace('"', '\"', $request),
					$_SERVER['HTTP_REFERER']
				);
					
				global $Config;
				$siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
				$siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
				$adminTitle = 'Администратор';
				$adminEmail = $this->db->getOne('SELECT Email1 FROM contacts WHERE CityId=?i OR CityId=?i', $_SESSION['ClientUser']['City']['Id'], CITY_MOSCOW_ID);
					
				$letter['subject'] = $form['Title'].' с сайта "'.$siteTitle.'"';
				$letter['html'] = '<b>'.$form['Title'].'</b><br/><br/>';
				$letter['html'] .= str_replace("\r\n", '<br/>', $request);
				$mail = new Mail();
				$mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
											
				$json = array(
					'status' => true,
					'header' => $form['SuccessHeader'],
					'message' => $form['Success']
				);

			} else {
				$json = array(
					'status' => false,
					'message' => 'Серверная ошибка. При повторном возникновении, пожалуйста, обратитесь к администратору.'
				);
			}
		} else {
			$json = array(
				'status' => false,
				'message' => 'Сработал антиспам. При повторном возникновении, пожалуйста, обратитесь к администратору.'
			);
		}

		return json_encode($json);
	}

	/** Калькулятор */
	function Calculation() {
		$tel = trim($_POST['tel']);
		$code = $_POST['code'];
		$answers = $_POST['answers'];
		$totalSum = $_POST['totalSum'];
		$pageId = $_POST['pageId'];

		$capcha = $_POST['capcha'];

		// проверка на спамбота
		// основывается на сверки user agent-ов
		if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
			if ($tel) {				
				$form = $this->db->getRow('SELECT Title, SuccessHeader, Success FROM forms WHERE Code=?s', $code);				
				$request = '';
				if ($name) $request .= "Имя: $name\r\n";
				if ($tel) $request .= "Телефон: $tel\r\n";
				if ($answers) {
					$query = 'SELECT Id, Title, `Order` '
							.'FROM calculator_steps '
							.'WHERE PageId = ?s ';
					$steps = $this->db->getInd('Id', $query, $pageId);

					$query = 'SELECT Id, Title '
							.'FROM calculator_step_variants '
							.'WHERE StepId IN (?a) ';
					$variants = $this->db->getInd('Id', $query, array_keys($steps));

					$request .= "\r\n";
					$request .= "Ответы в калькуляторе:\r\n";

					$answers_text = [];
					foreach ($answers as $stepId => $variantId) {
						$answers_text[$steps[$stepId]['Order']] .= $steps[$stepId]['Title'] . ': ' . $variants[$variantId]['Title'] . "\r\n";
					}
					ksort($answers_text);
					$request .= implode('', $answers_text);
					$request .= "\r\n";
				}
				if ($totalSum) {
					$request .= "Итоговая рассчитанная сумма: " . number_format($totalSum, 0, '', ' ') . "\r\n";
				}
				$this->db->query('INSERT INTO requests SET DateTime=NOW(), Form=?s, Tel=?s, Text=?s, RefererPage=?s, IsSet=0',
					$form['Title'],
				 	$tel,
					str_replace('"', '\"', $request),
					$_SERVER['HTTP_REFERER']
				);
					
				global $Config;
				$siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
				$siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
				$adminTitle = 'Администратор';
				$adminEmail = $this->db->getOne('SELECT Email1 FROM contacts WHERE CityId=?i OR CityId=?i', $_SESSION['ClientUser']['City']['Id'], CITY_MOSCOW_ID);
					
				$letter['subject'] = $form['Title'].' с сайта "'.$siteTitle.'"';
				$letter['html'] = '<b>'.$form['Title'].'</b><br/><br/>';
				$letter['html'] .= str_replace("\r\n", '<br/>', $request);
				$mail = new Mail();
				$mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
											
				$json = array(
					'status' => true,
					'header' => $form['SuccessHeader'],
					'message' => $form['Success']
				);

			} else {
				$json = array(
					'status' => false,
					'message' => 'Серверная ошибка. При повторном возникновении, пожалуйста, обратитесь к администратору.'
				);
			}
		} else {
			$json = array(
				'status' => false,
				'message' => 'Сработал антиспам. При повторном возникновении, пожалуйста, обратитесь к администратору.'
			);
		}

		return json_encode($json);
	}

	/** Вступить в СРО */
	function Sro() {
		$name = trim($_POST['name']);
		$tel = trim($_POST['tel']);
		$text = $_POST['text'];
		$companyId = $_POST['company_id'];
		$code = $_POST['code'];

		$capcha = $_POST['capcha'];

		// проверка на спамбота
		// основывается на сверки user agent-ов
		if ($capcha == $_SERVER['HTTP_USER_AGENT']) {
			if ($tel) {				
				$company = $this->db->getRow('SELECT Title FROM sro_companies WHERE Id = ?i', $companyId);

				$form = $this->db->getRow('SELECT Title, SuccessHeader, Success FROM forms WHERE Code=?s', $code);				
				$request = '';
				if ($name) $request .= "Имя: $name\r\n";
				if ($tel) $request .= "Телефон: $tel\r\n";
				if ($company) $request .= "Компания СРО: " . $company['Title'] . "\r\n";
				$this->db->query('INSERT INTO requests SET DateTime=NOW(), Form=?s, Name=?s, Tel=?s, Text=?s, RefererPage=?s, IsSet=0',
					$form['Title'],
				 	$name,
				 	$tel,
					str_replace('"', '\"', $request),
					$_SERVER['HTTP_REFERER']
				);
					
				global $Config;
				$siteTitle = strtr(stGetSetting('SiteEmailTitle', $Config['Site']['Title']), array('«'=>'"','»'=>'"','—'=>'-'));
				$siteEmail = stGetSetting('SiteEmail', $Config['Site']['Email']);
				$adminTitle = 'Администратор';
				$adminEmail = $this->db->getOne('SELECT Email1 FROM contacts WHERE CityId=?i OR CityId=?i', $_SESSION['ClientUser']['City']['Id'], CITY_MOSCOW_ID);
					
				$letter['subject'] = $form['Title'].' с сайта "'.$siteTitle.'"';
				$letter['html'] = '<b>'.$form['Title'].'</b><br/><br/>';
				$letter['html'] .= str_replace("\r\n", '<br/>', $request);
				$mail = new Mail();
				$mail->SendMailFromSite($adminEmail, $letter['subject'], $letter['html']);
											
				$json = array(
					'status' => true,
					'header' => $form['SuccessHeader'],
					'message' => $form['Success']
				);

			} else {
				$json = array(
					'status' => false,
					'message' => 'Серверная ошибка. При повторном возникновении, пожалуйста, обратитесь к администратору.'
				);
			}
		} else {
			$json = array(
				'status' => false,
				'message' => 'Сработал антиспам. При повторном возникновении, пожалуйста, обратитесь к администратору.'
			);
		}

		return json_encode($json);
	}

	/** Города - Фильтр по ключевому слову */
	function FilterCitiesByKeyword() {
		$keyword = trim($_POST['keyword']);
		
		$element = LoadTemplate('modal_geo_el');
		$content='';
		
		$query = 'SELECT Id, Title, Code FROM geo_city WHERE Title LIKE ?s ORDER BY IF(`Order`,-2000/`Order`,0) ASC, Popularity DESC, Title ASC LIMIT 0, ?i';
		$cities = $this->db->getAll($query, $keyword . '%', CITIES_MODAL_COUNT);
		foreach ($cities as $city) {
			$content .= strtr($element, array(
				'<%CLASS%>'	=> $city['Id'] == $_SESSION['ClientUser']['City']['Id'] ? ' class="active"' : '',
				'<%TITLE%>'	=> $city['Title'],
				'<%CODE%>'	=> $city['Code'],
				'<%ID%>'	=> $city['Id'],
			));
		}
		
		return $content;
	}

	/** Города - Установить город */
	function SetCity(){
		$city_id = (int)$_POST['city_id'];
		
		if ($city = $this->db->getRow('SELECT * FROM geo_city WHERE Id = ?i', $city_id)) {
			$this->db->query('UPDATE geo_city SET Popularity = Popularity + 1 WHERE Id = ?i', $city_id);

			$url = parse_url($_SERVER['HTTP_REFERER']);
			$path = str_replace($_SESSION['ClientUser']['City']['Code'] . '/', '', $url['path']);

			$_SESSION['ClientUser']['City'] = $city;
			$json = array(
				'status'	=> true,
				'path'		=> $path,
			);
		} else {
			$json = array(
				'status'	=> false,
				'error'		=> 'Не удалось установить город'
			);
		}
		return json_encode($json);
	}

}

?>