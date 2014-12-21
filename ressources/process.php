<?php
	//-----------------------
	//-----------------------
	//-----------------------
	//---IMAGES

	//Security !!!
	$extension_upload = strtolower(  substr(  strrchr($_FILES['uploaded-image']['name'], '.')  ,1)  );                           


	$imgDirName = "imgtoprocess/";

	if($_FILES['uploaded-image']['name'] != ""){

		$imgName = explode(".", $_FILES['uploaded-image']['name']);
		$imgNewName = md5($imgName[0]);
		$imgNewPath = $imgDirName.$imgNewName.".".$imgName[1];

		$imageFile = (object) array('name' => $imgNewName, 'path' => $imgNewPath, 'type' => $imgName[1]);

		$result = move_uploaded_file($_FILES['uploaded-image']['tmp_name'],$imgNewPath);


		//-----------------------
		//-----------------------
		//-----------------------
		//Generate the lego pixel art
		include('ressources/getlego.php');
		if(isset($_POST['get-size'])){
			getLego($imageFile, $_POST['get-size']);
		} else {
			getLego($imageFile, 25);
		}

	}		
?>	