<?php
require_once ("../../eat.php");

class PmHoliday extends Xbo {
	public $holidaycode;
    public function __construct() {      
        $this->holidaycode = Xbo::getString("holidaycode");
    }	
    public function __destruct() 
	{
        return null;
    }   
    public static function selectData() {      
        Xbo::select("ID", "pmholidayid");
        Xbo::select("Holiday Code", "holidaycode");
        Xbo::select("Description", "description");
        Xbo::select("Start Date", "startdate", "date"); 
        Xbo::select("End Date", "enddate", "date");
        Xbo::select("Type", "holidaytype");
    }
    public static function displayData() {
        PmHoliday::selectData(); 
        XboSet::getXboSet("pmholiday"); 
        renderData(); 
    }
    public static function init() {
        $get = new PmHoliday();		
        $holidaycode = $get->holidaycode;        
        if (isset($holidaycode) && !empty($holidaycode)) {
            XboSet::getXboSet("pmholiday", "holidaycode = '$holidaycode'");
            $result = XboSet::validate(); 
            if ($result->num_rows) {
                $exec = PmHoliday::changes();
            } else {
                $exec = PmHoliday::add();
            }
        }
		
        PmHoliday::displayData();
    }
	
    public static function add() {       
        XboSet::getXboSet("pmholiday");
        XboSet::add();
    }

    public static function changes() {
        $get = new PmHoliday();		
        $holidaycode = $get->holidaycode;	
        XboSet::getXboSet("pmholiday", "holidaycode = '$holidaycode'");		
        XboSet::changes();
    }
		   
    public static function showDetail() {
       XboSet::showDetail();
    }
}

PmHoliday::init();