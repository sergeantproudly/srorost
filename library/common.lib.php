<?php

    function LoadFile ($filename){
        if (file_exists ($filename)){
	            $fp = fopen($filename, "r");
	            $content = fread ($fp, filesize ($filename));
	            fclose ($fp);
	            return $content;
            } else return false;
    }

    function LoadTemplate ($name,$dir=''){
    	if($dir)$dir.='/';
    	$file=file_exists(TEMPLATE_DIR.$dir.$name)?TEMPLATE_DIR.$dir.$name:(file_exists(TEMPLATE_DIR.$dir.$name.'.html')?TEMPLATE_DIR.$dir.$name.'.html':TEMPLATE_DIR.$dir.$name.'.htm');
       	return LoadFile ($file);
    }

    function SetAtribs($template,$array){
		if(count($array)){
			foreach($array as $k=>$v)
				$template=str_replace('<%'.strtoupper($k).'%>',$v,$template);
		}
		return $template;
	}

	function SetContent($template,$content){
		return strtr($template,array(
			'<%CONTENT%>'	=> $content
		));
	}

    //function Redirect($url){
   	function __Redirect($url){
        header('Location: '.$url);
        exit;
    }

    function Reload(){
		header('Location: '.$_SERVER['HTTP_REFERER']);
	}

    function UrlAddVars($url,$vars){
		foreach($vars as $k=>$v){
			//$url=preg_replace('/(\.html|\/)$/is','-'.$k.'-'.$v.'\\1',$url);
			$url=preg_replace('/([^\/]+)(\/*)$/','\\1--'.$k.'-'.$v.'\\2',$url);
		}
		return $url;
	}

    function UrlDelVars($url,$vars){
    	if(is_array($vars)){
    		foreach($vars as $v){
				//$url=preg_replace('/-'.$k.'-[^\-]*(-|\.html$)/is','\\1',$url);
				$url=preg_replace('/--'.$v.'-[^\-\-\/]*([^\/]*)(\/*)$/','\\1\\2',$url);
			}
    	}else{
    		$url=preg_replace('/--'.$vars.'-[^\-\-\/]*([^\/]*)(\/*)$/','\\1\\2',$url);
    	}
		
		return $url;
	}

	function UrlSetVars($url,$vars){
		$url=UrlDelVars($url,$vars);
		$url=UrlAddVars($url,$vars);
	}

    function GetPagination($total, $p_size, $pg_size, $cur_page, $url = false, $get_var = 'page') {
    	if (!$url) $url = $_SERVER['REQUEST_URI'];
    	if (!$p_size) $p_size = 1;
    	if (!$pg_size) $pg_size = 1;
		$result = '';
		$page_count = ceil($total/$p_size);
		
		if ($page_count > 1) {
			$page_group = ceil($cur_page/$pg_size);
			$url = UrlDelVars($url, array($get_var, 'act'));

			$pg_size--;

			$result .= '<ul>';

			if ($cur_page > 1) {
				$result .= '<li class="prev"><a href="' . UrlAddVars($url, array($get_var => $cur_page-1)) . '">Назад</a></li>';
			} else {
				$result .= '<li class="prev disabled"><span>Назад</span></li>';
			}
			
			if (($cur_page > ceil($pg_size/2)+1) && ($cur_page < $page_count-$pg_size)) {
				$result .= '<li' . ($cur_page<>ceil($pg_size/2)+2 ? ' class="dotts-after"' : '') . '><a href="' . UrlAddVars($url, array($get_var => 1)) . '">1</a></li>';

				for ($i = $cur_page-ceil($pg_size/2); $i < $cur_page+ceil($pg_size/2)+1; $i++) {
					$result .= ($i == $cur_page ? '<li><strong>' . $i . '</strong></li>' : '<li><a href="' . UrlAddVars($url, array($get_var=>$i)) . '">' . $i . '</a></li>');
				}
				$result .= '<li class="dotts-before"><a href="'.UrlAddVars($url, array($get_var=>$page_count)).'">'.$page_count.'</a></li>';
			}

			if ($cur_page <= ceil($pg_size/2)+1) {
				for ($i = 1; $i < $cur_page+($pg_size)/2+1; $i++) {
					if ($page_count >= $i)
						$result .= ($i == $cur_page ? '<li><strong>' . $i . '</strong></li>' : '<li><a href="' . UrlAddVars($url, array($get_var=>$i)) . '">' . $i . '</a></li>');
				}
				if ($page_count > $cur_page+($pg_size)/2+1) {
					$result.='<li class="dotts-before"><a href="' . UrlAddVars($url, array($get_var=>$page_count)) . '">' . $page_count . '</a></li>';
				}
			} else {
				if ($cur_page >= $page_count-$pg_size) {				
					$result .= '<li' . ($cur_page<>ceil($pg_size/2)+2 ? ' class="dotts-after"' : '') . '><a href="' . UrlAddVars($url, array($get_var=>1)) . '">1</a></li>';
					for ($i = $cur_page-ceil($pg_size/2); $i <= $page_count; $i++){
						$result .= ($i == $cur_page ? '<li><strong>' . $i . '</strong></li>' : '<li><a href="' . UrlAddVars($url, array($get_var=>$i)) . '">' . $i . '</a></li>');
					}
				}
			}

			if ($cur_page < $page_count) {
				$result .= '<li class="next"><a href="' . UrlAddVars($url, array($get_var => $cur_page+1)) . '">Вперед</a></li>';
			} else {
				$result .= '<li class="next disabled"><span>Вперед</span></li>';
			}

			$result .= '</ul>';
		}
		return '<div id="pagination"><div class="holder">' . $result . '</div></div>';
	}

	function GetMore($function, $label=false){
		return '<div class="btn-line"><button class="btn type3 js-more-btn" data-function="'.$function.'">'.($label?$label:'Показать еще').'</div>';
	}

	function GetBreadCrumbs($refs, $curr_page){
		$content='';
		foreach($refs as $k=>$v)
			$content.='<li><a href="'.$v.'">'.$k.'</a></li>'."\r\n";
		$content.='<li>'.$curr_page.'</li>'."\r\n";
		return SetContent(LoadTemplate('bread_crumbs'), $content);
	}

	function ModifiedDate($date,$type=3){
		if(gettype($date)=='integer')$date=date('Y.m.d',$date);
		$months=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		if(preg_match('/(\d{4}).(\d{1,2}).(\d{1,2})/i',$date,$regs)){
			if($type==1){
				$today=mktime(0,0,0,date('m'),date('d'),date('Y'));
				$yesterday=$today-DAY_IN_SEC;
				$result=(strtotime($date)>=$today?'сегодня':(strtotime($date)>=$yesterday?'вчера':(int)$regs[3].' '.$months[(int)$regs[2]-1].(date('Y')==$regs[1]?'':' '.$regs[1])));
			}elseif($type==2){
				$result=$regs[3].' '.$months[(int)$regs[2]].' '.$regs[1].' г.';
			}elseif($type==3) {
				$result=$regs[3].'.'.$regs[2].'.'.$regs[1];
			}
		}
		return $result;
	}
	
	function ModifiedDateTime($date,$type=1){
		if(gettype($date)=='integer')$date=date('Y.m.d H:i:s',$date);
		$months=array('января','февраля','марта','апреля','мая','июня','июля','августа','сентября','октября','ноября','декабря');
		if(preg_match('/(\d{4}).(\d{1,2}).(\d{1,2}) (\d{1,2}).(\d{1,2}).(\d{1,2})/i',$date,$regs)){
			if($type==1){
				$tdiff=time()-strtotime($date);
				
				if($tdiff<MINUTE_IN_SEC){
					$result='только что';
				}elseif($tdiff<HOUR_IN_SEC){
					$min=floor($tdiff/MINUTE_IN_SEC);
					$result=$min.' '.Word125($min,'минуту','минуты','минут').' назад';
				}elseif($tdiff<=HOUR_IN_SEC*8){
					$hours=floor($tdiff/HOUR_IN_SEC);
					$min=floor(($tdiff-$hours*HOUR_IN_SEC)/MINUTE_IN_SEC);
					$result=$hours.' '.Word125($hours,'час','часа','часов').($min?(' '.$min.' '.Word125($min,'минуту','минуты','минут')):'').' назад';
				}elseif($tdiff<=DAY_IN_SEC){
					$result='вчера';
				}elseif($tdiff<WEEK_IN_SEC){
					$days=floor($tdiff/DAY_IN_SEC);
					$result=$days.' '.Word125($min,'день','дня','дней').' назад';
				}elseif($tdiff<MONTH_IN_SEC){
					$weeks=floor($tdiff/WEEK_IN_SEC);
					$result=$weeks.' '.Word125($min,'неделю','недели','недель').' назад';
				}else{
					$result=(int)$regs[3].' '.$months[(int)$regs[2]-1].(date('Y')==$regs[1]?'':' '.$regs[1]).' в '.$regs[4].':'.$regs[5];
				}				

			}

		}

		return $result;

	}
	
	function getFullYears($date) {
	    $datetime=new DateTime($date);
	    $interval=$datetime->diff(new DateTime(date('Y-m-d')));
	    return $interval->format('%Y');
	}
	
	function getAge($datebirth){
	  $datebirth_ts = strtotime($datebirth);
	  $age = date('Y') - date('Y', $datebirth_ts);
	  if (date('md', $datebirth_ts) > date('md')) {
	    $age--;
	  }
	  return $age;
	}
	
	function getPreposition($title){
		$vowels=array('а','е','ё','и','о','у','ы','э','ю','я','a','e','i','o','u');
		if(preg_match('/^[^a-zA-Zа-яА-я]*([a-zA-Zа-яА-я]?)/u',$title,$m)){
			$fst_letter=mb_strtolower($m[1]);
			if(in_array($fst_letter,$vowels)) return 'об';
		}
		return 'о';
	}
	
	function quoteTitle($title){
		$title='«'.$title.'»';
		return str_replace('««','«',str_replace('»»','»',$title));
	}
	
	function PhoneHref($phone){
		return preg_replace('/[^+0-9]/','',$phone);
	}

	function TrimText($text,$len,$postfix='...',$encoding='UTF-8'){
		if(mb_strlen($text,$encoding) <= $len){
	        return $text;
	    }	 
	    $tmp=mb_substr($text,0,$len,$encoding);
	    return mb_substr($tmp,0,mb_strripos($tmp,' ',0,$encoding),$encoding).$postfix;
	}
	
	function SplitText($text,$len,$postfix='...',$encoding='UTF-8'){
		if(mb_strlen($text,$encoding) <= $len){
	        return $text;
	    }	 
	    $tmp=mb_substr($text,0,$len,$encoding);
	    $pos=mb_strripos($tmp,' ',0,$encoding);
	    return mb_substr($text,0,$pos,$encoding).'<span class="delimiter">'.$postfix.'</span>'.mb_substr($text,$pos+1,mb_strlen($text,$encoding)-$pos,$encoding);
	}
	
	function SplitTextInBuffer($text,$len,$postfix='...',$encoding='UTF-8'){
		if(mb_strlen($text,$encoding) <= $len){
	        return array($text);
	    }	 
	    $tmp=mb_substr($text,0,$len,$encoding);
	    $pos=mb_strripos($tmp,' ',0,$encoding);
	    return array(
	    	mb_substr($text,0,$pos,$encoding),
	    	mb_substr($text,$pos+1,mb_strlen($text,$encoding)-$pos,$encoding)
		);
	}

	function TrimHtml($text,$len){
		if(mb_strlen(strip_tags($text))>$len){
			$res='';
			$tagged=false;
			$tagname='';
			$tags=array();
			$i=0;
			$lc=0;
			while($lc<=$len){
				$i++;
				if(mb_substr($text,$i-1,1)!='<'){
					if(mb_substr($text,$i-1,1)=='>'){
						$tagged=false;
						if(in_array($tagname,$tags)){
							$key=array_search($tagname,$tags);
							unset($tags[$key]);
						}else{
							$tags[]=$tagname;
						}
					}else{
						if(!$tagged){
							$lc++;
						}else{
							$tagname.=mb_substr($text,$i-1,1);
						}
					}
				}else{
					$tagged=true;
				}
				$res.=mb_substr($text,$i-1,1);
			}
			$res.='...';
			foreach(array_reverse($tags) as $k=>$tag){
				$res.='</'.$tag.'>';
			}
			return $res;
		}else{
			return $text;
		}	
	}

	function isEven($number){
		if($number%2==0)return true;
		return false;
	}

	function Num125($n){
		$n100 = $n % 100;
		$n10 = $n % 10;
	  	if( ($n100 > 10) && ($n100 < 20) ) {
	    	return 5;
	  	}
	  	elseif( $n10 == 1) {
	    	return 1;
	  	}
	  	elseif( ($n10 >= 2) && ($n10 <= 4) ) {
	    	return 2;
	  	}
	  	else {
	    	return 5;
	  	}
	}

	function Word125($n,$ending1,$ending2,$ending5){
		return ${'ending'.Num125((int)$n)};
	}
    
    function cp1251_to_utf8 ($str)  {
    	return iconv('CP1251','UTF-8',$str);
    	
	    $in_arr = array (
	        chr(208), chr(192), chr(193), chr(194),
	        chr(195), chr(196), chr(197), chr(168),
	        chr(198), chr(199), chr(200), chr(201),
	        chr(202), chr(203), chr(204), chr(205),
	        chr(206), chr(207), chr(209), chr(210),
	        chr(211), chr(212), chr(213), chr(214),
	        chr(215), chr(216), chr(217), chr(218),
	        chr(219), chr(220), chr(221), chr(222),
	        chr(223), chr(224), chr(225), chr(226),
	        chr(227), chr(228), chr(229), chr(184),
	        chr(230), chr(231), chr(232), chr(233),
	        chr(234), chr(235), chr(236), chr(237),
	        chr(238), chr(239), chr(240), chr(241),
	        chr(242), chr(243), chr(244), chr(245),
	        chr(246), chr(247), chr(248), chr(249),
	        chr(250), chr(251), chr(252), chr(253),
	        chr(254), chr(255)
	    );  
	    $out_arr = array (
	        chr(208).chr(160), chr(208).chr(144), chr(208).chr(145),
	        chr(208).chr(146), chr(208).chr(147), chr(208).chr(148),
	        chr(208).chr(149), chr(208).chr(129), chr(208).chr(150),
	        chr(208).chr(151), chr(208).chr(152), chr(208).chr(153),
	        chr(208).chr(154), chr(208).chr(155), chr(208).chr(156),
	        chr(208).chr(157), chr(208).chr(158), chr(208).chr(159),
	        chr(208).chr(161), chr(208).chr(162), chr(208).chr(163),
	        chr(208).chr(164), chr(208).chr(165), chr(208).chr(166),
	        chr(208).chr(167), chr(208).chr(168), chr(208).chr(169),
	        chr(208).chr(170), chr(208).chr(171), chr(208).chr(172),
	        chr(208).chr(173), chr(208).chr(174), chr(208).chr(175),
	        chr(208).chr(176), chr(208).chr(177), chr(208).chr(178),
	        chr(208).chr(179), chr(208).chr(180), chr(208).chr(181),
	        chr(209).chr(145), chr(208).chr(182), chr(208).chr(183),
	        chr(208).chr(184), chr(208).chr(185), chr(208).chr(186),
	        chr(208).chr(187), chr(208).chr(188), chr(208).chr(189),
	        chr(208).chr(190), chr(208).chr(191), chr(209).chr(128),
	        chr(209).chr(129), chr(209).chr(130), chr(209).chr(131),
	        chr(209).chr(132), chr(209).chr(133), chr(209).chr(134),
	        chr(209).chr(135), chr(209).chr(136), chr(209).chr(137),
	        chr(209).chr(138), chr(209).chr(139), chr(209).chr(140),
	        chr(209).chr(141), chr(209).chr(142), chr(209).chr(143)
	    );  
	    $str = str_replace($in_arr,$out_arr,$str);
	    return $str;
	}
	
	if (!function_exists('mb_ucfirst') && extension_loaded('mbstring'))	{
		function mb_ucfirst($str, $encoding='UTF-8') {
			$str = mb_ereg_replace('^[\ ]+', '', $str);
			$str = mb_strtoupper(mb_substr($str, 0, 1, $encoding), $encoding).
				   mb_substr($str, 1, mb_strlen($str), $encoding);
			return $str;
		}
	}
	
	function GetFileSizeFormat($filesize){
	    $formats = array('b','Kb','Mb','Gb','Tb');
	    $format = 0;
	    
	    while ($filesize > 1024 && count($formats) != ++$format){
	        $filesize = round($filesize / 1024, 2);
	    }
	    $formats[] = 'Tb';
	    
	    return $filesize.' '.$formats[$format];
	}


	class RecordList{

		private $list=array();

		private $inited=false;

		

		public function __construct($src=array()){

			switch(gettype($src)){

				case 'array':

					$this->list=$src;

					break;

				case 'resource':

					while($this->list[]=dbGetRecord($src));

					array_pop($this->list);

					break;

				case 'string':

					$res=dbDoQuery($src,__FILE__,__LINE__);

					while($this->list[]=dbGetRecord($res));

					array_pop($this->list);

					break;

				default:

					$this->list=array();

					break;	

			}

		}

		

		public function GetCurr(){

			return current($this->list);

		}

		

		public function GetPrev($recordId=false){

			if($recordId!==false)$this->SetPosById($recordId);

			return prev($this->list);

		}

		

		public function GetNext($recordId=false){

			if($recordId!==false)$this->SetPosById($recordId);

			if(!$recordId && ($this->GetKey()==0 && !$this->inited)){

				$this->inited=true;

				return current($this->list);

			}

			return next($this->list);

		}

		

		public function GetFirst(){

			return $this->Reset();

		}

		

		public function GetLast(){

			return end($this->list);

		}

		

		public function GetKey($recordId=false){

			if($recordId)$this->SetPosByKey($recordId);

			return key($this->list);

		}

		

		public function GetPos($recordId=false){

			return $this->GetKey($recordId);

		}

		

		public function Reset(){

			$this->inited=false;

			return reset($this->list);

		}

		

		public function GetCount(){

			return count($this->list);

		}

		

		public function SetPosByKey($key){

			$this->Reset();

			while($this->GetNext()!==false){

				if($this->GetKey()==$key)return true;

			}

			return false;

		}

		

		public function SetPosById($recordId){

			$this->Reset();

			while($this->GetNext()!==false){

				$curr=$this->GetCurr();

				if($curr['Id']==$recordId){

					return $this->GetKey();

				}

			}

			return false;

		}

		

		public function InsertFirst($src){

			switch(gettype($src)){

				case 'array':

					array_unshift($this->list,$src);

					break;

				case 'resource':

					$tmp=array();

					while($tmp[]=dbGetRecord($src));

					array_pop($tmp);

					array_unshift($this->list,$tmp);

					break;

				case 'string':

					$tmp=array();

					$res=dbDoQuery($src,__FILE__,__LINE__);

					while($tmp[]=dbGetRecord($res));

					array_pop($tmp);

					array_unshift($this->list,$tmp);

					break;

			}

		}
		
		public function ToArray(){
			return $this->list;
		}

		

	}
	
	function assocImplode($glue1,$glue2,$assoc_array,$reverse=false){
		if($reverse)$assoc_array=array_flip($assoc_array);
		$res=array();
		foreach($assoc_array as $k=>$v)$res[]=$k.$glue1.$v;
		return implode($glue2,$res);
	}
	
	function fullImplode($glue1,$glue2,$multi_array){
		$res=array();
		foreach($multi_array as $assoc_array)$res[]=assocImplode($glue1,$glue2,$assoc_array);
		return implode($glue2,$res);
	}
	
	function debugArr($array){
		echo nl2br(print_r($array,true));
		exit;
	}
	
	function strip_html_tags($text){
		$text=preg_replace(
			array(
				// Remove invisible content
				'@<head[^>]*?>.*?</head>@siu',
				'@<style[^>]*?>.*?</style>@siu',
				'@<script[^>]*?.*?</script>@siu',
				'@<object[^>]*?.*?</object>@siu',
				'@<embed[^>]*?.*?</embed>@siu',
				'@<applet[^>]*?.*?</applet>@siu',
				'@<noframes[^>]*?.*?</noframes>@siu',
				'@<noscript[^>]*?.*?</noscript>@siu',
				'@<noembed[^>]*?.*?</noembed>@siu',
				/*'@<input[^>]*?>@siu',*/
				'@<form[^>]*?.*?</form>@siu',
	
				// Add line breaks before & after blocks
				'@<((br)|(hr))>@iu',
				'@</?((address)|(blockquote)|(center)|(del))@iu',
				'@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
				'@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
				'@</?((table)|(th)|(td)|(caption))@iu',
				'@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
				'@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
				'@</?((frameset)|(frame)|(iframe))@iu',
			),
			array(
				" ", " ", " ", " ", " ", " ", " ", " ", " ", " ", 
				" ", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
				"\n\$0", "\n\$0",
			),
			$text );
	
		$text = preg_replace("/(^[\r\n]*|[\r\n]+)[\s\t]*[\r\n]+/", "\n", $text);
		$text = preg_replace("/\n( )*/", "\n", $text);
	
		return strip_tags($text);
	} 
	
	function GetURIParams($uri=false){
		if(!$uri)$uri=$_SERVER['REQUEST_URI'];
		$raw_arr=explode('&',mb_substr($uri,mb_strpos($uri,'?')+1));
		$params=array();
		foreach($raw_arr as $pair){
			$label=mb_substr($pair,0,mb_strpos($pair,'='));
			$value=mb_substr($pair,mb_strpos($pair,'=')+1);
			$params[$label]=$value;
		}
		return $params;
	}

	function punycode_encode($url) {
		$parts = parse_url($url);	 

		$out = '';
		if (!empty($parts['scheme']))   $out .= $parts['scheme'] . ':';
		if (!empty($parts['host']))     $out .= '//';
		if (!empty($parts['user']))     $out .= $parts['user'];
		if (!empty($parts['pass']))     $out .= ':' . $parts['pass'];
		if (!empty($parts['user']))     $out .= '@';
		if (!empty($parts['host']))     $out .= idn_to_ascii($parts['host']);
		if (!empty($parts['port']))     $out .= ':' . $parts['port'];
		if (!empty($parts['path']))     $out .= $parts['path'];
		if (!empty($parts['query']))    $out .= '?' . $parts['query'];
		if (!empty($parts['fragment'])) $out .= '#' . $parts['fragment'];	 

		return $out;
	}

?>