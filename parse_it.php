<?php

include_once(dirname(__FILE__)."/Webpage_parser.php");
if ( $argc >= 4){
	$input = $argv[1];
	$output = $argv[2];
	$type = intval($argv[3]);
}else{
	$input = "app.html";
	$output = "parse.txt";
	$type = 1;
}
$N = NULL;
switch ($type){
case 0:
	$N = new MacUknowParser($input, $output);
	break;
case 1:
	$N = new iappParser($input,$output);
	break;
default:
	echo "none of parser\n";
	break;
}
if ($N->Parse()){
	$N->WriteToFile();
}

?>
