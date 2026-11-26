<?php 
require_once ("uiweb.php");

class JVRCommon  {
	public static function Init(){
		if(isset($_GET['regcustomer'])){
			MaxModules::regcustomer();
		}
		if(isset($_GET['lang'])){		
			MaxModules::setCookies();
		}
		if(isset($_GET['p'])){		
			MaxModules::signup();
		}
		if(isset($_GET['lg'])){		
			MaxModules::login();
		}
		if(isset($_GET['so'])){		
			MaxModules::signout();
		}
		if(isset($_GET['t'])){		
			MaxModules::activateUser();
		}
		if(isset($_GET['rp'])){		
			MaxModules::resetPassword();
		}
		if(isset($_GET['rpl'])){		
			MaxModules::resetPasswordLink();
		}
		if(isset($_GET['chpw'])){		
			MaxModules::changePass();
		}
		if(isset($_GET['sjwt'])){		
			MaxModules::sjwt();
		}
		if(isset($_GET['pdu'])){		
			MaxModules::pdu();
		}
		if(isset($_GET['cjwt'])){		
			MaxModules::cjwt();
		}
		if(isset($_GET['cdu'])){		
			MaxModules::cdu();
		}
		if(isset($_GET['tdu'])){		
			MaxModules::tdu();
		}
		if(isset($_GET['chpwd'])){		
			MaxModules::chpwd();
		}
		if(isset($_POST['currency_code'])){
			MaxModules::purchase();
		}
		if(isset($_POST['cfirstname'])){
			PayPal::PayWithPayPal();
		}
		
		if(isset($_GET['paymentId']) && isset($_GET['success']) &&isset($_GET['req']) ){
			if (isset($_GET['success']) && $_GET['success'] == 'true') {
				PayPal::ExecutePayment();
			}else{
				PayPal::CancelPayment();
			}
		}
		if(isset($_GET['req']) && isset($_GET['success']) ){
			if (isset($_GET['success']) && $_GET['success'] == 'false') {
				PayPal::CancelPayment();
			}
		}
		if(isset($_GET['gtusr'])){
			MaxModules::gtusr();
		}
		if(isset($_GET['pyid'])){
			MaxModules::pyid();
		}
		if(isset($_GET['hcticket_tbl'])){
			if(isset($_GET['s'])){
			
				MaxModules::hcticket_tbl($_GET['hcticket_tbl'], 1, $_GET['s']);
			}else{
				$tx = explode("|", $_GET['hcticket_tbl']);
				if(!empty($tx[1])){
					MaxModules::hcticket_tbl($tx[0], $tx[1]);
				}else{
					MaxModules::hcticket_tbl($tx[0]);
				}	
			}
		}
		if(isset($_GET['shwtb'])){
			$table = $_GET['tb'];
			$id = $_GET['id'];
			MaxModules::shwtb($table, $id);
		}
		if(isset($_GET['tkb'])){
			MaxModules::tkb();
		}
		if(isset($_GET['tkry'])){
			MaxModules::tkry();
		}
		if(isset($_GET['uxt'])){
			MaxModules::uxt();
		}
		if(isset($_GET['asx'])){
			MaxModules::asx();
		}
		if(isset($_GET['rsx'])){
			MaxModules::rsx();
		}
		if(isset($_POST['ppluser'])){
			PayPal::PayWithPayPal2();
		}
		
		if(isset($_GET['paymentId']) && isset($_GET['success']) &&isset($_GET['urq']) ){
			if (isset($_GET['success']) && $_GET['success'] == 'true') {
				PayPal::ExecutePayment2();
			}else{
				PayPal::CancelPayment();
			}
		}
		if(isset($_GET['urq']) && isset($_GET['success']) ){
			if (isset($_GET['success']) && $_GET['success'] == 'false') {
				PayPal::CancelPayment();
			}
		}
		
		if(isset($_GET['dsoft'])){
			MaxModules::dsoft();
		}
		if(isset($_GET['dpatch'])){
			MaxModules::dpatch();
		}
		if(isset($_GET['ddocs'])){
			MaxModules::ddocs();
		}
		if(isset($_GET['dvids'])){
			MaxModules::dvids();
		}
		if(isset($_GET['fdl'])){
			MaxModules::fdl();
		}
		if(isset($_GET['pgr'])){
			MaxModules::pgr();
		}
		
	}		
}

JVRCommon::Init();

?>