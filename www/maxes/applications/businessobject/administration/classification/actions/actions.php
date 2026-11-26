<?php
require_once ("../../../eat.php");

Class ActionSet {

    public static function addAttributes(){
		$payload = Super::getParam("addAttributes");
		$classificationcode = $payload["classificationcode"];
		$data = $payload["data"];

		$attrList = [];

		$attributes = [];
		foreach($data as $row){
			$attributeid = $row["attributeid"];
			$attrSet = (new Set())->useTransactionManager()->MboSetRemote("attribute")->where("attributeid = ?", $attributeid)->MboRemote("attributename, description, type")->getMboSet()->fetch();
			$attributes [] = [
				"attributename" => $attrSet["attributename"],
				"description" => $attrSet["description"],
				"type" => $attrSet["type"]
			];
		}

		$enterby = Xbo::getLoginUser();
		$enterdate = Xbo::setEnterDate();

		foreach($attributes as &$row){
			$row["classificationcode"] = $classificationcode;
			$row["enterby"] = $enterby;
			$row["enterdate"] = $enterdate;
		}

		
		(new Set())->MboSetRemote("classattribute")->addStream($attributes);
		Super::sendJsonResponse("success", "Attributes has been added to Classsification : $classificationcode");

	}

}
?>