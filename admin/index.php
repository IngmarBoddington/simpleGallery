<?php

session_start();
unset($_SESSION['uploaderStatus']);

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
if(($requestStringEnd = stripos($page_request, '?')) !== false) {
	$page_request = substr($page_request,0,$requestStringEnd); //strips anything after the ?
}
while(stripos($page_request, '../') !== false) { //checks for any "../" in the request and strips out. This prevents unwanted directory navigation
	$page_request = str_ireplace('../', '', $_SERVER['REQUEST_URI']);
}
$page_request = trim($page_request, '/'); //trim any remaining leading or trailing slashes
$page_request = explode("/",$page_request); //explodes the request into an array


/**
 * create new html page template
 */
$page = New page();



/**
 * build navigation list using default "home" link plus a link to each gallery...
 */
$galleryNavigation = '
		<ul>
			<li><a href="' . $installDirectory . '">Home</a></li>
			<li><a href="' . $installDirectory . 'upload">Uploader</a></li>
			<li><a href="' . $installDirectory . 'settings">Settings</a></li>
			<hr />
			<li><a href="' . str_ireplace('admin/', '', $installDirectory) . '" target="_blank">View Gallery</a></li>
		</ul>' . PHP_EOL;

$page->navigation = $galleryNavigation;




/******************************************************************************************************************************/
/*                                                                                                                            */
/*                                               Start POST / GET handling                                                    */
/*                                                                                                                            */
/******************************************************************************************************************************/


function recursiveRmDir($dir) {
	$dir = trim($dir, '/'); //trims any excess '/', as a bonus it also prevents absolute paths
	if(($directoryContents = glob($dir . '/*')) !== false) {
		//directory has contents, so recursively scan directory and delete files and subfolders
		foreach($directoryContents as $item) {
			if(is_file($item)) {
				unlink($item);
			}
			if(is_dir($item)) {
				recursiveRmDir($item);
				rmdir($item);
			}
		}
	} 
	rmdir($dir);
}

/**
 * delete directory - recursively scan directory and delete files and subfolders
 */
if(isset($_GET['deleteDirectory'])) {
	recursiveRmDir('../images/' . $_GET['deleteDirectory']);
}



/**
 * image rotation and deletion requests...
 */
if($page_request[0] == 'upload' && isset($page_request[1])) {
	if(isset($_GET['rotatec'])) {
		if(is_file("../images/"  .$page_request[1] . '/' . $_GET['rotatec'])) {
			$rotating = New image("../images/"  .$page_request[1] . '/' . $_GET['rotatec']);
			$rotating->rotate(270);
		} else {
			echo '<p>' . "../images/"  .$page_request[1] . '/' . $_GET['rotatec'] . '</p>';
		}
	}

	if(isset($_GET['rotateac'])) {
		if(is_file("../images/"  .$page_request[1] . '/' . $_GET['rotateac'])) {
			$rotating = New image("../images/"  .$page_request[1] . '/' . $_GET['rotateac']);
			$rotating->rotate(90);
		}  else {
			echo '<p>' . "../images/"  .$page_request[1] . '/' . $_GET['rotatec'] . '</p>';
		}
	}

	if(isset($_GET['delete'])) {
		if(is_file($_SERVER['DOCUMENT_ROOT'] . str_ireplace('admin/', '', $installDirectory) . "images/"  .$page_request[1] . '/' . $_GET['delete'])) {
			unlink($_SERVER['DOCUMENT_ROOT'] . str_ireplace('admin/', '', $installDirectory) . "images/"  .$page_request[1] . '/' . $_GET['delete']);
			unlink($_SERVER['DOCUMENT_ROOT'] . str_ireplace('admin/', '', $installDirectory) . "images/"  .$page_request[1] . '/200/' . $_GET['delete']);
			unlink($_SERVER['DOCUMENT_ROOT'] . str_ireplace('admin/', '', $installDirectory) . "images/"  .$page_request[1] . '/800/' . $_GET['delete']);
		}  else {
			echo '<p>' . $_SERVER['DOCUMENT_ROOT'] . str_ireplace('admin/', '', $installDirectory) . "images/"  .$page_request[1] . '/' . $_GET['delete'] . '</p>';
		}
	}
}




/**
 * Installation form. Displays when configuration files do not yet exist.
 */
if( (!file_exists('.htpasswd') || !file_exists('.htaccess') || !file_exists('../.htaccess') )
		&& !isset($_POST['Username'])) {
	$page->content = '
	<article>
		<h2>Installation</h2>

		<p>Congratulations on uploading the simple gallery. To secure the admin section please enter a username and password below. These will be required to access the admin in future.</p>

		<form action="index.php" method="post" name="Config">
			<fieldset>
				<label for="Username">Admin Username</label>
				<input type="text" id="Username" name="Username" class="form-text">
			</fieldset>
		
			<fieldset>
				<label for="Password">Password</label>
				<input type="password" id="Password" name="Password" class="form-text">
			</fieldset>
		
			<fieldset class="form-actions">
				<input type="submit" value="Submit">
			</fieldset>
		</form>
	</article>
	';
}



/**
 * process username and password postings into the appropriate htaccess and htpasswd files used to secure the admin
 */
if(isset($_POST['Username'])) {
	$username = $_POST['Username'];
	$password = $_POST['Password'];

	$installDirectory = str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);

	$htaccess = '
			AuthName "Gallery Administration"
			AuthType Basic
			AuthUserFile ' . $_SERVER['DOCUMENT_ROOT'] . $installDirectory . '.htpasswd
			AuthGroupFile /dev/null
			<Limit GET>
			require valid-user
			</Limit>
			<IfModule mod_rewrite.c>
			RewriteEngine On
			RewriteBase /
			RewriteRule ^index\.php$ - [L]
			RewriteCond %{REQUEST_FILENAME} !-f
			RewriteCond %{REQUEST_FILENAME} !-d
			RewriteRule . ' . $installDirectory . 'index.php [L]
			</IfModule>';

	$root_htaccess = '
			<IfModule mod_rewrite.c>
			RewriteEngine On
			RewriteBase /
			RewriteRule ^index\.php$ - [L]
			RewriteCond %{REQUEST_FILENAME} !-f
			RewriteCond %{REQUEST_FILENAME} !-d
			RewriteRule . ' . str_ireplace('/admin','',$installDirectory) . 'index.php [L]
			</IfModule>';

	$password = base64_encode(sha1($password, true));
	if(file_put_contents('.htpasswd', "$username:{SHA}$password\n")) {
		if(file_put_contents('.htaccess', $htaccess)) {
			if(file_put_contents('../.htaccess', $root_htaccess)) {
				header('Location: index.php');
			} else {
				$page->content = '<article><p class="error">Could not create .htaccess file in installation directory. Please check file permissions. <a href="' . $installDirectory . '">Retry</a></p></article>';
			}
		} else {
			$page->content = '<article><p class="error">Could not create admin .htaccess file in admin directory. Please check file permissions. <a href="' . $installDirectory . '">Retry</a></p></article>';
		}
	} else {
		$page->content = '<article><p class="error">Could not create admin .htpasswd file in admin directory. Please check file permissions. <a href="' . $installDirectory . '">Retry</a></p></article>';
	}
}



/**
 * New gallery creation
 */
if(isset($_POST['newGallery'])) {
	//strip unwanted characters from gallery name
	$galleryName = str_replace(' ', '-', $_POST['newGallery']);
	$galleryName = preg_replace('/[^A-Za-z0-9 -]/', '', $galleryName);
	
	//TODO - improve description validation
	$description = $_POST['Description'];

	if(is_dir('../images/' . $galleryName)) {
		$page->body = '<p class="error">Could not create "' . $galleryName . '" as it already exists. <a href="' . $installDirectory . 'upload">Retry</a></p>';
		exit();
	} else {
		//TODO - mkdir fault handling
		mkdir('../images/' . $galleryName);
		chmod('../images/' . $galleryName, 0777);
		file_put_contents('../images/' . $galleryName . '/description.txt', $description);
		mkdir('../images/' . $galleryName . '/200');
		chmod('../images/' . $galleryName . '/200', 0777);
		mkdir('../images/' . $galleryName . '/800');
		chmod('../images/' . $galleryName . '/800', 0777);
	}
}




/******************************************************************************************************************************/
/*                                                                                                                            */
/*                                               End POST / GET handling                                                      */
/*                                                                                                                            */
/******************************************************************************************************************************/




/**
 * basic settings configuration
 */
if($page_request[0] == 'settings') {
	$page->content .= '
	<article>
		<h2>Settings</h2>
		
		<form action="settings" method="post" name="Config">
			<fieldset>
				<label for="Username">Admin Username</label>
				<input type="text" id="Username" name="Username" class="form-text">
			</fieldset>
		
			<fieldset>
				<label for="Password">Password</label>
				<input type="password" id="Password" name="Password" class="form-text">
			</fieldset>
		
			<fieldset class="form-actions">
				<input type="submit" value="Submit">
				<p class="form-help">Please be aware that changes are saved straight away and you will need to enter the new credentials straight away.</p>
			</fieldset>
		</form>
	</article>
	';
}


/**
 * upload menu with no gallery selected:
 * 		- offer gallery list to edit
 * 		- offer form for new gallery
 */
if($page_request[0] == 'upload' && !isset($page_request[1])) {
	$galleryDirectory = '../images/';

	$galleries = glob('*', $galleryDirectory);

	// Open a known directory, and proceed to read its contents
	$galleriesList = '';
	if (is_dir($galleryDirectory)) {
		if (($dh = opendir($galleryDirectory)) !== false) {
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..' && is_dir($galleryDirectory . $file)) {
					//TODO - gallery editing
					//	- rename gallery
					//	- list gallery images
					//		- remove image
					//		- rotate image (http://uk3.php.net/imagerotate)
					//	- remove gallery
					$galleriesList .= '	Gallery: <a href="upload/' . $file . '">' . $file . '</a><br />' . PHP_EOL;
				}
			}
			closedir($dh);
		}
	}

	//new gallery form
	$form = file_get_contents('forms/newgallery.php');
	$page->content .= '
	<article>
		<h2>Upload</h2>
		
		<p>Select the gallery you wish to upload to or create a new one using the provided form:</p>
		' . $galleriesList . '
		' . $form . '
	</article>';
}



/**
 * upload form
 */
if($page_request[0] == 'upload' && isset($page_request[1])) {
	//TODO - convert to class based templating
	$page->content .= file_get_contents('forms/upload.php');
	$page->content = str_ireplace('##INSTALLDIRECTORY##', $installDirectory, $page->content);
	$page->content = '<article>' . str_ireplace('##GALLERY##', $page_request[1], $page->content);
	
	//displayGallery...
	if($page_request[1] != '') {
		$imageArray = array();
		foreach(glob("../images/".$page_request[1]."/200/*") as $filename) {
			$imageArray[] = str_replace("../images/".$page_request[1]."/200/",'',$filename);
		}
		if(isset($imageArray)) {
			$images = '<div class="imgContainer">' . PHP_EOL;
			$imageCount = count($imageArray);
			for($i = 0; $i < $imageCount; $i++) {
				if(isset($imageArray[$i])) {
					$images .= str_replace('admin/','','<div class="imgBox"><img src="' . $installDirectory . "images/" . $page_request[1] . "/200/" . $imageArray[$i] . '?mt='.filemtime("../images/" . $page_request[1] . "/200/" . $imageArray[$i]).'" />') . PHP_EOL;
					$images .= '<a href="?rotatec=' . $imageArray[$i] . '"><img src="http://cdn1.iconfinder.com/data/icons/silk2/arrow_turn_right.png" /></a>' . PHP_EOL;
					$images .= '<a href="?rotateac=' . $imageArray[$i] . '"><img src="http://cdn1.iconfinder.com/data/icons/silk2/arrow_turn_left.png" /></a>' . PHP_EOL;
					$images .= '<a href="?delete=' . $imageArray[$i] . '"><img src="http://cdn1.iconfinder.com/data/icons/silk2/cross.png" /></a></div>' . PHP_EOL;
				}
			}
			$images .= '</div>' . PHP_EOL;
			$page->content .= $images;
		}
	}
	$page->content .= '</article>' . PHP_EOL;
}


/**
 * Home page
 */
if($page_request[0] == '') {
	$page->content .= '
	<article>		
		<h2>Home</h2>
		<p>Pick an option from the menu on the left to use the gallery.</p>
		<p>"Uploader" allows you to create new galleries and upload images to them. "Settings" will allow you to change the administration username &amp; password.</p>
		<p>Authentication of this gallery uses a .htaccess and .htpasswd configuration instead of a database. If you\'re confident working with this method you may wish to relocate the .htpasswd file to a location outside of the public html directory for increased security.</p>
	</article>
	';	
}

