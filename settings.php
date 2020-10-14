<?php

   $Config['Db']['Host']   = 'localhost';
   $Config['Db']['Login']  = 'root';
   $Config['Db']['Pswd']   = '2IPOhsMbwKU9$bt5s';
   $Config['Db']['DbName'] = 'srorost';  
   	
   $Config['Site']['Title']      = 'Получение допуска СРО: услуги «Консалтинговой группы «РОСТ» | Получить допуск СРО к работам в Москве';
   $Config['Site']['Email']      = 'sro.rost@gmail.com';
   $Config['Site']['Keywords']      = 'сро москва услуги допуск получить к работам получение'; 
   $Config['Site']['Description']   = '«Консалтинговая группа «РОСТ» оказывает профессиональные услуги в получении допуска СРО к работам на выгодных условиях. Наши телефоны в Москве +7 (800) 250-50-15, +7 (495) 229-94-06.';
   $Config['Site']['Url']        = 'https://сророст.рф';
   	
   $Config['Smtp']['Server']	= 'smtp.yandex.ru';
   $Config['Smtp']['Port']		= '465';
   $Config['Smtp']['Email']	= 'info@proudly.ru';
	$Config['Smtp']['Password']	= 'sergeantpepperr7';
	$Config['Smtp']['Secure']	= 'ssl';
   	
   error_reporting (E_ALL & ~E_NOTICE);

	// constants
   define ('TEMPLATE_DIR', 'templates/');
   define ('TOOL_DIR', 'tools/');
   define ('IMAGE_DIR', 'images/');
   define ('MISC_DIR', 'misc/');
   define ('MODULE_DIR', 'modules/');
   define ('LIBRARY_DIR', 'library/');
   define ('DOWNLOADS_DIR', 'downloads/');
   	
   define ('ABS_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
   define ('ROOT_PATH', $_SERVER['DOCUMENT_ROOT'].'/');
   define ('TEMP_DIR', 'mycms/uploads/temp/');

?>