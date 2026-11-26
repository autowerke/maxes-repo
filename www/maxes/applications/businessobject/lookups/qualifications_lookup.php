<?php 
include ("../eat.php");

class QualificationLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
					
		Xbo::select("Qualification", "qualification");
		Xbo::select("Description", "description");
		Xbo::select("Skill", "skill");
		XboSet::getXboSet("qualification");
		XboSet::displayData();
	}
	public static function init(){
		QualificationLookup::displayData();
	}
	
}
QualificationLookup::init();
?>