<?php

krnLoadLib('define');

class main extends krn_abstract{
	
	function __construct(){
		parent::__construct();
	}
	
	function GetResult(){
		$result=$this->GetContent();
		return $result;
	}
	
	function GetContent(){
		$records=krnLoadModuleByName('records2');
		$documents='<div class="inner-wrapper">'.$records->BrowseDocuments().'</div>';
		
		if($_SESSION['User']['Status']!=PERMISSION_MASK_MODERATOR){
			$records=krnLoadModuleByName('records');
			$documents.='<div class="inner-wrapper">'.$records->BrowseDocuments().'</div>';
		}
		
		//return $this->GetStatistics();		
		return '';
	}
	
	function GetStatistics(){
		$stats['CompaniesTotal']=dbGetValueFromDb('SELECT COUNT(Id) FROM requests',__FILE__,__LINE__);
		$stats['UsersTotal']=dbGetValueFromDb('SELECT COUNT(Id) FROM users WHERE Confirmed=1',__FILE__,__LINE__);
		$stats['InvoicesCount']=dbGetValueFromDb('SELECT COUNT(Id) FROM invoices WHERE Paid=1',__FILE__,__LINE__);	
		$stats['InvoicesSum']=dbGetValueFromDb('SELECT SUM(`Sum`) FROM invoices WHERE Paid=1',__FILE__,__LINE__);		
		
		$result=LoadTemplate('main');
		$result=strtr($result,array(
			'<%DOCUMENTS%>'	=> $documents,
			'<%VALUE1%>'	=> $stats['CompaniesTotal'],
			'<%UNIT1%>'		=> '',
			'<%TITLE1%>'	=> 'Страховых компаний',
			'<%VALUE2%>'	=> $stats['UsersTotal'],
			'<%UNIT2%>'		=> '',
			'<%TITLE2%>'	=> 'Пользователей',
			'<%VALUE3%>'	=> $stats['InvoicesCount'],
			'<%UNIT3%>'		=> '',
			'<%TITLE3%>'	=> 'Заказов продуктов',
			'<%VALUE4%>'	=> $stats['InvoicesSum'],
			'<%UNIT4%>'		=> ' Р',
			'<%TITLE4%>'	=> 'На общую сумму',
		));
		return $result;
	}
	
}

?>