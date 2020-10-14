<?php

krnLoadLib('settings');
krnLoadLib('define');

class Modal {

	protected $db;
	protected $settings;
	
	protected $code;
	protected $params = [];

	protected $template = '';
	protected $result = false;
	
	public function __construct($code, $params = []) {
		global $Params;
		global $Settings;
		$this->db = $Params['Db']['Link'];
		$this->settings = $Settings;

		$this->code = $code;
		if (!empty($params)) $this->params = $params;
		else $this->params = $_POST;
		
		$this->template = SetAtribs(LoadTemplate('modal_base'), [
			'Id'		=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'Code'		=> $this->code,
			'Content' 	=> LoadTemplate('modal_'.$this->code)
		]);
		$func = 'Modal';
		$r = explode('_', $this->code);
		foreach ($r as $k) {
			$k{0} = strtoupper($k{0});
			$func .= $k;
		}
		if (method_exists($this, $func)) $this->result = $this->$func();		
	}
	
	public function GetModal() {
		return $this->result;
	}

	public function ModalGeo() {
		$element = LoadTemplate('modal_geo_el');
		$content='';

		$query = 'SELECT Id, Title, Code FROM geo_city ORDER BY IF(`Order`,-2000/`Order`,0) ASC, Popularity DESC, Title ASC LIMIT 0, ?i';
		$cities = $this->db->getAll($query, CITIES_MODAL_COUNT);
		foreach ($cities as $city) {
			$content .= strtr($element, array(
				'<%CLASS%>'	=> $city['Id'] == $_SESSION['ClientUser']['City']['Id'] ? ' class="active"' : '',
				'<%TITLE%>'	=> $city['Title'],
				'<%ID%>'	=> $city['Id'],
				'<%CODE%>'	=> $city['Id'] != CITY_MOSCOW_ID ? $city['Code'] . '/' : '',
			));
		}

		return SetContent($this->template, $content);
	}

	public function ModalFeedback() {
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s', 'modal-feedback');
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
		));
		return $template;
	}

	public function ModalCallback() {
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s', 'modal-callback');
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
		));
		return $template;
	}

	public function ModalConsultation() {
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s', 'modal-consultation');
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
		));
		return $template;
	}

	public function ModalDone() {
		return $this->template;
	}

	public function ModalWhatcost() {
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s', 'modal-whatcost');
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
		));
		return $template;
	}

	public function ModalSro() {
		$form = $this->db->getRow('SELECT * FROM `forms` WHERE Code = ?s', 'modal-sro');
		
		$template = strtr($this->template, array(
			'<%TITLE%>'			=> $form['Title'],
			'<%TEXT%>'			=> $form['Text'],
			'<%ACTION%>'		=> $this->params['Action'],
			'<%ID%>'			=> $this->params['Id'] ? $this->params['Id'] : $this->code,
			'<%CODE%>'			=> $this->code,
			'<%COMPANYID%>'		=> $this->params['CompanyId'],
		));
		return $template;
	}
	
}

?>