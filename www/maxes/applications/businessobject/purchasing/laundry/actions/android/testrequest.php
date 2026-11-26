<?php
require_once("actions.php");
header('Access-Control-Allow-Origin: *');

$_POST["minid"] = 0;
$_POST["length"] = 10;
$_POST["getNota"] = "all";
ActionSet::getNota($_POST["minid"],$_POST["length"]);



?>