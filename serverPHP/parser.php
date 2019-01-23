<?php

	function Parser(&$allFiles, $filesNum, &$conn){
		
		//======== First Part ==========

		$obj = array('term', 'docID');
		$merge = array();//first table

		for($i=0; $i<$filesNum; $i++){
			$temp = preg_split('/[\s,#";*@$%^!-=+~}{|?><.]+/', $allFiles[$i]['content']);
			foreach ($temp as $val){
				if ($val!="") {
					$obj[0] = strtolower($val); //Make a term lowercase
					$obj[1] = $allFiles[$i]['id'];
					$merge[] = $obj;
				}
			}
		}



		//======== Second Part ==========

		$rank = array();

		$docID = $merge[0][1];//the current doc 
		$positionInRank = 0; 
		$size;

		//first assign
		$rankObj['term'] = $merge[0][0];
		$rankObj['termID'] = 0;
		$rankObj['docID'] = $merge[0][1];
		$rankObj['rank'] = 1;
		$rank[] = $rankObj;

		for($i=1; $i < count($merge) && $docID < ($filesNum + $docID);){
			while( ($i < count($merge)) && $docID == $merge[$i][1]) {
				$size = count($rank);
				$flag = 0;
				for($j=$positionInRank; $j < $size; $j++){ //still on the same doc
					if($rank[$j]['term'] == $merge[$i][0]){
						$rank[$j]['rank'] = $rank[$j]['rank'] + 1;
						$flag = 1; //found the term
					}
				}
				if(!$flag){//new term for curent doc
					$rankObj['term'] = $merge[$i][0];
					$rankObj['docID'] = $merge[$i][1];
					$rankObj['rank'] = 1;
					$rank[] = $rankObj;
				}
				$i++;
			}
			$positionInRank = $size+1;
			$docID++;
		}




		//======== Third  Part ==========

		$terms = array();
		$temp = array();
		$termObj = array('term', 'termID');

		for($i=0; $i<count($merge);$i++){
			$temp[] = $merge[$i][0];
		}

		$temp = array_unique($temp);

		$query =  "SELECT * FROM term";
		$result = $conn->query($query);
		$DBterms = array();
		/*if($result == null){
	            die("SELECT query faild."); //Equivalent to exit
	    }*/

		while ($row = $result->fetch_row()) {
		    $DBterms[] = $row;  
		}

		$num_Of_New_Terms = count($temp);
		$num_Of_Stored_Terms = count($DBterms) + 1;

		for($i=0; $i< ($num_Of_Stored_Terms-1); $i++){
			if($key = array_search($DBterms[$i][0], $temp)){ //Checks if a term from DB exists in terms
				 unset($temp[$key]);
			}
		}

		$num_Of_New_Terms = count($temp);

		//add the new terms to DB
		foreach($temp as $val){
			$term = $val;
			$term = mysql_real_escape_string($term);
			$ID = $num_Of_Stored_Terms++;
			$query =  "INSERT INTO term (termVal, termID)
				   	   VALUES ('$term', '$ID')";
			$result = $conn->query($query);
			/*if($result == null){
	            die("INSERT query faild.");
	    	}*/
		}


		//get termID for rank array
		for($i=0; $i<count($rank); $i++){
			$term = $rank[$i]['term'];
			$term = mysql_real_escape_string($term);
			$query =  "SELECT termID FROM term WHERE termVal='$term'";
			$result = $conn->query($query);
			/*if($result == null){
	            die("SELECT query faild.");
	    	}*/
			$row = $result->fetch_row();
			$rank[$i]['termID'] = $row[0];
		}

		
		//insert to "posting" table the new docs
		for($i=0; $i<count($rank);$i++){
			$termID = $rank[$i]['termID']; 
			$docID = $rank[$i]['docID']; 
			$termRank = $rank[$i]['rank']; 
			$av = 1;
			$query =  "INSERT INTO posting VALUES ('$termID', '$docID', '$termRank', '$av')";
			$result = $conn->query($query);
			/*if($result == null){
	            die("INSERT query faild.");
	    	}*/
		}



		//======== Fourth Part ==========

		//insert to "doc" table the new docs
		for($i=0; $i< $filesNum;$i++){
			$id = $allFiles[$i]['id']; $id = mysql_real_escape_string($id);
			$name = $allFiles[$i]['name']; $name = mysql_real_escape_string($name);
			$theme = $allFiles[$i]['theme'];$theme = mysql_real_escape_string($theme);
			$author = $allFiles[$i]['author']; $author = mysql_real_escape_string($author);
			$content = $allFiles[$i]['content']; $content = mysql_real_escape_string($content);
			$brief = $allFiles[$i]['brief']; $brief = mysql_real_escape_string($brief);
			$query =  "INSERT INTO doc VALUES ('$id', '$name', '$theme', '$author', '$content', '$brief')";
			$result = $conn->query($query);
			/*if($result == null){
	            die("INSERT query faild.");
	    	}*/
		}
	    
	    //mysqli_free_result($result);
		//echo "ok";
	}


?>




