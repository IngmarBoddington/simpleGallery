<?php


$string = '';
foreach($_SERVER as $key => $value) {
	$string .= $key . "\t\t#\t" . $value . '<br />' . PHP_EOL;
}




file_put_contents($_SERVER['HTTP_X_FILE_NAME'],file_get_contents('php://input'));

$finfo = new finfo(FILEINFO_MIME);
$string .= $finfo->file($_SERVER['HTTP_X_FILE_NAME']) . PHP_EOL;

file_put_contents("log.txt",$string);
echo $string;