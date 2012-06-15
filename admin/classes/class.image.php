<?php

/** 
 * Image class. 
 * 
 * Used to process images
 * @author BJP
 * 
 */
class image {
	public $filename		= '';
	public $errors			= 0;
	public $rotateErrors	= 0;
	
	function __construct($filename) {		
		$this->filename = $filename;
	}
	
	function rotate($degrees) {
		//check file is actually an image by making sure getimagesize returns an array
		$imageSizeArray = @getimagesize($this->filename);
		if(is_array($imageSizeArray)) {
			// parse path for the extension
			$fileInfo = pathinfo($this->filename);
			
			//check the file type
			if(strtolower($fileInfo['extension']) == 'jpg' || strtolower($fileInfo['extension']) == 'gif' || strtolower($fileInfo['extension']) == 'png') {
				// Load
				if(strtolower($fileInfo['extension']) == 'jpg') {
					$img = imagecreatefromjpeg($this->filename);
				} elseif(strtolower($fileInfo['extension']) == 'gif') {
					$img = imagecreatefromgif($this->filename);
				} elseif(strtolower($fileInfo['extension']) == 'png') {
					$img = imagecreatefrompng($this->filename);
				}
				
				// Rotate
				$rotatedImg = imagerotate($img, $degrees, 0);
				
				// Output
				if(strtolower($fileInfo['extension']) == 'jpg') {
					if(!imagejpeg( $rotatedImg, $this->filename, 100 )) {
						$this->rotateErrors++;
					}
				} elseif(strtolower($fileInfo['extension']) == 'gif') {
					if(!imagegif( $rotatedImg, $this->filename, 100 )) {
						$this->rotateErrors++;
					}
				} elseif(strtolower($fileInfo['extension']) == 'png') {
					if(!imagepng( $rotatedImg, $this->filename, 100 )) {
						$this->rotateErrors++;
					}
				}
				$this->generateThumbNails();
			} else {
				$this->rotateErrors++;
			}
		} else {
			$this->rotateErrors++;
		}
	}
	
	function generateThumbNails() {
		//check file is actually an image by making sure getimagesize returns an array
		$imageSizeArray = @getimagesize($this->filename);
		if(is_array($imageSizeArray)) {
			// parse path for the extension
			$fileInfo = pathinfo($this->filename);
	
			//check the file type
			if(strtolower($fileInfo['extension']) == 'jpg' || strtolower($fileInfo['extension']) == 'gif' || strtolower($fileInfo['extension']) == 'png') {
					
				//GD includes several "imagecreatefrom..." functions for several image types, so use the appropriate function for your image type
				if(strtolower($fileInfo['extension']) == 'jpg') {
					$img = imagecreatefromjpeg($this->filename);
				} elseif(strtolower($fileInfo['extension']) == 'gif') {
					$img = imagecreatefromgif($this->filename);
				} elseif(strtolower($fileInfo['extension']) == 'png') {
					$img = imagecreatefrompng($this->filename);
				}
				list($width, $height) = $imageSizeArray;
	
				// calculate thumbnail size for 200px wide thumb
				$new_width = 200;
				$new_height = floor( $height * ( 200 / $width ) );
	
				// create a new temporary image
				$tmp_img = imagecreatetruecolor( $new_width, $new_height );
	
				// copy and resize old image into new image
				imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
	
				// save thumbnail into a file
				$thumbFilename = $fileInfo['dirname'] . '/' . 200 . '/' . $fileInfo['filename'] . '.' . $fileInfo['extension'];
					
				if(strtolower($fileInfo['extension']) == 'jpg') {
					if(!imagejpeg( $tmp_img, $thumbFilename, 80 )) {
						$this->errors++;
					}
				} elseif(strtolower($fileInfo['extension']) == 'gif') {
					if(!imagegif( $tmp_img, $thumbFilename, 80 )) {
						$this->errors++;
					}
				} elseif(strtolower($fileInfo['extension']) == 'png') {
					if(!imagepng( $tmp_img, $thumbFilename, 80 )) {
						$this->errors++;
					}
				}
				
				
				// calculate thumbnail size for 800px wide thumb
				$new_width = 800;
				$new_height = floor( $height * ( 800 / $width ) );
				
				// create a new temporary image
				$tmp_img = imagecreatetruecolor( $new_width, $new_height );
				
				// copy and resize old image into new image
				imagecopyresampled( $tmp_img, $img, 0, 0, 0, 0, $new_width, $new_height, $width, $height );
				
				// save thumbnail into a file
				$thumbFilename = $fileInfo['dirname'] . '/' . 800 . '/' . $fileInfo['filename'] . '.' . $fileInfo['extension'];
					
				if(strtolower($fileInfo['extension']) == 'jpg') {
					if(!imagejpeg( $tmp_img, $thumbFilename, 80 )) {
						$this->errors++;
					}
				} elseif(strtolower($fileInfo['extension']) == 'gif') {
					if(!imagegif( $tmp_img, $thumbFilename, 80 )) {
						$this->errors++;
					}
				} elseif(strtolower($fileInfo['extension']) == 'png') {
					if(!imagepng( $tmp_img, $thumbFilename, 80 )) {
						$this->errors++;
					}
				}
			} else {
				$this->errors++;
			}
		} else {
			$this->errors++;
		}
	}
	
	
	
	
}

?>