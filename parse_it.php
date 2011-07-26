<?php

include_once(dirname(__FILE__)."/Webpage_parser.php");
if ( $argc >= 3){
	$input = $argv[1];
	$output = $argv[2];
}else{
	$input = "app.html";
	$output = "parse.txt";
}
$N = new MacUknowParser($input, $output);
//$N = new iapp($input,$output);
$N->Parse();
$N->WriteToFile();

?>
