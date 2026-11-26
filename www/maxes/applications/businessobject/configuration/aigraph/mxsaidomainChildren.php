<?php 
require_once ("../../eat.php");

class MxsAIDomainChildren {

	public static function init(){
		XboSet::validateChildren();	
		MxsAIDomainChildren::displayData();
	}
	
	public static function displayData() {
		$getOwner = XboSet::getOwner();	
		$tableid = Xbo::getTableID();
		
		$x = explode("=", $getOwner);
		$parentDomain = "mxsairelations.parentdomain=".$x[1];

		// 2. CHILD: Mapping Tabel ke Domain (Search Space Reducer)
		if($tableid == "mxtable_mxsaitabledomains_child"){
			Xbo::select("Table Name", "tablename");
			Xbo::select("Description", "mxsaitables.description");
			Xbo::select("Ontology", "mxsaitables.objectname");
			XboSet::getXboSet("mxsaitabledomains", $getOwner);
			XboSet::displayData(["mxsaitables.tablename = mxsaitabledomains.tablename"]);
		}


		if($tableid == "mxtable_mxsaicolumns_child"){
			Xbo::select("Table Name", "tablename");
			Xbo::select("Column", "columnname");
			Xbo::select("Description", "description");
			Xbo::select("Is Primary Key?", "isprimarykey");
			XboSet::getXboSet("mxsaicolumns", $getOwner);
			XboSet::displayData();
		}


		// 3. CHILD: Ontology / Relations (Constraint Engine)
		if($tableid == "mxtable_mxsairelations_child"){
			Xbo::select("Parent", "parenttable");
			Xbo::select("Child", "childtable");
			Xbo::select("Relation", "relationname");
			Xbo::select("Join Cond.", "joincondition");
			Xbo::select("Child Dom.", "childdomain");
			XboSet::getXboSet("mxsairelations", $parentDomain);
			XboSet::displayData();
		}

		// 4. CHILD: Tooling Registry (Function Calling)
		if($tableid == "mxtable_mxsaitools_child"){
			Xbo::select("Function Name", "toolname");
			Xbo::select("Description", "description");
			Xbo::select("URL Endpoint", "urlendpoint");
			XboSet::getXboSet("mxsaitools", $getOwner);
			XboSet::displayData();
		}

		// 5. CHILD: Industrial Formulas (KPI Logic)
		if($tableid == "mxtable_mxsaiformulas_child"){
			Xbo::select("Formula Name", "formulaname");
			Xbo::select("Description", "description");
			Xbo::select("Logic Instruction", "logicinstruction");
			XboSet::getXboSet("mxsaiformulas", $getOwner);
			XboSet::displayData();
		}

		// 6. CHILD: Developer Defined Queries (DDQ)
		if($tableid == "mxtable_mxsaiddq_child"){
			Xbo::select("Query Name", "queryname");	
			Xbo::select("Description", "description");
			Xbo::select("SQL Template", "sqltext");
			Xbo::select("Parameter Meta", "parammeta");
			XboSet::getXboSet("mxsaiddq", $getOwner);
			XboSet::displayData();
		}
	}
}

MxsAIDomainChildren::init();

?>