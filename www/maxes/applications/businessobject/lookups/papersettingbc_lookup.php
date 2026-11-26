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
		Xbo::select("Margin Top", "margintop");
		Xbo::select("Margin Left", "marginleft");
		Xbo::select("Margin Vertical", "secmargintop");
		Xbo::select("Margin Horizontal", "secmarginright");
		
		Xbo::select("Padding", "padding");
		Xbo::select("Default?", "isdefault");
		XboSet::getXboSet("papersettingbc");
		XboSet::displayData();
	}
	public static function init(){
		ItemGroupLookup::displayData();
	}
	
}
ItemGroupLookup::init();
?>