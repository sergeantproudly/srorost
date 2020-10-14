<?php

	krnLoadLib('files');

	function UploaderFile(){
		krnLoadLib('settings');
		global $Settings;
		$recordId=$_POST['id'];
		$elementId=$_POST['element_id'];
		$filepath=flUpload($_FILES['File']['name'],$_FILES['File']['tmp_name'],ABS_PATH.TEMP_DIR,false);
		
		$javascript="parent.uplSetUploaded($recordId,$elementId,'$filepath','{$_FILES['File']['name']}');";
		return '<script type="text/javascript">'.$javascript.'</script>';
	}
	
	function UploaderImage(){
		krnLoadLib('images');
		krnLoadLib('settings');
		global $Settings;
		$recordId=$_POST['id'];
		$elementId=$_POST['element_id'];
		$filepath=flUpload($_FILES['File']['name'],$_FILES['File']['tmp_name'],ABS_PATH.TEMP_DIR,false);
		
		$preview['width']=35;
		$preview['height']=35;
		$info=flGetInfo($filepath);
		$preview['filepath']=TEMP_DIR.$info['caption'].($w?$w:'').($h?'_'.$h:'').'_preview.'.$info['extension'];
		flCopyFile($filepath,ABS_PATH.$preview['filepath']);
		imgThumbnail(ABS_PATH.$preview['filepath'],$preview['width'],$preview['height']);
		
		$html='<img src="'.$preview['filepath'].'" alt="'.$_FILES['File']['name'].'" title="'.$_FILES['File']['name'].'" class="preview"/> Файл успешно загружен';
		$javascript="parent.uplSetUploaded($recordId,$elementId,'$filepath','{$_FILES['File']['name']}','$html');";
		return '<script type="text/javascript">'.$javascript.'</script>';
	}

?>