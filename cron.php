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

  // GENERATE SITEMAP
  $sitemap = krnLoadModuleByName('sitemap');
  $sitemap->Generate();
    
  echo 'STATUS OK';

?>