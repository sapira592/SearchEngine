<?php

	function splitExpression($express){

			$str;
			$explodeExpress = array();

			$express = strtolower($express);
			$str = preg_replace('/[()]/s', '', $express);// if exist "()"

			$explodeExpress = preg_split('/\s+/', $str);

			$first_key = key($explodeExpress);//first key
			$last_key = count($explodeExpress)+$first_key;

			for($j=$first_key; $j<$last_key;$j++)
				   if(!$explodeExpress[$j])
				      unset($explodeExpress[$j]);

			return $explodeExpress;
	}



	function extractTermID($termVal, &$conn){

		$query =  "SELECT termID FROM term WHERE termVal='$termVal'";
		$result = $conn->query($query);
		$row = $result->fetch_row();

		$result->close();

		return $row[0];

	}



	function extractDocInfo($docsArr, &$conn){

		if(!$docsArr){
			$str = array();
			$arr = array();
			$str['name'] = "NO_DOCS";
			$arr[] = $str;
			return $arr;
		}

		$doc = array();
		$allDocs = array();
		$num = count($docsArr);
		asort($docsArr);

		for($i=0; $i<$num; $i++){
			$docID = $docsArr[$i];
			$query =  "SELECT docName,docTheme,docAuthor,docContent,docBrief 
					   FROM doc 
					   WHERE docID='$docID'";
			$result = $conn->query($query);
			$row = $result->fetch_row();
			$doc['name'] = $row[0];
			$doc['theme'] = $row[1];
			$doc['author'] = $row[2];
			$doc['content'] = $row[3];
			$doc['brief'] = $row[4];
			$allDocs[] = $doc;

		}

		$result->close();

		//translates the data passed to it to a JSON string which can then be output to a JavaScript variable
		//echo /*json_encode*/

		//print_r($allDocs,true);
		return $allDocs;

	}




//=============================================================


	

	function getDocID($termID ,&$conn){

		$query =  "SELECT docID FROM posting 
				   WHERE termID='$termID' AND available=1";
		$result = $conn->query($query);
		$docID = array();

		while ($row = $result->fetch_row()) {
		    $docID[] = $row[0];  
		}
		
	    return $docID;
		$result->close();
	}

	function andFunc($termID1, $termID2, &$conn){

		$term1Res = getDocID($termID1, $conn);
		$term2Res = getDocID($termID2, $conn);

		if(count($term1Res) == 0)
			$docID = $term1Res;
		else if(count($term2Res) == 0)
			$docID = $term2Res;
		else
			$docID = array_intersect($term1Res, $term2Res);
		
		return $docID;
	}


	function orFunc($termID1, $termID2, &$conn){

		$term1Res = getDocID($termID1, $conn);
		$term2Res = getDocID($termID2, $conn);
		$docID = array_merge($term1Res, $term2Res);
		$docID = array_unique($docID);
		return $docID;
	}


?>




