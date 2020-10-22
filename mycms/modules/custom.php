<?php

krnLoadLib('define');
krnLoadLib('settings');
krnLoadLib('images');
krnLoadLib('files');
krnLoadLib('common');

class custom extends krn_abstract {

	const TEMPLATE_SETTING_ID = 1;
	const STEP_EXCLUSION_SETTING_ID = 3;
	
	public function __construct(){
		parent::__construct();
	}
	
	public function GetResult(){
	}

	public function BrowseCalcValue($rec) {
		$type = dbGetValueFromDb('SELECT `Type` FROM calc_settings WHERE Id = ' . $rec['SettingId'], __FILE__, __LINE__);
		if ($type != 'select') $result = $rec['Value'];
		else {
			if ($rec['SettingId'] == self::TEMPLATE_SETTING_ID) {
				$result = dbGetValueFromDb('SELECT Title FROM calculator_templates WHERE Id = ' . $rec['Value'], __FILE__, __LINE__);

			} elseif ($rec['SettingId'] == self::STEP_EXCLUSION_SETTING_ID) {
				$result = dbGetValueFromDb('SELECT Title FROM calculator_steps WHERE Id = ' . $rec['Value'], __FILE__, __LINE__);
			}
		}
		return $result;
	}

	public function ModifyCalcValue($rec, $name) {
		$type = dbGetValueFromDb('SELECT `Type` FROM calc_settings WHERE Id = ' . $rec['SettingId'], __FILE__, __LINE__);
		$result = LoadTemplate('inp_' . $type);
		switch ($type) {
			case 'text': 
				$result = strtr($result, array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $rec['Value'],
					'<%MAXLENGTH%>'		=> '',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'textarea': 
				$result = strtr($result, array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> $name,
					'<%VALUE%>'			=> $rec['Value'],
					'<%SIZE%>'			=> '',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'select': 
				$options = '';
				if ($rec['SettingId'] == self::TEMPLATE_SETTING_ID) {
					$res = dbDoQuery('SELECT Id, Title FROM calculator_templates ORDER BY IF(`Order`,-100/`Order`,0), Title', __FILE__, __LINE__);
					while($rec2 = dbGetRecord($res)) {
						if ($rec['Value'] == $rec2['Id']) {
							$default = [
								'Title' => $rec2['Title'],
								'Value' => $rec2['Id']
							];
							$options.='<span class="item current" value="'.$rec2['Id'].'">'.$rec2['Title'].'</span>';
						} else {
							$options.='<span class="item" value="'.$rec2['Id'].'">'.$rec2['Title'].'</span>';
						}
					}

				} elseif ($rec['SettingId'] == self::STEP_EXCLUSION_SETTING_ID) {
					$res = dbDoQuery('SELECT Id, Title FROM calculator_steps ORDER BY IF(`Order`,-100/`Order`,0), Title', __FILE__, __LINE__);
					while($rec2 = dbGetRecord($res)) {
						if ($rec['Value'] == $rec2['Id']) {
							$default = [
								'Title' => $rec2['Title'],
								'Value' => $rec2['Id']
							];
							$options.='<span class="item current" value="'.$rec2['Id'].'">'.$rec2['Title'].'</span>';
						} else {
							$options.='<span class="item" value="'.$rec2['Id'].'">'.$rec2['Title'].'</span>';
						}
					}
				}

				$result = strtr($result, array(
					'<%IDNUM%>'			=> '',
					'<%CALLBACK%>'		=> '',
					'<%NAME%>'			=> $name,
					'<%DEFAULT_TITLE%>'	=> $default['Title'],
					'<%DEFAULT_VALUE%>'	=> $default['Value'],
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
		}
		return $result;
	}
	
	public function GetCalcValueBySettingId() {
		$settingId = $_POST['setting_id'];
		$pageId = $_POST['page_id'];

		$type = dbGetValueFromDb('SELECT `Type` FROM calc_settings WHERE Id = ' . $settingId, __FILE__, __LINE__);

		$result = LoadTemplate('inp_' . $type);
		switch ($type) {
			case 'text': 
				$result = strtr($result, array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> '',
					'<%VALUE%>'			=> '',
					'<%MAXLENGTH%>'		=> '',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'textarea': 
				$result = strtr($result, array(
					'<%IDNUM%>'			=> '',
					'<%NAME%>'			=> '',
					'<%VALUE%>'			=> '',
					'<%SIZE%>'			=> '',
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
			case 'select': 
				$options = '';
				if ($settingId == self::TEMPLATE_SETTING_ID) {
					$res = dbDoQuery('SELECT Id, Title FROM calculator_templates ORDER BY IF(`Order`,-100/`Order`,0), Title', __FILE__, __LINE__);
					while($rec = dbGetRecord($res)) {
						if (!isset($default)) {
							$default = [
								'Title' => $rec['Title'],
								'Value' => $rec['Id']
							];
							$options.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
						} else {
							$options.='<span class="item" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
						}
					}

				} elseif ($settingId == self::STEP_EXCLUSION_SETTING_ID) {
					$templateId = dbGetValueFromDb('SELECT `Value` FROM pages_calc_settings WHERE PageId = '. $pageId . ' AND SettingId = ' . self::TEMPLATE_SETTING_ID, __FILE__, __LINE__);
					$res = dbDoQuery('SELECT Id, Title FROM calculator_steps WHERE TemplateId = ' . ($templateId ? $templateId : 0) . ' ORDER BY IF(`Order`,-100/`Order`,0)', __FILE__, __LINE__);
					while($rec = dbGetRecord($res)) {
						if (!isset($default)) {
							$default = [
								'Title' => $rec['Title'],
								'Value' => $rec['Id']
							];
							$options.='<span class="item current" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
						} else {
							$options.='<span class="item" value="'.$rec['Id'].'">'.$rec['Title'].'</span>';
						}
					}
				}

				$result = strtr($result, array(
					'<%IDNUM%>'			=> '',
					'<%CALLBACK%>'		=> '',
					'<%NAME%>'			=> '',
					'<%DEFAULT_TITLE%>'	=> $default['Title'],
					'<%DEFAULT_VALUE%>'	=> $default['Value'],
					'<%OPTIONS%>'		=> $options,
					'<%ATTRIBUTES%>'	=> ''
				));
			break;
		}
		return $result;
	}

	public function DeleteTemplateSettings($id) {
		dbDoQuery('DELETE FROM pages_calc_settings WHERE SettingId = ' . self::TEMPLATE_SETTING_ID . ' AND `Value` = "' . $id . '"', __FILE__, __LINE__);
	}

	public function DeleteStepExclusionSettings($id) {
		dbDoQuery('DELETE FROM pages_calc_settings WHERE SettingId = ' . self::STEP_EXCLUSION_SETTING_ID . ' AND `Value` = "' . $id . '"', __FILE__, __LINE__);
	}

	public function BrowseStepVariantId($rec) {
		$variant = dbGetRecordFromDb('SELECT cv.Title, cs.Title AS StepTitle FROM calculator_step_variants cv LEFT JOIN calculator_steps cs ON cv.StepId = cs.Id WHERE cv.Id = ' . $rec['VariantId'],__FILE__,__LINE__);
		$result = $variant ? ($variant['StepTitle'] . ' : ' . $variant['Title']) : '';
		return $result;
	}

	public function ModifyStepVariantId($rec, $name) {
		$res = dbDoQuery('SELECT cv.Id, cv.Title, cs.Title AS StepTitle FROM calculator_step_variants cv LEFT JOIN calculator_steps cs ON cv.StepId = cs.Id WHERE cs.TemplateId = ' . $rec['TemplateId'] . ' ORDER BY IF(cs.`Order`, -100/cs.`Order`, 0), IF(cv.`Order`, -100/cv.`Order`, 0)', __FILE__, __LINE__);
		while($rec2 = dbGetRecord($res)) {
			if ($rec['VariantId'] == $rec2['Id']) {
				$default = [
					'Title' => $rec2['StepTitle'] . ' : ' . $rec2['Title'],
					'Value' => $rec2['Id']
				];
				$options.='<span class="item current" value="'.$rec2['Id'].'">'.$rec2['StepTitle'] . ' : ' . $rec2['Title'].'</span>';
			} else {
				$options.='<span class="item" value="'.$rec2['Id'].'">'.$rec2['StepTitle'] . ' : ' . $rec2['Title'].'</span>';
			}
		}
		if (!$default) {
			$default = [
				'Title' => '',
				'Value' => 0
			];
			$options = '<span class="item current" value="0">&nbsp;</span>' . $options;
		} else {
			$options = '<span class="item" value="0">&nbsp;</span>' . $options;
		}

		$result = strtr(LoadTemplate('inp_select'), array(
			'<%IDNUM%>'			=> '',
			'<%CALLBACK%>'		=> '',
			'<%NAME%>'			=> $name,
			'<%DEFAULT_TITLE%>'	=> $default['Title'],
			'<%DEFAULT_VALUE%>'	=> $default['Value'],
			'<%OPTIONS%>'		=> $options,
			'<%ATTRIBUTES%>'	=> ''
		));
		return $result;
	}
	
}

?>