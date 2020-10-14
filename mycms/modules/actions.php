<?php

class actions extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
	}
	
	/** System */
	function SystemMultiSelect($params){
		$storageTable=$params['storageTable'];
		$storageSelfField=$params['storageSelfField'];
		$storageField=$params['storageField'];
		$selfValue=$params['selfValue'];
		dbDoQuery('DELETE FROM `'.$storageTable.'` WHERE `'.$storageSelfField.'`="'.$selfValue.'"',__FILE__,__LINE__);
		if(isset($params['values'])){
			foreach($params['values'] as $value){
				dbDoQuery('INSERT INTO `'.$storageTable.'` SET `'.$storageSelfField.'`="'.$selfValue.'", `'.$storageField.'`="'.$value.'"',__FILE__,__LINE__);
			}
		}
	}

	/** Service Category */
	function OnAddServiceCategory($newRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE services_categories SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}
	
	function OnEditServiceCategory($newRecord,$oldRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE services_categories SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}

	function OnDeleteServiceCategory($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM services_categories WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}

	/** Service */
	function OnAddService($newRecord) {
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE services SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}
	
	function OnEditService($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE services SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'services');
		}
	}

	function OnDeleteService($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM services WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}
	
	/** New */
	function OnAddNew($newRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code, array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE news SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'news');
		}
	}
	
	function OnEditNew($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE news SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'news');
		}
	}

	function OnDeleteNew($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM news WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}
	
	/** Statue */
	function OnAddStatue($newRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE statues SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'statues');
		}
	}
	
	function OnEditStatue($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE statues SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'statues');
		}
	}

	function OnDeleteStatue($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM statues WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}

	/** Laws */
	function OnAddLaw($newRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE laws SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'law');
		}
	}
	
	function OnEditLaw($newRecord,$oldRecord){
		if (!$newRecord['Code']) {
			krnLoadLib('chars');
			$code = mb_strtolower(chrTranslit($newRecord['Title']));
			$code = strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE laws SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		} else {
			$code = $newRecord['Code'];
		}

		if ($code) {
			krnLoadLib('routing');
			Routing::SetRouting($code, 'law');
		}
	}

	function OnDeleteLaw($oldRecord) {
		$code = dbGetValueFromDb('SELECT Code FROM laws WHERE Id='.$oldRecord['Id'],__FILE__,__LINE__);

		if ($code) {
			krnLoadLib('routing');
			Routing::DeleteRouting($code);
		}
	}
	
	/** City */
	function OnAddCity($newRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE cities SET `Code`="'.$code.'" WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}
	}
	
	function OnEditCity($newRecord,$oldRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE cities SET `Code`="'.$code.'" WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}
	}
	
	/** Static pages */
	function OnAddStaticPage($newRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}		
	}
	
	function OnEditStaticPage($newRecord,$oldRecord){
		if(!$newRecord['Code']){
			krnLoadLib('chars');
			$code=mb_strtolower(chrTranslit($newRecord['Title']));
			$code=strtr($code,array(','=>'',' '=>'_','*'=>'','!'=>'','?'=>'','@'=>'','#'=>'','$'=>'','%'=>'','^'=>'','('=>'',')'=>'','+'=>'','-'=>'_','«'=>'','»'=>'','—'=>'',':'=>'',';'=>'','ь'=>''));
			dbDoQuery('UPDATE static_pages SET `Code`="'.$code.'", LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}else{
			dbDoQuery('UPDATE static_pages SET LastModTime='.time().' WHERE Id='.$newRecord['Id'],__FILE__,__LINE__);
		}	
	}

	/** Files */
	function OnAddFile($newRecord){
		krnLoadSiteLib('define');
		if ($newRecord['Code'] == IMPORT_SRO_FILE_CODE)	{
			krnLoadSiteLib('sro');
			new Sro($newRecord['File']);
		}
	}

	function OnEditFile($newRecord){
		krnLoadSiteLib('define');
		if ($newRecord['Code'] == IMPORT_SRO_FILE_CODE)	{
			krnLoadSiteLib('sro');
			new Sro($newRecord['File']);
		}
	}
}

?>