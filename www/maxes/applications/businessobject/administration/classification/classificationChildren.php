<?php 
require_once ("../../eat.php");

class Attributes extends classificationChildren {
	public static function addAttributes(){
	
		if(Super::hasParam("childPackage")){
			
			$payload = Super::getParam("childPackage");
			$attributename = $payload["attributename"];
			$description = $payload["description"];

			$attributeSet = (new Set())->useTransactionManager()->MboSetRemote("attribute")->where("attributename=?", $attributename)->getMboSet();

			if($attributeSet->isEmpty()){
				(new Set())->useTransactionManager()->MboSetRemote("attribute")
				->setValue("attributename", $attributename)
				->setValue("description", $description)->add();
			}
		}
		
	}
}

class classificationChildren {

	public static function init(){
		
		Attributes::addAttributes();
		XboSet::validateChildren();	
		classificationChildren::displayData();

	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		Xbo::select("Attribute Name", "attributename");
		Xbo::select("Description", "attribute.description");
		XboSet::getXboSet("classattribute", $getOwner);
		XboSet::displayData(["classattribute.attributename = attribute.attributename"]);
	}
}

classificationChildren::init();

?>