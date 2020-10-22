<?php

	define ('SSH_PATH','');
	define ('TEMPLATE_DIR', SSH_PATH.'templates/');
  define ('TOOL_DIR', SSH_PATH.'tools/');
  define ('IMAGE_DIR', SSH_PATH.'images/');
  define ('MISC_DIR', SSH_PATH.'misc/');
  define ('MODULE_DIR', SSH_PATH.'modules/');
  define ('LIBRARY_DIR', SSH_PATH.'library/');
  define ('DOWNLOADS_DIR', SSH_PATH.'downloads/');

  mb_internal_encoding('utf8');
	require_once SSH_PATH.'settings.php';
	require_once SSH_PATH.LIBRARY_DIR.'common.lib.php';
  require_once SSH_PATH.LIBRARY_DIR.'dbmysqli.lib.php';
  require_once SSH_PATH.LIBRARY_DIR.'kernel.lib.php';
  require_once SSH_PATH.LIBRARY_DIR.'site.lib.php';
    
  if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');	
	session_start();

  // LIBS
  krnLoadLib('preactions');
  krnLoadLib('settings');
  krnLoadLib('mail');

  // CALCULATOR STEPS FROM PAGES TO TEMPALES
  class updater extends krn_abstract {
        public function __construct() {
            parent::__construct();
        }

        public function GetResult() {}

        public function Update() {
            $templates = [];
            $steps = $this->db->getAll('SELECT cs.*, p.Title AS PageTitle FROM calculator_steps cs LEFT JOIN static_pages p ON cs.PageId = p.Id ORDER BY IF(cs.`Order`,-1000/cs.`Order`,0)');
            foreach ($steps as $step) {
                if (!($templateId = array_search($step['PageTitle'], $templates))) {
                    $this->db->query('INSERT INTO calculator_templates SET Title = ?s', $step['PageTitle']);
                    $templateId = $this->db->insertId();
                    $templates[$templateId] = $step['PageTitle'];
                }
                $this->db->query('UPDATE calculator_steps SET TemplateId = ?i WHERE Id = ?i', $templateId, $step['Id']);
            }

            echo 'STATUS OK';
        }
  }

  $Updater = new updater();
  $Updater->Update();

?>