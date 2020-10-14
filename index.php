<?php

	mb_internal_encoding('utf8');
	include_once 'settings.php';
	include_once LIBRARY_DIR.'common.lib.php';
    include_once LIBRARY_DIR.'dbmysqli.lib.php';
    include_once LIBRARY_DIR.'kernel.lib.php';
    include_once LIBRARY_DIR.'site.lib.php';
	
	if (function_exists('date_default_timezone_set')) date_default_timezone_set('Europe/Moscow');	
	session_start();	
   	
   	krnLoadLib('preactions');
	
    $Params['Site']['Page']['Code'] = krnGetPageCode();
    if (file_exists($Params['Site']['Page']['Code'])) die(readfile($Params['Site']['Page']['Code']));
    $Params['Site']['Page']['Module'] = krnGetPageModule();
    $krnModule = krnLoadModule();

	if ($actionName = $_GET['act'] ? $_GET['act'] : $_POST['act']){
		if (method_exists($krnModule, $actionName)) {
			echo $krnModule->$actionName();
		} else {
			echo 'Method '.$actionName.' not found';
		}
	}else{
		header('Content-type: text/html; charset=utf-8');
		echo $Site->GetPage();
	}

?>