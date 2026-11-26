<?php 
require_once ("../../eat.php");


class PMChildren {

	public static function init(){
		XboSet::validateChildren();	
		PMChildren::displayData();
		
	}
	public static function displayData(){
		$getOwner = XboSet::getOwner();
		$tableid = Xbo::getTableID();
		$linktable = Super::getParam("linktable");
		
		if($tableid=="mxtable_pmwoline_child"){
			Xbo::select("Line", "pmwolinenum");
			Xbo::select("WO", "wonum");
			Xbo::select("Description", "Description");
			Xbo::select("Targ. Start Date", "targstartdate");
			Xbo::select("Team", "teamnum");
			Xbo::select("Remarks", "remarks");
			Xbo::select("Leader", "leader");
			Xbo::select("Resource Remarks", "remarks2");
			Xbo::select("Race Remarks", "raceremarks");
			Xbo::select("Generated?", "isgenerated");
			XboSet::getXboSet("pmwoline", $getOwner);
			XboSet::displayData();
		}
		if($tableid=="mxtable_pmjpline_child"){
			Xbo::select("Job Plan#", "wtnum");
			Xbo::select("WO#", "wonum");
			Xbo::select("Target Start", "pmwoline.targstartdate");
			Xbo::select("Leader", "pmwoline.leader");
			Xbo::select("Sequence", "sequence");
			XboSet::getXboSet("pmjpline", $getOwner);
			XboSet::displayData(["pmjpline.wonum = pmwoline.wonum"]);
		}
		
		if($tableid == "mxtable_pmjprule_child"){
			Xbo::select("Job Plan#", "wtnum");
			Xbo::select("Rule", "ruletype");
			Xbo::select("Frequency", "frequencyvalue");
			Xbo::select("Specific Date", "specificdate");
			Xbo::select("Sequence", "sequence");
			Xbo::select("Behaviour", "behaviour");
			XboSet::getXboSet("pmjprule", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid == "mxtable_pmmeterrule_child"){
			Xbo::select("Meter Name", "meternum");
			Xbo::select("Description", "meter.description");
			Xbo::select("Operator", "operator");
			Xbo::select("Trigger Point", "triggerpoint");
			Xbo::select("Trigger Point To", "triggerpoint2");
			Xbo::select("Auto Reserved Material?", "autoreserve");
			XboSet::getXboSet("pmmeterrule", $getOwner);
			XboSet::displayData(["meter.meternum = pmmeterrule.meternum"]);
		}
		if($tableid == "mxtable_pmhealthrule_child"){
			
			Xbo::select("Operator", "operator");
			Xbo::select("Trigger Point", "triggerpoint");
			Xbo::select("Lower Reset", "lowerreset");
			Xbo::select("Upper Reset", "upperreset");
			Xbo::select("Auto Reserved Material?", "autoreserve");
			XboSet::getXboSet("pmhealthrule", $getOwner);
			XboSet::displayData();
		}
		
		if($tableid == "mxtable_pmmeterfreq_child"){
			Xbo::select("Meter Name", "meternum");
			Xbo::select("Description", "meter.description");
			Xbo::select("Frequency", "frequency");
			Xbo::select("Alert Lead (days))", "alertlead");
			Xbo::select("Generate WO Ahead (days)", "generatewoahead");
			Xbo::select("Auto Reserved Material?", "autoreserve");
			Xbo::select("Is Active?", "isactive");
			XboSet::getXboSet("pmmeterfreq", $getOwner);
			XboSet::displayData(["meter.meternum = pmmeterfreq.meternum"]);
		}
		
		
		if(!empty($linktable)){	
		
			$where = Xbo::getLinkTableWhere($linktable); 
			$tableid = Xbo::getLinkTableName($linktable);
			if($tableid=="mxtable_pmmeterrulejp_child"){
				self::PmMeterRuleJP($where);
				return;
			}
			
			if($tableid=="mxtable_pmhealthrulejp_child"){
				self::PmHealthRuleJP($where);
				return;
			}
			if($tableid=="mxtable_pmmeterfreqjp_child"){
				self::PmMeterFrequencyJP($where);
				return;
			}
		}else{
			if($tableid == "mxtable_pmmeterrulejp_child"){
				self::PmMeterRuleJP($getOwner);
				return;
			}
			if($tableid == "mxtable_pmhealthrulejp_child"){
				self::PmHealthRuleJP($getOwner);
				return;
			}
			if($tableid == "mxtable_pmmeterfreqjp_child"){
				self::PmMeterFrequencyJP($getOwner);
				return;
			}
			
		}
	}
	public static function PmMeterFrequencyJP($where){
	
		Xbo::select("Rule ID#", "pmmeterfreqid");
		Xbo::select("Meter", "pmmeterfreq.meternum");
		Xbo::select("Sequence", "sequence");
		Xbo::select("Description", "worktemplates.description");
		Xbo::select("Job Plan#", "wtnum");
		Xbo::select("Team", "teamnum");
		Xbo::select("Leader", "leader");
		Xbo::select("GL Account", "glaccount");
		XboSet::getXboSet("pmmeterfreqjp", $where);
		XboSet::displayData(["pmmeterfreqjp.pmmeterfreqid = pmmeterfreq.pmmeterfreqid", "pmmeterfreqjp.wtnum = worktemplates.wtnum"]);
	}
	
	public static function PmMeterRuleJP($where){
		Xbo::select("Rule ID#", "pmmeterrulejpid");
		Xbo::select("Sequence", "sequence");
		Xbo::select("Job Plan#", "wtnum");
		Xbo::select("Description", "worktemplates.description");
		Xbo::select("Team", "teamnum");
		Xbo::select("Leader", "leader");
		Xbo::select("GL Account", "glaccount");
		XboSet::getXboSet("pmmeterrulejp", $where);
		XboSet::displayData(["pmmeterrulejp.wtnum = worktemplates.wtnum"]);
	}
	
	public static function PmHealthRuleJP($where){
		Xbo::select("Rule ID#", "pmhealthruleid");
		Xbo::select("Sequence", "sequence");
		Xbo::select("Job Plan#", "wtnum");
		Xbo::select("Description", "worktemplates.description");
		Xbo::select("Team", "teamnum");
		Xbo::select("Leader", "leader");
		Xbo::select("GL Account", "glaccount");
		XboSet::getXboSet("pmhealthrulejp", $where);
		XboSet::displayData(["pmhealthrulejp.wtnum = worktemplates.wtnum"]);
	}
	
}

PMChildren::init();

?>