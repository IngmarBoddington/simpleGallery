<?php


$galleryArray = array();
foreach(glob("images/*") as $filename) {
    if(is_dir($filename)) {
        $galleryArray[] = $filename;
    }
}
sort($galleryArray);



$page_request = strip_tags($_SERVER['REQUEST_URI']); //strips any html

//checks for any "../" in the request and strips out. This prevents unwanted directory navigation
while(stripos($page_request, '../') !== false) {
	$page_request = str_ireplace('../', '', $_SERVER['REQUEST_URI']);
}
$page_request = trim($page_request, '/'); //trim any remaining leading or trailing slashes
$page_request = explode("/",$page_request); //explodes the request into an array
$page_request = $page_request[0]; //grabs just the first bit



$imageArray = array();
foreach(glob("images/".$page_request."/*_200.jpg") as $filename) {
	$imageArray[] = str_ireplace("_200.jpg",".jpg",$filename);
}


echo '<pre>'; print_r($galleryArray); echo '</pre>';

echo '<hr />';

echo '<pre>'; print_r($imageArray); echo '</pre>';