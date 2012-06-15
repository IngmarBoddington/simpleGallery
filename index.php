<?php

//Autoloader
function __autoload($className) {
	if (is_file('classes/class.'.strtolower($className).'.php')) {
		include('classes/class.'.strtolower($className).'.php');
	}
}


/**
 * determine the directory the gallery is installed in...
 */
$installDirectory = str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);


/**
 * breakdown request in order to route...
 */
$page_request = str_ireplace($installDirectory, '', strip_tags($_SERVER['REQUEST_URI'])); //strips any html
while(stripos($page_request, '../') !== false) { //checks for any "../" in the request and strips out. This prevents unwanted directory navigation
	$page_request = str_ireplace('../', '', $_SERVER['REQUEST_URI']);
}
$page_request = trim($page_request, '/'); //trim any remaining leading or trailing slashes
$page_request = explode("/",$page_request); //explodes the request into an array



/**
 * grab a list of all top level directories in the images gallery
 * each folder is it's own gallery with containing thumbnail folders and primary images 
 */
$galleryArray = array();
foreach(glob("images/*") as $filename) {
    if(is_dir($filename)) {
        $galleryArray[] = $filename;
    }
}


/**
 * check if a gallery has been requested, if so we need to grab the images...
 */
if($page_request[0] != '') {
	$imageArray = array();
	foreach(glob("images/".$page_request[0]."/200/*") as $filename) {
		$imageArray[] = str_replace("images/".$page_request[0]."/200/",'',$filename);
	}
}


/**
 * create new html page template
 */
$page = New page();


/**
 * build navigation list using default "home" link plus a link to each gallery...
 */
$galleryNavigation = '
	<nav role="navigation">
		<ul>
			<li><a href="' . $installDirectory . '">Home</a></li>' . PHP_EOL;
foreach($galleryArray as $gallery) {
	$galleryNavigation .= '<li><a href="' . $installDirectory . str_ireplace('images/','', $gallery) . '">' . str_ireplace('images/','', $gallery) . '</a></li>' . PHP_EOL;
}
$galleryNavigation .= '
		</ul>
	</nav>' . PHP_EOL;

$page->navigation = $galleryNavigation;


$page->content .= '<article>' . PHP_EOL;


/**
 * display description.txt contents if available...
 */
if(isset($imageArray)) {
	$filename = 'images/' . $page_request[0] . '/description.txt';
	if(is_file($filename)) {
		$page->content .= '<div>' . htmlentities(file_get_contents($filename)) . '</div>';
	}
}



/**
 * display images as required
 */
if(isset($imageArray)) {
	$images = '<div class="imgContainer">' . PHP_EOL;
	$imageCount = count($imageArray);
	$offset = (int) $page_request[1];
	$imagesToDisplay = 30 + $offset;	
	for($i = $offset; $i < $imagesToDisplay; $i++) {
		if(isset($imageArray[$i])) {
			$images .= '<div class="imgBox"><img src="' . $installDirectory . "images/" . $page_request[0] . "/200/" . $imageArray[$i] . '" /></div>' . PHP_EOL;
		}
	}
	$images .= '</div>' . PHP_EOL;
	$page->content .= $images;

	//check if pagination is required...
	if($imageCount > 30) {
		$pages = ceil($imageCount / 30);
		$page->content .= '<ul class="pagination">' . PHP_EOL;
		for($i = 0; $i < $pages; $i++) {
			$pageNumber = $i;
			$offset = ($pageNumber * 30);
			$pageNumber++;
			$page->content .= '<li><a href="' . $installDirectory . str_ireplace('images/','', $page_request[0]) . '/' . $offset . '">' . $pageNumber . '</a></li>' . PHP_EOL;
		}
		$page->content .= '</ul>' . PHP_EOL;
	}
}

$page->content .= '</article>' . PHP_EOL;