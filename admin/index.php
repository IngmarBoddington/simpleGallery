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
 * Routing
 */
$installDirectory = str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);
$route = str_ireplace($installDirectory, '', $_SERVER['REQUEST_URI']);
$route = explode('/',$route);

$page = New page();
$page->body .= '
<nav role="navigation">
	<ul>
		<li><a href="' . $installDirectory . '">Home</a></li>
		<li><a href="' . $installDirectory . 'upload">Uploader</a></li>
		<li><a href="' . $installDirectory . 'settings">Settings</a></li>
	<ul>
</nav>' . PHP_EOL;


if( (!file_exists('.htpasswd') || !file_exists('.htaccess') || !file_exists('../.htaccess') )  
		&& !isset($_POST['Username'])) {	
	$page->body = '
<article>
	<header>
		<h2>Installtion</h2>
	</header>
	
	<p>Congratulations on uploading the simple gallery. To secure the admin section please enter a username and password below. These will be required to access the admin in future.</p>

	<form action="index.php" method="post" name="Config">
		<fieldset>
			<label for="Username">Admin Username</label>
			<input type="text" id="Username" name="Username" class="form-text">
			<p class="form-help">This is help text under the form field.</p>
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
	exit();
}
	
if(isset($_POST['Username'])) {
	$username = $_POST['Username'];
	$password = $_POST['Password'];
	
	$installDirectory = str_ireplace('index.php', '', $_SERVER['SCRIPT_NAME']);
	
	$htaccess = 'AuthName "Gallery Administration"
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
				$page->body = '<p class="error">Could not create .htaccess file in installation directory. Please check file permissions. <a href="' . $installDirectory . '">Retry</a></p>';
			}
		} else {
			$page->body = '<p class="error">Could not create admin .htaccess file in admin directory. Please check file permissions. <a href="' . $installDirectory . '">Retry</a></p>';
		}
	} else {
		$page->body = '<p class="error">Could not create admin .htpasswd file in admin directory. Please check file permissions. <a href="' . $installDirectory . '">Retry</a></p>';
	}
	exit();
}

if(isset($_POST['newGallery'])) {
	//create new gallery...
	$galleryName = str_replace(' ', '-', $_POST['newGallery']);
	$galleryName = preg_replace('/[^A-Za-z0-9 -]/', '', $galleryName);

	if(is_dir('../images/' . $galleryName)) {
		$page->body = '<p class="error">Could not create "' . $galleryName . '" as it already exists. <a href="' . $installDirectory . 'upload">Retry</a></p>';
		exit();
	} else {
		mkdir('../images/' . $galleryName);
		chmod('../images/' . $galleryName, 0777);
		mkdir('../images/' . $galleryName . '/200');
		chmod('../images/' . $galleryName . '/200', 0777);
		mkdir('../images/' . $galleryName . '/800');
		chmod('../images/' . $galleryName . '/800', 0777);
	}
}


if($route[0] == 'settings') {
	$page->body .= '
<article>
	<header>
		<h2>Settings</h2>	
	</header>
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
	exit();
}

if($route[0] == 'upload' && !isset($route[1])) {
	$galleryDirectory = '../images/';	

	$galleries = glob('*', $galleryDirectory);
	
	// Open a known directory, and proceed to read its contents
	$galleriesList = '';
	if (is_dir($galleryDirectory)) {
		if ($dh = opendir($galleryDirectory)) {
			while (($file = readdir($dh)) !== false) {
				if($file != '.' && $file != '..' && is_dir($galleryDirectory . $file)) {
					$galleriesList .= 'Gallery: <a href="upload/' . $file . '">' . $file . '</a><br />';
				}
			}
			closedir($dh);
		} 
	}
	
	//new gallery form
	$page->body .= '
<article>
	<header>
		<h2>Upload</h2>	
	</header>
	<p>Select the gallery you wish to upload to or create a new one using the provided form:</p>
	' . $galleriesList . '
	<form action="upload" method="post" name="Config">
		<fieldset>
			<label for="newGallery">New Gallery</label>
			<input type="text" id="newGallery" name="newGallery" class="form-text">
		</fieldset>

		<fieldset class="form-actions">
			<input type="submit" value="Submit">
		</fieldset>
	</form>
</article>
</body>
</html>';	
	exit();
}

if($route[0] == 'upload' && isset($route[1])) {
	$page->body .= file_get_contents('views/upload.php');	
	$page->body = str_ireplace('##INSTALLDIRECTORY##', $installDirectory, $page->body);
	$page->body = str_ireplace('##GALLERY##', $route[1], $page->body);
	exit();
}


$page->body .= '
<article>
	<header>
		<h2>Simple Gallery Administration</h2>	
	</header>
	
	<p>Pick an option from the menu on the left to use the gallery.</p>
	<p>"Uploader" allows you to create new galleries and upload images to them. "Settings" will allow you to change the administration username &amp; password.</p>
</article>
</body>
</html>
';