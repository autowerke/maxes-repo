<?php

Class DataLoader {
	
	public static function DownloadLoader(){
	
		$value = $_POST["downloadloader"];
		$isarr = is_array($value);
		if($isarr == 1){
			$countloadfiles = count($value);
			for($i=0; $i<$countloadfiles; $i++){
				$val = $value[$i];
				$get = Super::getExcelPath($val);
				$fullPath = $get->data;
				$filename = $get->filename;
				$appurl = $get->appurl;
				if (file_exists($fullPath)) {
					$fullhost =  ContextPath::getFullHostName()."/applications/importdata/".$appurl.$filename;
					Set::throws(["msg"=>"downloaded", "errormsg"=>"success", "reviewmsg"=>$fullhost]);
					exit;
				}else{
					Set::throws(["msg"=>"none", "errormsg"=>"File does not exist in $fullPath", "reviewmsg"=>""]);
				}
			}
		}
	}
	public static function Load()
	{
		Super::Load();
	}
	
} 


?>