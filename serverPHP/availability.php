<?php

	include('db.php');

	if(isset($_POST['name']) && isset($_POST['type'])){

        $name = $_POST['name']; 
        $type = $_POST['type'];

        $name = mysql_real_escape_string($name);
        
	    // get the doc id
		$query =  "SELECT docID FROM doc WHERE docName='$name'";
		$result = $conn->query($query);

		if(!($result)){
	            die("SELECT query faild.");
	    }
		$row = $result->fetch_row();
		$id = $row[0];
		//the update
		if($id){
			$query =  "UPDATE posting SET available='$type' WHERE docID='$id' AND termID>=0";
			$result = $conn->query($query);
			if(!($result)){
		            die("SELECT query faild.");
		    }
		}
		

    }

    echo "ok";
    mysqli_free_result($result);
    mysqli_close($conn);


?>