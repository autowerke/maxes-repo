<?php 
require_once ("../eat.php");

header('Access-Control-Allow-Origin: *');
		
class Action  {
	
	public static function Run(){		
		if(Super::hasParam("updateLastRevisedRecord")) Xbo::updateLastRevisedRecord();
		/* DRAWERS */
		if (Super::hasParam("getDrawerVendorData")) VendorDrawer::getDrawerData();
		if (Super::hasParam("getDrawerAssetData"))  AssetDrawer::getDrawerData();
		if (Super::hasParam("getDrawerEmployeeData")) EmployeeDrawer::getDrawerData();
		if (Super::hasParam("getDrawerItemData")) ItemDrawer::getDrawerData();
		if (Super::hasParam("getDrawerWorkTemplateData")) WorkTemplateDrawer::getDrawerData();
		/* END DRAWERS */

		/* Youtube URL */
		if(Super::hasParam("deleteYoutubeVideo")) Xbo::deleteYoutubeVideo();
		if(Super::hasParam("updateYoutubeVideo")) Xbo::updateYoutubeVideo();
		if(Super::hasParam("getSurveilanceVideos")) Xbo::getSurveilanceVideos();
		if(Super::hasParam("saveYoutubeVideos")) Xbo::saveYoutubeVideos();


		
		if(Super::hasParam("getDataURL")) XboSet:: getDataURL();
		if(Super::hasParam("syncAiLookup")) XboSet:: syncAiLookup();
		if(Super::hasParam("generalAIChat")) MaxesAI:: generalAIChat();
		if(Super::hasParam("generalAIChatStream")) MaxesAI:: generalAIChatStream();
		if (Super::hasParam("reloadKGCache")) MaxesAI::reloadKGCache();
		if(Super::hasParam("generalAIWelcomeScreen")) XboSet::generalAIWelcomeScreen();
		
		if(Super::hasParam("markNotifRead")) Xbo::markNotifRead();
		if(Super::hasParam("markAllNotifRead")) Xbo::markAllNotifRead();
		if(Super::hasParam("getUnreadNotificationCount")) Xbo::getUnreadNotificationCount();
		if(Super::hasParam("getAutoCompletion")) Xbo::getAutoCompletion();
		if(Super::hasParam("getBaseCurrency")) Xbo::getBaseCurrency();
		if(Super::hasParam("loaddata")) Super::Load();
		if(Super::hasParam("downloadloader")) Super::download_defDataLoader();
		
		if(isset($_POST['senddocuments']) && isset($_POST['owner']) && isset($_POST['documentremarks']) && isset($_POST['documentstatus'])  ) Super::senddocuments();	
		if(isset($_POST["setBarcodeItem"])) XboSet::SetBarcode(); 
		if(isset($_POST["updateMaxesProperties"])) XboSet::updateMaxesProperties(); 
		if(isset($_POST["bccheckresult"])) XboSet::BarcodeCheckResult(); 
		if(isset($_POST["getbarcodelist"])) XboSet::GetBarcodeList();
		if(isset($_POST['checkinternet'])) Super::CheckInternet();
		if(isset($_POST['renderqrdata'])) Super::renderQRData();
		if(isset($_POST['updateqrconfig'])) Super::UpdateQRConfig();
		if(isset($_POST['qrconfiginfo'])) Super::QRConfigInfo();
		if(isset($_POST['landpage'])) Super::LandPage();
		if(isset($_POST['mxsidebar'])) Super::GetSideBar();
		if(isset($_POST['linktable'])) XboSet::linkTable();
		if(isset($_POST['lookupentry'])) Super::entrylookup();
		if(isset($_POST['lookupentrymod'])) Super::entrylookupmod();
		if(isset($_POST["seedroot"])) Xbo::seedroot();
		if(isset($_POST["checksum"])) Xbo::checksum();
		
		if(isset($_FILES["fileToUpload"])) XboSet::fileUpload();
		if(isset($_FILES["rptToUpload"]["name"])) XboSet::rptUpload();
		
		if(isset($_GET["gendescription"])){
			if(isset($_GET["appname"])) Xbo::gendescription(null, $_GET["appname"]); 
			else Xbo::gendescription();
		}
		if(isset($_GET["crtbody"])) UI::crtbody();
		if(isset($_GET["chartport"])) UI::chartport();
		if(isset($_GET['recl'])) Xbo::recl();
		if(isset($_GET["getlanglat"])) Xbo::getlanglat();
		if(Super::hasParam("changePassword")) XboSet::changePassword();
		if(isset($_GET["getwhgl"])) Xbo::getwhgl();
		if(isset($_GET["getlocgl"])) Xbo::getlocgl();
		if(isset($_GET["getwogl"])) Xbo::getwogl();
		if(isset($_GET["getTaxValue"])) XboSet::getTaxValue();
		if(isset($_GET["isCondType"])) Xbo::isCondType();
		
		if(Super::hasParam("runReport")) XboSet::runReport();

		if(isset($_GET['runReportParam'])) XboSet::runReportParam();
		if(isset($_GET['delLookupPackage'] )) XboSet::delLookupPackage();
		if(isset($_GET["getDefaultInsertSite"])) Xbo::getDefaultInsertSite();
		if(Super::hasParam("getInitStatus"))XboSet::getInitStatus();
		if(isset($_GET["getNextVal"])) XboSet::getNextVal();
		if(isset($_GET["chorg"])) XboSet::chorg();
		if(isset($_GET["downloadFiles"])) XboSet::downloadFiles();
		if(isset($_GET['displayFiles'])) XboSet::displayFiles();
		if(isset($_GET['delFileUpload'])) XboSet::deleteFileUpload();
		if(isset($_GET["getkpiot"])) Xbo::getkpiot();
		if(isset($_GET['showprevrec'])) XboSet::showPrevRecord();	
		if(isset($_GET['shownextrec']))	XboSet::showNextRecord();
		if(isset($_GET["getLinenum"])) XboSet::getLinenum();
		
		if(Super::hasParam("getautonum")) Xbo::getautonum();

		if(isset($_GET["wautonum"])) {
			$a = new Super();
			echo $a->getAutoNumber($_GET["wautonum"])->autonumber;
		}
		
			
		if(isset($_GET["importxls"])) Super::importxls();
		
		if(isset($_POST['getFirstRecord']))  XboSet::getFirstRecord(); 
		if(isset($_POST['modChildPackage'])) XboSet::updateTableRowDetail();
		if(isset($_POST['delChildPackage'])) XboSet::deleteChildren();	
		if(isset($_POST["changesalesman"])) XboSet::changesalesman();
		if(isset($_POST['updateFileInfo'])) XboSet::updateFileInfo();
		if(isset($_POST["esigupdate"])) XboSet::EsigUpdate();
		if(isset($_POST["userlonglat"])) XboSet::UserLongLat();
		if(isset($_POST["avaimage"])) Xbo::AvaImage();
		if(isset($_POST["extendAttr"])) XboSet::extendAttr();
		if(isset($_POST["rollupkey"])) XboSet::rollupkey();
		if(isset($_POST["showcostrollup"])) XboSet::showcostrollup();
		if(isset($_POST["showptt"])) XboSet::showptt();
		if(isset($_POST['reportApp']) || isset($_GET['reportApp'])) XboSet::displayReportList();
		if(isset($_POST['tablecopy']))  {
			$super = new Super();
   			$super->copyChildrenTable($_POST['tablecopy']);
		}
		if(isset($_POST['deleteparent'])) XboSet::deleteParent();
		if(isset($_POST['changeStatus'])) XboSet::changeStatus();
		if(isset($_POST['actionid']))  ui::getactions();		
		if(isset($_POST['fxstable'])) Super::FixedSearch();
		if(isset($_POST['attkey'])) XboSet::attKey();
		if(isset($_POST["sapxhr"])) UI::sapxhr();
		if(isset($_POST['childRelColumn'])) Super::childColumnsRelationship();
		if(isset($_POST['loadprofile'])) XboSet::LoadProfile();
		if(isset($_POST['xhr_profile'])) XboSet::ProfileUpdate();
		if(isset($_POST['invested'])) XboSet::signout();
		if(isset($_POST['resetOwnerPassword']))  Xbo::resetOwnerPassword();
		if(Super::hasParam("logininfo"))  Xbo::logininfo();
		if(isset($_POST['recordOwner'])) XboSet::checkNewRecord();
		if(isset($_POST['getpapersetting'])) Xbo::getPaper();
		if(isset($_POST["getexchangerate"])) Xbo::getExchangeRate();
		if(isset($_POST["getConversionFactor"])) Xbo::getConversionFactor();
		if(isset($_POST["addBarcode"])) XboSet::addBarcode(); 		
		if(isset($_GET["icn"])){
			$icn = new Super();
			$icn->Xbor();
		}
		if(isset($_GET["inc"])){
			$inc = new Super();
			$inc->Xbor("inc");
		}
		if(isset($_GET["upreg"])){
			$prg = new Super();
			$prg->XPreg();
		}
		
		if(isset($_POST["showcomments"])) MaxesWorkflow::showcomments();
		if(isset($_POST["commentinput"])) MaxesWorkflow::commentinput();		
		if(isset($_POST["qqueryname"])) Super::savequery();
		if(isset($_POST["xxchildtable"]) && !isset($_POST["assignreports"])  ) MaxesWorkflow::SetRoles();
		if(isset($_POST["wfxboset"]) && !isset($_POST['xxtonodeline'])) {
			MaxesWorkflow::ProcessWorkflow($_POST);
		}
		if(isset($_POST["xxtonodeline"]) && isset($_POST["wfxboset"])) {
			MaxesWorkflow::ProcessWorkflow($_POST);
		}
		if(isset($_POST["viewwfhist"])){
			MaxesWorkflow::ViewWFHistory($_POST);
		}		
		if(isset($_POST["xxtotalleadtime"])) MaxesWorkflow::TotalLeadTime();
		if(isset($_POST["xxwfinbox"])) MaxesWorkflow::WFInbox();
		if(isset($_POST["xxnotifuserid"]))  MaxesWorkflow::CheckWFNotif();
		if(isset($_POST["xxxnotifuserid"]))  MaxesWorkflow::ShowWFNotif();
		//if(isset($_POST['getwfuserid'])) MaxesWorkflow::TotalAssignments();
		
		if(isset($_POST["viewdrilldown"])) XboSet::viewdrilldown();			
		if(isset($_POST["craddcost"])) XboSet::craddcost();
	}	
	
 }

Action::Run();

?>