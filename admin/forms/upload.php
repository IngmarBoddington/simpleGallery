	<h2>Upload - ##GALLERY##</h2>	
	<p>Your are now uploading to "##GALLERY##". Drag and drop images onto the box below to upload.</p>
	<h3>Choose file(s)</h3>
	<div id="uploadArea">
		Drop files here to upload!
	</div>
	<div id="uploadList">
	</div>
	<div id="completeList">
	</div>
<script>
function preventDefaultAndPropagation(evt) {
	evt.preventDefault();
	evt.stopPropagation();
}

function loopThroughDroppedFiles (files) {
	//check for File API support & alert where it does not work.
	if (typeof files === "undefined") {
		$("#uploadList").html("No support for the File API in this web browser. <a href=\"https://www.google.com/chrome\">Download Chrome</a>");
	} else {
		postFile(files, 0);
	}
}


//function to post the file and provide status updates
function postFile(files, i) {
	var file = files[i];
	//create new elements used for the status information
	var div = $("<div></div>"),
	pb = $("<progress></progress>");
	div.append(pb); //apends progress bar to the div

	// HTTP request - supported by Firefox, Chrome and Safari...
	xhr = new XMLHttpRequest();

	//uploads should update the progress bar to see what is happening...
	xhr.upload.addEventListener("progress", function (evt) {
		if (evt.lengthComputable) {
			$("progress").attr({
				value:evt.loaded,max:evt.total});
		}
	}, false);

	//start the posting to the target script
	xhr.open("post", "##INSTALLDIRECTORY##file.php", true);

	//set request headers
	xhr.setRequestHeader("Content-Type", "multipart/form-data");
	xhr.setRequestHeader("X-File-Name", file.fileName);
	xhr.setRequestHeader("X-File-Size", file.fileSize);
	xhr.setRequestHeader("X-File-Type", file.type);
	xhr.setRequestHeader("Gallery", "##GALLERY##");

	//provide the user with a little warning about the file processing delay (the php bit)
	div.append('<p>Uploading ('+(i+1)+' of '+(files.length)+') - "'+file.fileName+'". Processing may take a few seconds...</p>');

	//validate the file types & post the file
	if(file.type == "image/jpeg" || file.type == "image/gif" || file.type == "image/png") {
		xhr.send(file);
	} else {
		div.html('Invalid file type ('+file.type+') for file '+file.fileName);
	}

	//display status information like progress bar/s etc...
	$("#uploadList").append(div);
	
	//once uploads are complete, return status information & clear any progress...
	xhr.onreadystatechange = function () {
		if (xhr.readyState == 4) {
			//perform ajax request to the status page
			$.ajax({
				type: "GET",
				url: "##INSTALLDIRECTORY##file.php",
				data: {status: "1"},
				async: false,
				success: function(data) {
					$("#completeList").html(data); //output the status
					$("#uploadList").html(""); //empty the progress info
					if(files.length > i) {
						i = i + 1;
						postFile(files, i);
					}
				}
			});
		}
	};
}

//cache to jquery selector so we do not keep dipping into the DOM
var uploadArea = $("#uploadArea");

//make sure no behaviors are triggered by drag events
uploadArea.on("dragleave", function (evt) {
	evt.preventDefault();evt.stopPropagation();
});
uploadArea.on("dragenter", function (evt) {
	evt.preventDefault();evt.stopPropagation();
});
uploadArea.on("dragover", function (evt) {
	evt.preventDefault();evt.stopPropagation();
});

//prevent default behavior on drop & send all dropped files to the looping function
uploadArea.on("drop", function (evt) {
	loopThroughDroppedFiles(evt.originalEvent.dataTransfer.files);
	evt.preventDefault();evt.stopPropagation();
});
</script>