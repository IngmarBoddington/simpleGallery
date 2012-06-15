<?php


//this is the target folder for uploading to
$galleryTarget = $_SERVER['HTTP_GALLERY'];


//debugging
$log = '============GET=========='.PHP_EOL;
foreach($_GET as $key => $value) {
	$log .= $key . "\t\t" . $value . PHP_EOL;
}
$log .= '============POST==========' . PHP_EOL;
foreach($_POST as $key => $value) {
	$log .= $key . "\t\t" . $value . PHP_EOL;
}
$log .= '============SERVER==========' . PHP_EOL;
foreach($_SERVER as $key => $value) {
	$log .= $key . "\t\t" . $value . PHP_EOL;
}
file_put_contents("log.txt",$log);


//start a session for storing the feedback on file validation
session_start();

//assuming no images are being posted to the script & status is not being requested we can reset the upload progress...
if(!isset($_GET['status']) && !isset($_SERVER['HTTP_X_FILE_NAME'])) {
	unset($_SESSION['uploaderStatus']);
}

//status requested, so echo it
if(isset($_GET['status'])) {
	echo $_SESSION['uploaderStatus'];
}

//image posted via ajax, so validate and handle it.
if(isset($_SERVER['HTTP_X_FILE_NAME'])) {

	//session variable to store results of processing. Sessions are used to support multiple uploads
	$_SESSION['uploaderStatus'] .=  '<div>';

	//grab and filter the file name - shouldn't have any slashes anyway, but no harm in making sure...
	$uploadFileName = stripslashes($_SERVER['HTTP_X_FILE_NAME']);

	//check that there isn't a file already in existence with that name...
	if(is_file($uploadFileName)) {
		$_SESSION['uploaderStatus'] .=  '<p class="error">Error: a file with that name ('.$uploadFileName.') already exists. Upload aborted.</p>';
	} else {
		//save the content of the input stream...
		file_put_contents($uploadFileName,file_get_contents('php://input'));

		//since user data cannot be trusted we check here for the file mime type...
		$finfo = new finfo(FILEINFO_MIME);
		$fileType .= $finfo->file($uploadFileName) . PHP_EOL;

		//the directory you want to store valid uploads
		$uploadDirectory = '../images/' . $galleryTarget . '/';

		//validate mime type. I'm accepting anything that's an image here. You can be more/less specific
		if(stripos($fileType, 'image/') !== false) {

			//check directory exists first
			if(!is_dir($uploadDirectory)) {
				//directory doesn't exist, create it (recursively for any subdirectories)
				mkdir($uploadDirectory, 0, true);
			}
			//recheck if directory exists - maybe a bit redundant, but functional and catches both doesn't exist and failed to create
			if(!is_dir($uploadDirectory)) {
				$_SESSION['uploaderStatus'] .=  '<p class="error">Error: upload directory does not exist and could not be created.</p>';
				unlink($uploadFileName); //delete posted file
			} else {
				//directory exists, check if it's writ[b]e[/b]able. - stupid php
				if(!is_writable($uploadDirectory)) {
					$_SESSION['uploaderStatus'] .=  '<p class="error">Error: cannot write to upload directory. Please check permissions</p>';
					unlink($uploadFileName); //delete posted file
				} else {
					//check if the file exists already
					if(is_file($uploadDirectory . $uploadFileName)) {
						//file exists - move_uploaded_file overwrites existing files and this shouldn't be desired behaviour here
						$_SESSION['uploaderStatus'] .=  '<p class="error">Error: a file with that name ('.$uploadFileName.') already exists. Upload aborted.</p>';
						unlink($uploadFileName); //delete posted file
					} else {
						//attempt to move file to desired location
						if(!rename($uploadFileName, $uploadDirectory . $uploadFileName)) {
							//file couldn't be moved
							$_SESSION['uploaderStatus'] .=  '<p class="error">Error: could not move uploaded file ('.$uploadFileName.') to uploads directory</p>';
							unlink($uploadFileName); //delete posted file
						} else {
							//file uploaded and moved to the upload directory - yay!
							$_SESSION['uploaderStatus'] .=  '<p>Successfully uploaded "'.$uploadFileName.'".</p>';
							
							$image = New image('../images/' . $galleryTarget . '/' . $uploadFileName);
							$image->generateThumbNails();
							if($image->errors != 0) {
								$_SESSION['uploaderStatus'] .=  '<p class="error">Error: Could not generate thumbnails for "'.$uploadFileName.'".</p>';
							}
						}
					}
				}
			}
		} else {
			//file is not a valid mime type
			$_SESSION['uploaderStatus'] .=  '<p class="error">Error: file ('.$uploadFileName.') is not an image</p>';
			unlink($uploadFileName); //delete posted file
		}
	}

	$_SESSION['uploaderStatus'] .=  '</div>';
}