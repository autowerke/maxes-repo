<?php 
include ("../eat.php");

class ItemGroupLookup extends Xbo {

	public function __destruct()
    {
        return null;
    }
	public static function displayData(){
		
		Xbo::select("Merek", "brand");
		Xbo::select("Type", "type");
		Xbo::select("Panjang", "width");
		Xbo::select("Lebar", "height");
		Xbo::select("Jml. Kolom", "colsqty", "decimal");
		Xbo::select("Jml. Baris", "rowsqty", "decimal");
		Xbo::select("Batas Tepian Atas (margin atas)", "margintop");
		Xbo::select("Batas Tepian Kiri (margin kiri)", "marginleft");
		Xbo::select("Jarak antar Label(At-Bw)", "secmargintop");
		Xbo::select("Jarak antar Label(ki-kan)", "secmarginright");
		
		Xbo::select("Padding", "padding");
		Xbo::select("Default?", "isdefault");
		XboSet::getXboSet("papersetting");
		XboSet::displayData();
	}
	public static function init(){
		ItemGroupLookup::displayData();
	}
	
}
ItemGroupLookup::init();
?>