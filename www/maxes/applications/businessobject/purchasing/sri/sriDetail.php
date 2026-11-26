<?php
include ("../../eat.php");

class SRIDetail  {
    public static function showDetail(){
        XboSet::getXboSet("sri");
        XboSet::showDetail();
    }
}

SRIDetail::showDetail();