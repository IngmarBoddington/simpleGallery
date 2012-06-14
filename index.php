<?php


$galleryArray = array();
foreach(glob("images/*") as $filename) {
    if(is_dir($filename)) {
        $galleryArray[] = $filename;
    }
}
sort($galleryArray);


$installDirectory = str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);
$page_request = str_ireplace($installDirectory, '', strip_tags($_SERVER['REQUEST_URI'])); //strips any html

//checks for any "../" in the request and strips out. This prevents unwanted directory navigation
while(stripos($page_request, '../') !== false) {
	$page_request = str_ireplace('../', '', $_SERVER['REQUEST_URI']);
}
$page_request = trim($page_request, '/'); //trim any remaining leading or trailing slashes
$page_request = explode("/",$page_request); //explodes the request into an array
$page_request = $page_request[0]; //grabs just the first bit



$imageArray = array();
foreach(glob("images/".$page_request."/200/*") as $filename) {
	$imageArray[] = $filename;
}

echo '<!DOCTYPE html> 
<html> 
	<head> 
	<title>default</title>
	<meta charset="utf-8" />
	<meta name="keywords" content="default" />
	<meta name="description" content="default" />
	<meta name="robots" content="index,follow" />
	<link rel="stylesheet" type="text/css" href="' . $installDirectory . 'styles.css" />
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.7.1/jquery.min.js"></script>

	</head> 
 
<body>
<nav role="navigation">
	<ul>
		<li><a href="' . $installDirectory . '">Home</a></li>';

foreach($galleryArray as $gallery) {
	echo '<li><a href="' . $installDirectory . str_ireplace('images/','', $gallery) . '">' . str_ireplace('images/','', $gallery) . '</a></li>' . PHP_EOL;
}

$images = '';
foreach($imageArray as $image) {
	$images .= '<div class="imgBox"><img src="' . $image . '" /></div>' . PHP_EOL;
}

echo '
	<ul>
</nav>

<article>
	<header>
		<h2>Simple Gallery</h2>	
	</header>
	
	<p>Pick an option from the menu on the left to use the gallery.</p>
	 ' . $images . '
</article>';


echo '</body>
</html>';