<?php 



$url = $_SERVER['REQUEST_URI'];
class UIPath { 
	protected function getViewport(){
		return $this->viewport;
	}
	public function getLoginURL(){
		$servername = $_SERVER['SERVER_NAME'];
		$x = explode("\\",dirname(__FILE__));
		$y = array_reverse($x);
		$context = "/".$y[2]."/";
		return $context;
	}
}

class UIWeb { 
	private static function getCacheBustingVersion($pathValue) {
		$context = MaxModules::getContext();
		$filePath = $_SERVER['DOCUMENT_ROOT'] . '/' .$context.'/'. $pathValue;
		if (file_exists($filePath)) {
			return '?v=' . filemtime($filePath);
		}
		return '';
	}
	public static function setLinkHrefIcon($pathValue){
		$getProperty = new UIPath();
		$loginURL = $getProperty->getLoginURL();
		$currentURL = $_SERVER['REQUEST_URI'];
		$getFullHostName = MaxModules::getFullHostName();
		$pathLinkHref = "<link href='$getFullHostName/$pathValue' rel='shortcut icon'>\n";
		echo $pathLinkHref;
	}
	public static function setLinkHref($pathValue){
		
		
		
		$getProperty = new UIPath();
		$getFullHostName = MaxModules::getFullHostName();
		$version = self::getCacheBustingVersion($pathValue);
		$pathLinkHref = "<link href='$getFullHostName/$pathValue$version' rel='stylesheet'>\n";
		echo $pathLinkHref;
	}

	public static function setScriptSrc($pathValue){
		
		$getProperty = new UIPath();
		$getFullHostName = MaxModules::getFullHostName();
		$version = self::getCacheBustingVersion($pathValue);
		$pathScriptSrc = "<script src='$getFullHostName/$pathValue$version' charset='utf-8'></script>\n";		
		echo $pathScriptSrc;
	}
	public static function moduleCheck($url, $pathValue, $pathJS, $type){
		if($url==$pathValue){
			if($type == "script"){
				UIWeb::setScriptSrc($pathJS);
			}else{
				if($type == "css") {
					UIWeb::setLinkHref($pathJS);
				}
			}
		}
	}
}

class MaxModules extends UIPath{
	public  static function  getContext(){
		$dirname = dirname(__FILE__);
		if(strpos($dirname, "\\") !== false) {
			$x = explode("\\",$dirname);
			$y = array_reverse($x);
			$context = $y[2];
		}else{
			$x = explode("/",$dirname);
			$y = array_reverse($x);
			$context = $y[2];
		}
		return $context;
	}
	public static function getFullHostName(){
		$context = MaxModules::getContext();
		$servername = $_SERVER['SERVER_NAME'];
		$port = $_SERVER['SERVER_PORT'];
		$serverPort = ":".$port;
		$http = "http://";
		if(!empty($serverPort)){
			if($serverPort==":80"){
				$serverPortVal="";
			}else{
				if($serverPort == ":443"){
					$http = "https://";
				}else{
					$serverPortVal=$serverPort;
				}
			}
		}
		
		$fullServerName = $http.$servername.$serverPortVal."/".$context;
		
		return $fullServerName;
	}
	
	public static function js($p1){
		$a = "
		<script text='javascript'>".$p1."</script>\n
		";
		echo $a;
	}
	

	public static function webProperties(){
		// Langsung gunakan path relatif dari lokasi file ini
		$appRoot = dirname(__DIR__, 2);
		
		$filePath = $appRoot . "/applications/properties/properties.xml";
		//echo "Path: " . $filePath . "<br>";

		$xmlload = XML::loadFile($filePath);
		$appHost = $xmlload->host;
		$domainHost = $xmlload->domainhost;
		$internetrequired = $xmlload->internetrequired;
		$appHost2 = $xmlload->remotehost;
		$wsport = $xmlload->wsport;

		$getProperty = new UIPath();
		$context = MaxModules::getContext();
		$url = $_SERVER['REQUEST_URI'];
		$serverPort = ":".$_SERVER['SERVER_PORT'];
		if($_SERVER['SERVER_PORT'] =="80" ){
			$serverPort="";
			$http="http://";
		}
		if($_SERVER['SERVER_PORT']=="443" ){
			$serverPort="";
			$http="https://";
		}else{
			$http="http://";
		}


		
		MaxModules::js("var context='".$context."';");

		if($domainHost == "" || $domainHost == null || empty($domainHost)){
			MaxModules::js("window.mxid=null, 
					url = url = window.location.href,
					appHost = \"".$appHost."\",
					appHost2 = \"".$appHost2."\",
					http=\"".$http."\",
					wsport=\"".$wsport."\",
					serverport=\"".$serverPort."\",
					window.uq_nvs = null;
					internetrequired = \"".$internetrequired."\";
					var maxhost =\"\";			
					if(url.indexOf(appHost) > -1) maxhost = appHost
					else maxhost = appHost2
					var domainhost = ''
					var rootHost = http + maxhost + serverport + \"/\" + context + \"/\" 
					var domainHost = ''
					var domainHostNonSSL = ''
					");
				
		}else{
			MaxModules::js("window.mxid=null, 
					url = url = window.location.href,
					appHost = \"".$appHost."\",
					appHost2 = \"".$appHost2."\",
					http=\"".$http."\",
					wsport=\"".$wsport."\",
					serverport=\"".$serverPort."\",
					uq_nvs = null;
					internetrequired = \"".$internetrequired."\";
					var maxhost =\"\";			
					if(url.indexOf(appHost) > -1) maxhost = appHost
					else maxhost = appHost2
					var domainhost = '$domainHost'
					var rootHost = http + maxhost + serverport + \"/\" + context + \"/\" 
					var domainHost = 'https://' + domainhost + serverport + \"/\" + context + \"/\" 
					var domainHostNonSSL = 'http://' + domainhost + serverport + \"/\" + context + \"/\" 
					");
		}
	
	
		echo '
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
		<meta name="description" content="">
		<meta name="author" content="">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<link rel="preconnect" href="https://fonts.googleapis.com">
		<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
		<link href="https://fonts.googleapis.com/css2?family=Blinker:wght@100;200;300;400;600;700;800;900&display=swap" rel="stylesheet">';

	}
}
class XML {
	public static function loadFile($filePath){
		if(!file_exists($filePath)) {
			echo "Property File is not found: " . $filePath;
			return null;
		}
		
		$xml = simplexml_load_file($filePath);
		
		if($xml){
			return $xml;
		} else {
			echo "Error on XML Parsing: " . $filePath;
			return null;
		}
	}

	function display_xml_error($error, $xml)
	{
		$return  = $xml[$error->line - 1] . "\n";
		$return .= str_repeat('-', $error->column) . "^\n";

		switch ($error->level) {
			case LIBXML_ERR_WARNING:
				$return .= "Warning $error->code: ";
				break;
			 case LIBXML_ERR_ERROR:
				$return .= "Error $error->code: ";
				break;
			case LIBXML_ERR_FATAL:
				$return .= "Fatal Error $error->code: ";
				break;
		}

		$return .= trim($error->message) .
				   "\n  Line: $error->line" .
				   "\n  Column: $error->column";

		if ($error->file) {
			$return .= "\n  File: $error->file";
		}

		return "$return\n";
	}
	
	public static function getFileProperties( $url )
	{
		$options = array(
			CURLOPT_RETURNTRANSFER => true,     // return web page
			CURLOPT_HEADER         => false,    // don't return headers
			CURLOPT_FOLLOWLOCATION => true,     // follow redirects
			CURLOPT_ENCODING       => "",       // handle all encodings
			CURLOPT_USERAGENT      => "spider", // who am i
			CURLOPT_AUTOREFERER    => true,     // set referer on redirect
			CURLOPT_CONNECTTIMEOUT => 120,      // timeout on connect
			CURLOPT_TIMEOUT        => 120,      // timeout on response
			CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
			CURLOPT_SSL_VERIFYPEER => false     // Disabled SSL Cert checks
		);

		$ch      = curl_init( $url );
		curl_setopt_array( $ch, $options );
		$content = curl_exec( $ch );
		$err     = curl_errno( $ch );
		$errmsg  = curl_error( $ch );
		$header  = curl_getinfo( $ch );
		curl_close( $ch );

		$header['errno']   = $err;
		$header['errmsg']  = $errmsg;
		$header['content'] = $content;
		return $header;
	}

	public static function load($asx){
		
		
		
	}
}

?>