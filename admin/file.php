<?php


function generateThumbNails($filename, $desiredThumbNailWidth) {
	//check file is actually an image by making sure getimagesize returns an array
	$imageSizeArray = @getimagesize($filename);
	if(is_array($imageSizeArray)) {
		// parse path for the extension
		$fileInfo = pathinfo($filename);

		//check the file type
		if(strtolower($fileInfo['extension']) == 'jpg' ) {
			//GD includes several "imagecreatefrom..." functions for several image types, so use the appropriate function for your image type
			$img = imagecreatefromjpeg($filename);
			list($width, $height) = $imageSizeArray;

			// calculate thumbnail size
			$new_width = $desiredThumbNailWidth;
			$new_height = floor( $height * ( $desiredThumbNailWidth / $width ) );

			// create a new temporary image
			$tmp_img = imagecreatetruecolor( $new_width, $new_height );

			// copy and resize old image into new image
			imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );

			// save thumbnail into a file
			$thumbFilename = $fileInfo['dirname'] . '/' . $desiredThumbNailWidth . '/' . $fileInfo['filename'] . '.' . $fileInfo['extension'];
			if(imagejpeg( $tmp_img, $thumbFilename, 80 )) {
				return true;
			} else {
				return false;
			}
		}
		//insert here copy/pastes of the above for each other image type you want to handle (gif/png/etc...)
	}
}



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
		$_SESSION['uploaderStatus'] .=  'Error: a file with that name ('.$uploadFileName.') already exists. Upload aborted.';
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
				$_SESSION['uploaderStatus'] .=  'Error: upload directory does not exist and could not be created.';
				unlink($uploadFileName); //delete posted file
			} else {
				//directory exists, check if it's writ[b]e[/b]able. - stupid php
				if(!is_writable($uploadDirectory)) {
					$_SESSION['uploaderStatus'] .=  'Error: cannot write to upload directory. Please check permissions';
					unlink($uploadFileName); //delete posted file
				} else {
					//check if the file exists already
					if(is_file($uploadDirectory . $uploadFileName)) {
						//file exists - move_uploaded_file overwrites existing files and this shouldn't be desired behaviour here
						$_SESSION['uploaderStatus'] .=  'Error: a file with that name ('.$uploadFileName.') already exists. Upload aborted.';
						unlink($uploadFileName); //delete posted file
					} else {
						//attempt to move file to desired location
						if(!rename($uploadFileName, $uploadDirectory . $uploadFileName)) {
							//file couldn't be moved
							$_SESSION['uploaderStatus'] .=  'Error: could not move uploaded file ('.$uploadFileName.') to uploads directory';
							unlink($uploadFileName); //delete posted file
						} else {
							//file uploaded and moved to the upload directory - yay!
							$_SESSION['uploaderStatus'] .=  'Successfully uploaded "'.$uploadFileName.'".';
							if(!generateThumbNails('../images/' . $galleryTarget . '/' . $uploadFileName, 200)) {
								$_SESSION['uploaderStatus'] .=  'Could not create 200px wide thumbnailed "'.$uploadFileName.'".';
							}
							if(!generateThumbNails('../images/' . $galleryTarget . '/' . $uploadFileName, 800)) {
								$_SESSION['uploaderStatus'] .=  'Could not create 800px wide thumbnailed "'.$uploadFileName.'".';
							}
						}
					}
				}
			}
		} else {
			//file is not a valid mime type
			$_SESSION['uploaderStatus'] .=  'Error: file ('.$uploadFileName.') is not an image';
			unlink($uploadFileName); //delete posted file
		}
	}

	$_SESSION['uploaderStatus'] .=  '</div>';
}