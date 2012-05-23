<?php


//initial configuration...

$salt = 'fdsafydasifet4353498r43';
$username = 'admin';
$password = '1234';

$htaccess = 'AuthName "Members Area"
AuthType Basic
AuthUserFile /var/www/gallery/admin/.htpasswd
AuthGroupFile /dev/null
<Limit GET>
require valid-user
</Limit>';

if(!file_exists('.htpasswd')) {
	echo 'making access files';
	$password = base64_encode(sha1($password, true));
	if(file_put_contents('.htpasswd', "$username:{SHA}$password\n")) {
		if(file_put_contents('.htaccess', $htaccess)) {
			header('Location: admin.php');
		}
	}
	exit();
}





?><html>
<head>
<script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.5.1/jquery.min.js"></script>

	<style>
		#drop-area {
			height: 50px;
			text-align: center;
			border: 2px dashed #ddd;
			padding: 10px;
			margin-bottom: 2em;
		}
		
		#drop-area .drop-instructions {
			display: block;
			height: 30px;
		}
		
		#drop-area .drop-over {
			display: none;
			font-size: 25px;
			height: 30px;
		}
				
		#drop-area.over {
			background: #ffffa2;
			border: 2px dashed #000;
		}
		
		#drop-area.over .drop-instructions {
			display: none;
		}

		#drop-area.over .drop-over {
			display: block;
		}
		
		#drop-area.over .drop-over {
			display: block;
			font-size: 25px;
		}
		
		
		#file-list {
			list-style: none;
			margin-bottom: 3em;
		}
	
		#file-list li {
			border-bottom: 1px solid #000;
			margin-bottom: 0.5em;
			padding-bottom: 0.5em;
		}

		#file-list li.no-items {
			border-bottom: none;
		}
		
		#file-list div {
			margin-bottom: 0.5em;
		}
		
		#file-list li img {
			max-width: 400px;
		}
		
		#file-list .progress-bar-container {
			width: 400px;
			height: 10px;
			border: 1px solid #555;
			margin-bottom: 20px;
		}
		
		#file-list .progress-bar-container.uploaded {
			height: auto;
			border: none;
		}
		
		#file-list .progress-bar {
			width: 0;
			height: 10px;
			font-weight: bold;
			background: #6787e3;
		}
		
		#file-list .progress-bar-container.uploaded .progress-bar{
			display: inline-block;
			width: auto;
			color: #6db508;
			background: transparent;
		}
	</style>
</head>
</head>
<body>
<h3>Choose file(s)</h3>
<p>
	<input id="files-upload" type="file" multiple>
</p>
<p id="drop-area">
	<span class="drop-instructions">or drag and drop files here</span>
	<span class="drop-over">Drop files here!</span>
</p>

<ul id="file-list">
	<li class="no-items">(no files uploaded yet)</li>
</ul>

<script>
					(function () {
						var filesUpload = document.getElementById("files-upload"),
							dropArea = document.getElementById("drop-area"),
							fileList = document.getElementById("file-list");
							
						function uploadFile (file) {
							var li = document.createElement("li"),
								div = document.createElement("div"),
								img,
								progressBarContainer = document.createElement("div"),
								progressBar = document.createElement("div"),
								reader,
								xhr,
								fileInfo;
								
							li.appendChild(div);
							
							progressBarContainer.className = "progress-bar-container";
							progressBar.className = "progress-bar";
							progressBarContainer.appendChild(progressBar);
							li.appendChild(progressBarContainer);
							
							// Uploading - for Firefox, Google Chrome and Safari
							xhr = new XMLHttpRequest();
							
							// Update progress bar
							xhr.upload.addEventListener("progress", function (evt) {
								if (evt.lengthComputable) {
									progressBar.style.width = (evt.loaded / evt.total) * 100 + "%";
								}
								else {
									// No data to calculate on
								}
							}, false);
							
							// File uploaded
							xhr.addEventListener("load", function () {
								progressBarContainer.className += " uploaded";
								progressBar.innerHTML = "Uploaded!";
							}, false);
							
							xhr.open("post", "file.php", true);
							
							// Set appropriate headers
							xhr.setRequestHeader("Content-Type", "multipart/form-data");
							xhr.setRequestHeader("X-File-Name", file.fileName);
							xhr.setRequestHeader("X-File-Size", file.fileSize);
							xhr.setRequestHeader("X-File-Type", file.type);

							// Send the file (doh)
							xhr.send(file);
							
							// Present file info and append it to the list of files
							xhr.onreadystatechange = function () {
								if (xhr.readyState == 4) { 
									div.innerHTML = xhr.responseText; 
								} 
							};
							
							fileList.appendChild(li);
						}
						
						function traverseFiles (files) {
							if (typeof files !== "undefined") {
								for (var i=0, l=files.length; i<l; i++) {
									uploadFile(files[i]);
								}
							}
							else {
								fileList.innerHTML = "No support for the File API in this web browser";
							}	
						}
						
						filesUpload.addEventListener("change", function () {
							traverseFiles(this.files);
						}, false);
						
						dropArea.addEventListener("dragleave", function (evt) {
							var target = evt.target;
							
							if (target && target === dropArea) {
								this.className = "";
							}
							evt.preventDefault();
							evt.stopPropagation();
						}, false);
						
						dropArea.addEventListener("dragenter", function (evt) {
							this.className = "over";
							evt.preventDefault();
							evt.stopPropagation();
						}, false);
						
						dropArea.addEventListener("dragover", function (evt) {
							evt.preventDefault();
							evt.stopPropagation();
						}, false);
						
						dropArea.addEventListener("drop", function (evt) {
							traverseFiles(evt.dataTransfer.files);
							this.className = "";
							evt.preventDefault();
							evt.stopPropagation();
						}, false);										
					})();
				</script>
</body>
</html>