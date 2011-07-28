<?php

include_once(dirname(__FILE__)."/Webpage_parser.php");
if ( $argc >= 3){
	$output = $argv[1];
	$type = intval($argv[2]);
}else{
	$output = "urls_iapp/nutch";
	$type = 1;
}
$N = NULL;
switch ($type){
case 0:
	$ret = MacUknow::GenerateNutchSeedURL($output);
	if ($ret == false){
		return -1;
	}
	break;
case 1:
	$ret = iapp::GenerateNutchSeedURL($output);
	if ($ret == false){
		return -1;
	}
	break;
default:
	echo "none of parser\n";
	break;
}

?>
