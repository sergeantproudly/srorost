<?php

	class Log{
		
		private $fp;
		private $filename='error.log';
		private $text;
		private $use_datetime=true;
		
		public function __construct($params=array()){
			if(count($params))$this->set($params);
			$this->fp=fopen($this->filename,'a');
		}
		
		public function __destruct(){
			fclose($this->fp);
		}
		
		public function set($params=array()){
			if($params && count($params)){
				foreach($params as $k=>$v){
					$k{0}=strtolower($k{0});
					if(isset($this->$k)){
						$this->$k=$v;
					}
				}
			}
		}
		
		public function put($text){
			$this->text=$text;
			return fwrite($this->fp,($this->use_datetime?date('d.m.Y H:i:s').'   ':'') . $this->text . PHP_EOL);
		}
		
		public function info(){
			return $this->text;
		}
		
	}
	
	$GLOBALS['Log']=new Log();

?>