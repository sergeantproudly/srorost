<?php

class Mail {
	
	protected $db;
	protected $settings;
	
	public $server;
	public $port;
	public $username;
	public $password;
	public $secure;    /* can be tls, ssl, or none */

	public $charset = "\"utf-8\""; /* included double quotes on purpose */
	public $contentType = "multipart/mixed";  /* can be set to: text/plain, text/html, multipart/mixed */
	public $transferEncodeing = "quoted-printable"; /* or 8-bit  */
	public $altBody = "";
	public $isLogin = false;
	public $recipients = array();
	public $cc = array();
	public $bcc = array();
	public $attachments = array();

	private $conn;
	private $newline = "\r\n";
	private $localhost = 'localhost';
	private $timeout = '60';
	private $debug = false;

	public function __construct($server=false, $port=false, $username=null, $password=null, $secure=null) {
		global $Config;
		global $Params;
		global $Settings;
		$this->db=$Params['Db']['Link'];
		$this->settings=$Settings;
		
  		$this->server = $server?$server:$Config['Smtp']['Server'];
		$this->port = $port?$port:$Config['Smtp']['Port'];
		$this->username = $username?$username:$Config['Smtp']['Email'];
		$this->password = $password?$password:$Config['Smtp']['Password'];
		$this->secure = $secure?$secure:$Config['Smtp']['Secure'];

		if(!$this->connect()) return;
		if(!$this->auth()) return;
		$this->isLogin = true;
		return;
	}

	/* Connect to the server */
	private function connect() {
		if(strtolower(trim($this->secure)) == 'ssl') {
			$this->server = 'ssl://' . $this->server;
		}
		$this->conn = fsockopen($this->server, $this->port, $errno, $errstr, $this->timeout);
		if (substr($this->getServerResponse(),0,3)!='220') { return false; }
		return true;
	}

	/* sign in / authenicate */
	private function auth() {
		fputs($this->conn, 'HELO ' . $this->localhost . $this->newline);
		$this->getServerResponse();
		if(strtolower(trim($this->secure)) == 'tls') {
			fputs($this->conn, 'STARTTLS' . $this->newline);
			if (substr($this->getServerResponse(),0,3)!='220') { return false; }
			stream_socket_enable_crypto($this->conn, true, STREAM_CRYPTO_METHOD_TLS_CLIENT);
			fputs($this->conn, 'HELO ' . $this->localhost . $this->newline);
			if (substr($this->getServerResponse(),0,3)!='250') { return false; }
		}
		if($this->server != 'localhost') {
			fputs($this->conn, 'AUTH LOGIN' . $this->newline);
			if (substr($this->getServerResponse(),0,3)!='334') { return false; }
			fputs($this->conn, base64_encode($this->username) . $this->newline);
			if (substr($this->getServerResponse(),0,3)!='334') { return false; }
			fputs($this->conn, base64_encode($this->password) . $this->newline);
			if (substr($this->getServerResponse(),0,3)!='235') { return false; }
		}
		return true;
	}

	/* send the email message */
	public function send($from, $to, $subject, $message, $headers=null) {
	  /* set up the headers and message body with attachments if necessary */
	  $email  = "Date: " . date("D, j M Y G:i:s") . " -0500" . $this->newline;
    $email .= "From: $from" . $this->newline;
    $email .= "Reply-To: $from" . $this->newline;
    $email .= $this->setRecipients($to);

    if ($headers != null) { $email .= $headers . $this->newline; }

    $email .= "Subject: $subject" . $this->newline;
    $email .= "MIME-Version: 1.0" . $this->newline;
    if($this->contentType == "multipart/mixed") {
      $boundary = $this->generateBoundary();
      $message = $this->multipartMessage($message,$boundary);
      $email .= "Content-Type: $this->contentType;" . $this->newline;
      $email .= "    boundary=\"$boundary\"";
    } else {
      $email .= "Content-Type: $this->contentType; charset=$this->charset";
    }
    $email .= $this->newline . $this->newline . $message . $this->newline;
    $email .= "." . $this->newline;

		/* set up the server commands and send */
		fputs($this->conn, 'MAIL FROM: <'. $this->getMailAddr($from) .'>'. $this->newline);
		$this->getServerResponse();

		if(!$to=='') {
			fputs($this->conn, 'RCPT TO: <'. $this->getMailAddr($to) .'>' . $this->newline);
			$this->getServerResponse();
		}
		$this->sendRecipients($this->recipients);
		$this->sendRecipients($this->cc);
		$this->sendRecipients($this->bcc);

		fputs($this->conn, 'DATA'. $this->newline);
		$this->getServerResponse();
		fputs($this->conn, $email);  /* transmit the entire email here */
		if (substr($this->getServerResponse(),0,3)!='250') { return false; }
		return true;
	}

	private function setRecipients($to) { /* assumes there is at least one recipient */
		$r = 'To: ';
		if(!($to=='')) { $r .= $to . ','; }
		if(count($this->recipients)>0) {
			for($i=0;$i<count($this->recipients);$i++) {
				$r .= $this->recipients[$i] . ',';
			}
		}
		$r = substr($r,0,-1) . $this->newline;  /* strip last comma */;
		if(count($this->cc)>0) { /* now add in any CCs */
			$r .= 'CC: ';
			for($i=0;$i<count($this->cc);$i++) {
				$r .= $this->cc[$i] . ',';
			}
			$r = substr($r,0,-1) . $this->newline;  /* strip last comma */
		}
		return $r;
	}

	private function sendRecipients($r) {
	  if(empty($r)) { return; }
		for($i=0;$i<count($r);$i++) {
			fputs($this->conn, 'RCPT TO: <'. $this->getMailAddr($r[$i]) .'>'. $this->newline);
			$this->getServerResponse();
		}
	}

	public function addRecipient($recipient) {
		$this->recipients[] = $recipient;
	}

	public function clearRecipients() {
		unset($this->recipients);
		$this->recipients = array();
	}

	public function addCC($c) {
		$this->cc[] = $c;
	}

	public function clearCC() {
		unset($this->cc);
		$this->cc = array();
	}

	public function addBCC($bc) {
		$this->bcc[] = $bc;
	}

	public function clearBCC() {
		unset($this->bcc);
		$this->bcc = array();
	}

	public function addAttachment($filePath) {
		$this->attachments[] = $filePath;
	}

	public function clearAttachments() {
		unset($this->attachments);
		$this->attachments = array();
	}

	/* Quit and disconnect */
	function __destruct() {
		fputs($this->conn, 'QUIT' . $this->newline);
		$this->getServerResponse();
		fclose($this->conn);
	}

  /* private functions used internally */
	private function getServerResponse() {
    $data="";
    while($str = fgets($this->conn,4096)) {
      $data .= $str;
      if(substr($str,3,1) == " ") { break; }
    }
    if($this->debug) echo $data . "<br>";
    return $data;
	}

	private function getMailAddr($emailaddr) {
	   $addr = $emailaddr;
	   $strSpace = strrpos($emailaddr,' ');
	   if($strSpace > 0) {
	     $addr= substr($emailaddr,$strSpace+1);
	     $addr = str_replace("<","",$addr);
	     $addr = str_replace(">","",$addr);
	   }
	   return $addr;
	}

	private function randID($len) {
	  $index = "abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ";
	  $out = "";
	  for ($t=0; $t<$len;$t++) {
	    $r = rand(0,61);
	    $out = $out . substr($index,$r,1);
	  }
	  return $out;
	}

	private function generateBoundary() {
	    $boundary = "--=_NextPart_000_";
	    $boundary .= $this->randID(4) . "_";
	    $boundary .= $this->randID(8) . ".";
	    $boundary .= $this->randID(8);
	    return $boundary;
	}

	private function multipartMessage($htmlpart,$boundary) {
		if($this->altBody == "") { $this->altBody = strip_html_tags($htmlpart); }
		$altBoundary = $this->generateBoundary();
		ob_start(); //Turn on output buffering
		$parts  = "This is a multi-part message in MIME format." . $this->newline . $this->newline;
  		$parts .= "--" . $boundary . $this->newline;

		$parts .= "Content-Type: multipart/alternative;" . $this->newline;
		$parts .= "    boundary=\"$altBoundary\"" . $this->newline . $this->newline;

		$parts .= "--" . $altBoundary . $this->newline;
	    $parts .= "Content-Type: text/plain; charset=$this->charset" . $this->newline;
	    $parts .= "Content-Transfer-Encoding: $this->transferEncodeing" . $this->newline . $this->newline;
	    $parts .= $this->altBody . $this->newline . $this->newline;
	
	    $parts .= "--" . $altBoundary . $this->newline;
	    $parts .= "Content-Type: text/html; charset=$this->charset" . $this->newline;
	    $parts .= "Content-Transfer-Encoding: $this->transferEncodeing" . $this->newline . $this->newline;
		$parts .= $htmlpart . $this->newline . $this->newline;

		$parts .= "--" . $altBoundary . "--" . $this->newline . $this->newline;

		if(count($this->attachments) > 0) {
		  for($i=0;$i<count($this->attachments);$i++) {
				$attachment = chunk_split(base64_encode(file_get_contents($this->attachments[$i])));
				$filename = basename($this->attachments[$i]);
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$parts .= "--" . $boundary . $this->newline;
			  $parts .= "Content-Type: application/$ext; name=\"$filename\"" . $this->newline;
				$parts .= "Content-Transfer-Encoding: base64" . $this->newline;
				$parts .= "Content-Disposition: attachment; filename=\"$filename\"" . $this->newline . $this->newline;
				$parts .=  $attachment . $this->newline;
		  }
		}

		$parts .= "--" . $boundary . "--";

	    $message = ob_get_clean(); //Turn off output buffering
	    return $parts;
	}

	public function SendMailFromSite($to,$subject,$text,$attachments=array()){
		$recepients=explode(',',$to);
		if(count($recepients)>1){
			foreach($recepients as $counter=>$recepient){
				if($counter==0)$to=$recepient;
				else $this->addRecipient($recepient);
			}
		}
		
		if($attachments){
			foreach($attachments as $attachment){
				$this->addAttachment($attachment);
			}
		}
		
		global $Config;
		$sended=$this->send($Config['Smtp']['Email'],$to,$subject,$text);
		
		$this->clearRecipients();
	    $this->clearCC();
	    $this->clearBCC();
	    $this->clearAttachments();
		
		return $sended;
	}
	
	public function SendDesignedMailFromSite($To,$ToName,$Subject,$Header,$Body,$Descriptor,$Type,$AttmFiles=array()){
		$element=LoadTemplate('mail_socials_el');
		$socials='';
			
		$res=dbDoQuery('SELECT Title, Link, ImageMail28_28 AS Image FROM social ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$rec['Alt']=htmlspecialchars($rec['Title'],ENT_QUOTES);
			$rec['Image']=$this->settings->GetSetting('SiteUrl').'/'.$rec['Image'];
			$socials.=SetAtribs($element,$rec);
		}
		
		krnLoadLib('settings');
		$template=LoadTemplate('base_mail');
		$Body=preg_replace('/<a href=(\S*)>(.*?)<\/a>/','<a href=$1 target="_blank">$2</a>',$Body);
		$Html=strtr($template,array(
			'<%SERVER_URL%>'	=> $this->settings->GetSetting('SiteUrl'),
		 	'<%HEADER%>'		=> strtoupper($Header),
		 	'<%BODY%>'			=> $Body,
		 	'<%DESCRIPTOR%>'	=> $Descriptor,
		 	'<%SOCIALS%>'		=> $socials,
		 	'<%TYPE%>'			=> $Type,
		 	'<%EMAIL%>'			=> urlencode($To),
		 	'<%GUID%>'			=> GenerateGuid($To)
		));
		$Text=strip_tags(str_replace('<br/>',"\r\n",$Header))."\r\n\r\n";
		$Text.=strip_tags(str_replace('<br/>',"\r\n",$Body));
		$this->SendMailFromSite($To,$ToName,$Subject,$Html,$Text,$AttmFiles);
	}

}

/*
class Mail{
	
	protected $db;
	protected $settings;
	
	public function __construct(){
		global $Params;
		global $Settings;
		$this->db=$Params['Db']['Link'];
		$this->settings=$Settings;
	}
	
	public function SendMail($smtp,			// SMTP-сервер
	          $port,			// порт SMTP-сервера
	          $login,			// имя пользователя для доступа к почтовому ящику
	          $password, 		// пароль для доступа к почтовому ящику
	          $from,			// адрес электронной почты отправителя
	          $from_name,		// имя отправителя
	          $to, 			// адрес электронной почты получателя
	          $subject, 		// тема сообщения
	          $message,		// текст сообщения
	          $res)			// сообщение, выводимое при успешной отправке
	{
	
	//    блок для других кодировок, отличных от UTF-8
	//    $message = iconv("UTF-8","KOI8-R",$message); // конвертируем в koi8-r
	//    $message = "Content-Type: text/plain; charset=\"koi8-r\"\r\nContent-Transfer-Encoding: 8bit\r\n\r\n".$message; // конвертируем в koi8-r
	//    $subject=base64_encode(iconv("UTF-8","KOI8-R",$subject)); // конвертируем в koi8-r
	//    $subject=base64_encode($subject); // конвертируем в koi8-r
	
	
		$from_name = base64_encode($from_name);
		$subject = base64_encode($subject);
		$message = base64_encode($message);
	    $message = "Content-Type: text/plain; charset=\"utf-8\"\r\nContent-Transfer-Encoding: base64\r\nUser-Agent: Koks Host Mail Robot\r\nMIME-Version: 1.0\r\n\r\n".$message;
	    $subject="=?utf-8?B?{$subject}?=";
	    $from_name="=?utf-8?B?{$from_name}?=";
	
	    try {
	        
     		$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
	        if ($socket < 0) {
	            throw new Exception('socket_create() failed: '.socket_strerror(socket_last_error())."\n");
	        }
	
	        $result = socket_connect($socket, $smtp, $port);
	        if ($result === false) {
	            throw new Exception('socket_connect() failed: '.socket_strerror(socket_last_error())."\n");
	        } 
	
	        smtp_read($socket);
	        
	        smtp_write($socket, 'EHLO '.$login);
	        smtp_read($socket); 
	        smtp_write($socket, 'AUTH LOGIN');
	        smtp_read($socket);        
	        smtp_write($socket, base64_encode($login));
	        smtp_read($socket);
	        smtp_write($socket, base64_encode($password));
	        smtp_read($socket); 
	        smtp_write($socket, 'MAIL FROM:<'.$from.'>');
	        smtp_read($socket); 
	        smtp_write($socket, 'RCPT TO:<'.$to.'>');
	        smtp_read($socket); 
	        smtp_write($socket, 'DATA');
	        smtp_read($socket); 
	        $message = "FROM:".$from_name."<".$from.">\r\n".$message; 
	        $message = "To: $to\r\n".$message; 
	        $message = "Subject: $subject\r\n".$message;
	
		    date_default_timezone_set('UTC');
		    $utc = date('r');
	
	        $message = "Date: $utc\r\n".$message;
	        smtp_write($socket, $message."\r\n.");
	        smtp_read($socket); 
	        smtp_write($socket, 'QUIT');
	        smtp_read($socket); 
	        return $res;
	        
	    } catch (Exception $e) {
	        echo "\nError: ".$e->getMessage();
	    }
	
	   
	    if (isset($socket)) {
	        socket_close($socket);
	    }
	}
	
	private function smtp_read($socket) {
	  $read = socket_read($socket, 1024);
	        if ($read{0} != '2' && $read{0} != '3') {
	            if (!empty($read)) {
	                throw new Exception('SMTP failed: '.$read."\n");
	            } else {
	                throw new Exception('Unknown error'."\n");
	            }
	        }
	}
	    
	private function smtp_write($socket, $msg) {
	  $msg = $msg."\r\n";
	  socket_write($socket, $msg, strlen($msg));
	}
	
	/*
	public function SendMail($From,$FromName,$To,$ToName,$Subject,$Html,$Text,$AttmFiles=array()){
		$From=iconv('utf-8','windows-1251',$From);
		$FromName=iconv('utf-8','windows-1251',$FromName);
		$To=iconv('utf-8','windows-1251',$To);
		$ToName=iconv('utf-8','windows-1251',$ToName);
		$Subject=iconv('utf-8','windows-1251',$Subject);
		$Html=iconv('utf-8','windows-1251',$Html);
		$Text=iconv('utf-8','windows-1251',$Text);
		
		$Subject="=?koi8-r?B?".base64_encode(convert_cyr_string($Subject,"w","k"))."?=";
		
		$OB="----=_OuterBoundary_000";
		$IB="----=_InnerBoundery_001";
		$Html=$Html?$Html:preg_replace("/\n/","<br>",$Text);
		$Text=$Text?$Text:"Sorry, but you need an html mailer to read this mail.";
		
		$headers ="MIME-Version: 1.0\r\n";
		$headers.="From: ".($FromName?"=?koi8-r?B?".base64_encode(convert_cyr_string($FromName,"w","k"))."?= <".$From.">":$From)."\n";
		$headers.="Reply-To: ".($FromName?"=?koi8-r?B?".base64_encode(convert_cyr_string($FromName,"w","k"))."?= <".$From.">":$From)."\n";
		$headers.="X-Priority: 3\n";
		$headers.="X-MSMail-Priority: High\n";
		$headers.="X-Mailer: Site Mailer\n";
		$headers.="Content-Type: multipart/mixed;\n\tboundary=\"".$OB."\"\n";
		
		//Messages start with text/html alternatives in OB
		$Msg ="This is a multi-part message in MIME format.\n";
		$Msg.="\n--".$OB."\n";
		$Msg.="Content-Type: multipart/alternative;\n\tboundary=\"".$IB."\"\n\n";
		
		//plaintext section
		$Msg.="\n--".$IB."\n";
		$Msg.="Content-Type: text/plain;\n\tcharset=\"windows-1251\"\n";
		$Msg.="Content-Transfer-Encoding: base64\n\n";
		
		// plaintext goes here 
		$Msg.=chunk_split(base64_encode($Text))."\n\n";
		
		// html section
		$Msg.="\n--".$IB."\n";
		$Msg.="Content-Type: text/html;\n\tcharset=\"windows-1251\"\n";
		$Msg.="Content-Transfer-Encoding: base64\n\n";
		
		// html goes here
		$Msg.=chunk_split(base64_encode($Html))."\n\n";
		
		// end of IB
		$Msg.="\n--".$IB."--\n";
		
		// attachments
		if(count($AttmFiles)){
			krnLoadLib('files');
			foreach($AttmFiles as $FileName=>$AttmFile){
				if(file_exists($AttmFile)){
					$Msg.= "\n--".$OB."\n"; 
					$Msg.="Content-Type: application/octetstream;\n\tname=\"".$FileName."\"\n";
					$Msg.="Content-Transfer-Encoding: base64\n";
					$Msg.="Content-Disposition: attachment;\n\tfilename=\"".$FileName."\"\n\n";
					
					//file goes here
					$FileContent=flLoadFile($AttmFile);
					$FileContent=chunk_split(base64_encode($FileContent));
					$Msg.=$FileContent;
					$Msg.="\n\n";
				} 
			}
		}
		
		//message ends
		$Msg.="\n--".$OB."--\n";
		
		return mail($To,$Subject,$Msg,$headers);
	}
	*
	
	public function SendMailFromSite($To,$ToName,$Subject,$Html,$Text,$AttmFiles=array()){
		global $Config;
		krnLoadLib('settings');
		$siteTitle=$this->settings->GetSetting('SiteTitle',$Config['Site']['Title']);
		$siteEmail=$this->settings->GetSetting('SiteEmail',$Config['Site']['Email']);
		return $this->SendMail($siteEmail,$siteTitle,$To,$ToName,$Subject,$Html,$Text,$AttmFiles);
	}
	
	public function SendDesignedMailFromSite($To,$ToName,$Subject,$Header,$Body,$Descriptor,$Type,$AttmFiles=array()){
		$element=LoadTemplate('mail_socials_el');
		$socials='';
			
		$res=dbDoQuery('SELECT Title, Link, ImageMail28_28 AS Image FROM social ORDER BY IF(`Order`,-1000/`Order`,0) ASC',__FILE__,__LINE__);
		while($rec=dbGetRecord($res)){
			$rec['Alt']=htmlspecialchars($rec['Title'],ENT_QUOTES);
			$rec['Image']=$this->settings->GetSetting('SiteUrl').'/'.$rec['Image'];
			$socials.=SetAtribs($element,$rec);
		}
		
		krnLoadLib('settings');
		$template=LoadTemplate('base_mail');
		$Body=preg_replace('/<a href=(\S*)>(.*?)<\/a>/','<a href=$1 target="_blank">$2</a>',$Body);
		$Html=strtr($template,array(
			'<%SERVER_URL%>'	=> $this->settings->GetSetting('SiteUrl'),
		 	'<%HEADER%>'		=> strtoupper($Header),
		 	'<%BODY%>'			=> $Body,
		 	'<%DESCRIPTOR%>'	=> $Descriptor,
		 	'<%SOCIALS%>'		=> $socials,
		 	'<%TYPE%>'			=> $Type,
		 	'<%EMAIL%>'			=> urlencode($To),
		 	'<%GUID%>'			=> GenerateGuid($To)
		));
		$Text=strip_tags(str_replace('<br/>',"\r\n",$Header))."\r\n\r\n";
		$Text.=strip_tags(str_replace('<br/>',"\r\n",$Body));
		$this->SendMailFromSite($To,$ToName,$Subject,$Html,$Text,$AttmFiles);
	}
	
	
}
*/

?>