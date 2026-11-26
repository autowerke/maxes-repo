<?php 
include ("../../eat.php");

class Qualification extends Xbo {
	
	protected $qualification;
	protected $description;
	protected $skill;
	protected $certificate;
		
	public function __construct(){
		$this->qualification = Xbo::getString("qualification");
		$this->description = Xbo::getString("description");	
		$this->skill =  Xbo::getString("skill");
		$this->certificate = Xbo::getString("certificate");
		
	}
	public function __destruct()
    {
        return null;
    }
	
	public static function selectData(){
		Xbo::select("Qualification", "qualification");
		Xbo::select("Description", "description");
		Xbo::select("Skill", "skill");
		Xbo::select("Sertifikasi", "certificate");
	}
	public static function displayData(){
		Qualification::selectData();
		$whereclause = Super::IDWhereClause("qualificationid");
		XboSet::getXboSet("qualification", $whereclause);
		renderData();
	}
	
	public static function init(){
		
		$get = new Qualification();		
		$qualification = $get->qualification;
		if(isset($qualification)) {
			XboSet::getXboSet("qualification", "qualification = '$qualification'");
			$result = Xboset::validate();
			if ($result->num_rows){
				$exec = Qualification::changes();
				}else{
				$exec = Qualification::add();
			}
		}
		
		Qualification::displayData();
		
	}
	
	
	public static function add(){
		
		$get = new Qualification();
		
		$qualification = $get->qualification;
		$description = $get->description;	
		$skill = $get->skill;
		$certificate = $get->certificate;
		
		XboSet::getXboSet("qualification");
		
		Xbo::setValue("qualification", $qualification);
		Xbo::setValue("description", $description);
		Xbo::setValue("skill", $skill);
		Xbo::setValue("certificate", $certificate);
		
		XboSet::add();
		
	}

	public static function changes(){
		
		$get = new Qualification();
		
		$qualification = $get->qualification;
		$description = $get->description;	
		$skill = $get->skill;
		$certificate = $get->certificate;
		
		XboSet::getXboSet("qualification", "qualification = '$qualification'");
		Xbo::setValue("qualification", $qualification);
		Xbo::setValue("description", $description);
		Xbo::setValue("skill", $skill);
		Xbo::setValue("certificate", $certificate);
		
		XboSet::changes();
			
		}
		
	public static function showDetail(){
		XboSet::showDetail();
	}
}

Qualification::init();




?>