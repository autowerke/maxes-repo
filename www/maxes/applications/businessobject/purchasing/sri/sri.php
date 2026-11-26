<?php
require_once ("../../eat.php");

class SRI extends Xbo {

    protected $orgcode;
    public $srinum;
    public function __construct(){
        $this->srinum = Xbo::getString("srinum");
    }
    public function __destruct()
    {
        return null;
    }

    public static function selectData(){
        Xbo::select("SRI Number", "srinum"); // Lebih deskriptif
        Xbo::select("Description", "description");
        Xbo::select("Status", "status");
        Xbo::select("User", "enterby"); // User yang membuat
        Xbo::select("PO Number", "ponum"); // Lebih deskriptif
        Xbo::select("PO Date", "podate", "date"); // Lebih deskriptif

    }
    public static function displayCols(){
        $defaultjoin = null;
        if(isset($_GET['mxsdvsearch'])){
            $a = new Super();
            $b = $a->getWhere($_GET['mxsdvsearch'], "sri");
            $jointable = $b->join;
            $where = $b->where;
            SRI :: selectData();
            XboSet::getXboSet("sri", $where);
            renderData($defaultjoin, $jointable);
        }else{
            SRI :: selectData();
            $whereclause = Super::IDWhereClause("sriid");
            XboSet::getXboSet("sri", $whereclause);
            renderData($defaultjoin);
        }
    }

    public static function init(){
        $get = new SRI();
        $srinum = $get->srinum;
        if(isset($srinum)) {
            XboSet::getXboSet("sri", "srinum = '$srinum'");
            $result = Xboset::validate();
            if ($result->num_rows){
                $exec = SRI::changes();
            }else{
                $exec = SRI::add();

            }
        }

        SRI::displayCols();
    }


    public static function add(){
        XboSet::getXboSet("sri");
        XboSet::add();
    }

    public static function changes(){
        $get = new SRI();
        $srinum = $get->srinum;
        XboSet::getXboSet("sri", "srinum='$srinum'");
        XboSet::changes();
    }

    public static function showDetail(){
        XboSet::showDetail();
    }
}

SRI::init();

?>