<?php 
require_once ("../../eat.php");
header('Access-Control-Allow-Origin: *');
		
class Action  {
	
	public static function Run(){
		if(isset($_POST["removeaccount"]))XboSet::removeAccount();
		if(isset($_POST["removeCompanyLogo"]))XboSet::removeCompanyLogo();
		if(isset($_POST["loadCompanyLogo"]))XboSet::loadCompanyLogo();
		if(isset($_POST["uploadCompanyLogo"]))XboSet::uploadCompanyLogo();
		if(isset($_POST["deleteImageFromAndroid"])) XboSet::deleteImageFromAndroid(); 
		if(isset($_POST["updateAndroidProfile"])) XboSet::updateAndroidProfile();
		if(isset($_POST["androidSignIn"])) XboSet::androidSignIn(); 
		if(isset($_POST["forgotEmail"])) XboSet::forgotEmail(); 
		if(isset($_POST["checkowneremail"])) XboSet::checkowneremail(); 
		if(isset($_POST["uploadErrorLogAndroid"])) XboSet::uploadErrorLogAndroid(); 
		if(isset($_POST["deletePlanItem"])) XboSet::deletePlanItem(); 
		if(isset($_POST["createAndroidWO"])) XboSet::createAndroidWO(); 
		if(isset($_POST["createusername"])) XboSet::CreateUsername(); 
		if(isset($_POST["createusername"])) XboSet::CreateUsername(); 
		if(isset($_POST["android_xbosetname"])) XboSet::getDataList(); 
		if(isset($_POST["barcodeuserverification"]))XboSet::BarcodeUserVerification();
		if(isset($_POST["uploadImageAndroid"]))XboSet::UploadImageAndroid();
		if(isset($_POST["modAndroidItemCatalog"]))XboSet::modAndroidItemCatalog();
		if(isset($_POST["loadItemCatalog"]))XboSet::loadItemCatalog();
		
		if(isset($_GET["resetPassword"])) XboSet::resetPassword();
		
	}	
	
 }

Action::Run();

?>