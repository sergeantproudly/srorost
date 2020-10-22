<?php

	krnLoadLib('xlsx');
	krnLoadLib('define');
	
	class Calculator {
		
		protected $db;
		protected $commonSettings;

		private $pageId;
		private $settings = [];
		
		public function __construct($pageId) {
			global $Params;
			global $Settings;
			$this->db = $Params['Db']['Link'];
			$this->commonSettings = $Settings;

			$this->pageId = $pageId;

			$this->LoadSettings();
		}

		public function LoadSettings() {
			$settings = $this->db->query('SELECT SettingId, `Value` FROM pages_calc_settings WHERE PageId = ?i', $this->pageId);
			foreach ($settings as $setting) {
				if ($setting['SettingId'] != CALC_STEP_EXCLUSION_SETTING_ID || !isset($this->settings[$setting['SettingId']])) {
					$this->settings[$setting['SettingId']] = $setting['Value'];
				} else {
					$this->settings[$setting['SettingId']] .= ',' . $setting['Value'];
				}
			}
			return $this->settings;
		}

		public function GetSettingById($settingId) {
			return $this->settings[$settingId];
		}

		public function GetTemplateId() {
			return $this->GetSettingById(CALC_TEMPLATE_SETTING_ID);	
		}

		public function GetBaseSumId() {
			return $this->GetSettingById(CALC_BASESUM_SETTING_ID);	
		}

		public function GetStepExclusionIds() {
			$setting = $this->GetSettingById(CALC_STEP_EXCLUSION_SETTING_ID);
			return $setting ? explode(',', $setting) : [];	
		}

		public function GetSroTypeIdByTemplateId($templateId) {
			$sroType = $this->db->getOne('SELECT SroTypeId FROM calculator_templates WHERE Id = ?i', $templateId);
			return $sroType;
		}

		public function GetSroTypeId() {
			return $this->GetSroTypeIdByTemplateId($this->GetTemplateId());
		}
		
	}

?>