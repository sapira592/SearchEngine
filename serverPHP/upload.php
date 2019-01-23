<?php

	include('db.php');
	include('parser.php');
	$res = "noFiles";

	if(is_uploaded_file($_FILES['uploadFiles']['tmp_name'][0]) &&
			file_exists($_FILES['uploadFiles']['tmp_name'][0])){
		$file = array();
		$allFiles = array();
		$filesNum = count($_FILES['uploadFiles']['tmp_name']);//num of requiered files to be uploaded.
		

		//get the num of the fiels which are stored in DB
		$query = "SELECT COUNT(*) as total FROM doc ";
		$result = $conn->query($query);
		$row = $result->fetch_row();
		$num_Of_Stored_Files = $row[0] + 1;


		//fetch data of the upcoming upload files
		for($i=0; $i < $filesNum; $i++){

			$file['id'] = $num_Of_Stored_Files++;
			$file['name'] = substr($_FILES['uploadFiles']['name'][$i],0,-4);
			$file['content'] = file_get_contents($_FILES['uploadFiles']['tmp_name'][$i]);
			$lines = file($_FILES['uploadFiles']['tmp_name'][$i], FILE_IGNORE_NEW_LINES);
			$file['author'] = substr($lines[0],0);
			$file['theme'] = substr($lines[2],1,-1);
			$file['brief'] = $lines[4] . " <br> " . $lines[5] . " <br> " . $lines[6];
			$allFiles[]=$file;

			//save in folder "uploads":
			move_uploaded_file($_FILES['uploadFiles']['tmp_name'][$i], "../uploads/".$file['name'].".txt");

		}

		
		//==================================================================================

		Parser($allFiles, $filesNum, $conn);
		$res = "ok";
		//==================================================================================

	}
	
	echo ($res);

		/*$result->close();
		mysql_close($conn);*/
?>