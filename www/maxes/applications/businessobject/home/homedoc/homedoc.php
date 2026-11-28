<?php 
require_once ("../../eat.php");

class Home extends Xbo {
	
	public function __construct(){
		
	}
	public function __destruct()
    {
       return null;
    }
	
	public static function displayData(){
		
		
	}
	public static function init(){
		Home::displayData();
	}
	
	

}

Home::init();



?>