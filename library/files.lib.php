<?php
    
    function flCreateFile($filename){
		touch($filename);
	}
    
    function flSaveFile($filename,$content,$binary=false){
		$fp=fopen($filename,$binary?'wb':'w');
		fwrite($fp,$content);
		fclose($fp);
	}
	
	function flLoadFile($filename,$binary=false){
		if(file_exists($filename)){
			return file_get_contents($filename,$binary?FILE_BINARY:FILE_TEXT);
		}else{
			return '';
		}
	}
	
	function flLoadFileLines($filename,$binary=false){
		if(file_exists($filename)){
			return file($filename,$binary?FILE_BINARY:FILE_TEXT);
		}else{
			return '';
		}
	}
	
	function flCopyFile($sourcepath,$destpath,$force=false){
		if(!$force){
			$info=flGetInfo($destpath);
			$destpath=flUniqueName($info['basename'],$info['directory']);
		}
		copy($sourcepath,$destpath);
	}
	
	function flMoveFile($sourcepath,$destpath,$force=false){
		if(!$force){
			$info=flGetInfo($destpath);
			$destpath=flUniqueName($info['basename'],$info['directory']);
		}
		rename($sourcepath,$destpath);
		return $destpath;
	}
	
	function flDeleteFile($filename){
		@unlink($filename);
	}
	
	function flIsUploaded($tmpname){
		return is_uploaded_file($tmpname);
	}
	
	function flMoveUploaded($tmpname,$filename){
		move_uploaded_file($tmpname,$filename);
		@chmod($filename,CONFIG_MASK_FILE^0666);
	}
	
	function flGetInfo($filepath){
		$basename=basename($filepath);
		$directory=mb_substr($filepath,0,mb_strpos($filepath,$basename));
		$extension=mb_strpos($basename,'.')?mb_substr($basename,mb_strpos($basename,'.')+1):'';
		$caption=$extension?mb_substr($basename,0,mb_strpos($basename,'.')):$basename;
		$size=@filesize($filepath);
		$root=@mb_substr($_SERVER['HTTP_REFERER'],0,mb_strpos($_SERVER['HTTP_REFERER'],basename($_SERVER['HTTP_REFERER'])));
		$absolute=$root.$directory.$basename;
		return Array(
			'basename'	=> $basename,
			'directory'	=> $directory,
			'absolute'	=> $absolute,
			'caption'	=> $caption,
			'extension'	=> $extension,
			'size'		=> $size
		);
	}
	
	function flGetExtension($filepath) {
	    return substr(strrchr($filepath,'.'),1);
 	}
	
	function flUniqueName($filename,$directory){
		$counter=1;
		if(mb_substr($directory,mb_strlen($directory)-1)!='/')$directory.='/';
		$info=flGetInfo($directory.$filename);
		$filepath=$directory.$filename;
		while(file_exists($filepath)){
			$counter++;
			$filepath=$directory.$info['caption'].'('.$counter.').'.$info['extension'];
		}
		return $filepath;
	}
	
	function flUpload($filename,$tmpname,$directory,$keepOrigin=false){
		krnLoadLib('chars');
		$filepath=flUniqueName($keepOrigin?$filename:chrTranslit($filename),$directory);
		flMoveUploaded($tmpname,$filepath);
		return $filepath;
	}
	
	function flCreateDirectory($directory){
		if(!is_dir($parDir=dirname($directory))){
			flCreateDirectory($parDir);
		}
		return @mkdir($directory,CONFIG_MASK_DIR^0777);
	}
	
	function flDeleteDirectory($directory){
		if($dp=@opendir($directory)){ 
			while(($filename=readdir($dp))!==false){
				if($filename!='.' && $filename!='..'){
					if(is_dir("$dirname/$filename"))
						cmDeleteDir("$dirname/$filename");
					else
						cmDeleteFile("$dirname/$filename");
				}
			}
			closedir($dp);
			rmdir($directory);
		}
	}
	
	function flDownloadExternal($external_link,$dest_directory){
		$info=flGetInfo($external_link);	
		if($info['basename']){
			krnLoadLib('chars');
			$ch=curl_init(str_replace(' ','%20',$external_link));
			$filepath=flUniqueName(chrTranslit($info['basename']),$dest_directory);
			$fp=fopen($filepath,'wb');
			curl_setopt($ch,CURLOPT_FILE,$fp);
			curl_setopt($ch,CURLOPT_HEADER,0);
			curl_exec($ch);
			curl_close($ch);
			fclose($fp);
			return $filepath;
		}
		return false;
	}

?>