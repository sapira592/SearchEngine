<?php

	include('db.php');
	include('searchFunctions.php');

	if(isset($_POST['query'])){

		$query = $_POST['query'];
		
		$stopList = array("to","and","it","as","or","not","is");
		$opList = array("or","and","not");
		$split1 = array();
		$experessions = array();
		

		//split the query to parts
		$split1 = preg_split('/[)()]/', $query);
		$first_key = key($split1);//first key (first key may be 1)
		$last_key = count($split1)+$first_key;

		//delete empty strings
		for($j=$first_key; $j<$last_key;$j++)
			if($split1[$j]=="" || $split1[$j]==" ")
				unset($split1[$j]);
			

		$split1 = array_values($split1);

		for($j=0; $j<count($split1);$j++)
			$experessions[] = splitExpression($split1[$j]);//first key: 0!-ok


	//======================== Validation of Expressions ===================================

		$arr = $experessions[0];
		$count = count($arr);
		$str = array();
		$res = array();
		$flag = 1;
		if($count==4 && count($experessions)==1){//only one line with not operator
			
			if($arr[1] == "and" && $arr[2] == "not"){
				$term1 = array($arr[0]);
				$op = array("not");
				$term2 = array($arr[3]);
				unset($experessions[0]);//covered
				array_unshift($experessions,$term2);
				array_unshift($experessions,$op);
				array_unshift($experessions,$term1);
				$flag = 0;
			}

		}	

if($flag)	{ 
		foreach ($experessions as $arr) {
			if(count($arr)>3){
				$str['name'] = "INVALID_QUERY";
				$res[] = $str;
			}
		}

		//querys of 1 or 3 words!
		$count = count($experessions);


		//the query "and not ____" must be at the end	
		for($i=0;$i<count($experessions)-1;$i++){
			if(count($experessions[$i]) == count($experessions[$i+1])
					 && count($experessions[$i+1]) == 3){

					$key = key($experessions[$i+1]);
					if($experessions[$i+1][$key] == "and"
						&& $experessions[$i+1][$key+1] == "not"){
						unset($experessions[$i+1][$key]);
					}
					else{ 
						$str['name'] = "INVALID_QUERY";
						$res[] = $str;
					}
			   }
		}


		//-------------

		$count = count($experessions);
		$experessions = array_values($experessions);
		//single operator -  _(...) / (...)_
		if(in_array($experessions[0][0], $opList) ||
			in_array($experessions[$count-1][count($experessions[$count-1])-(1-key($experessions[$count-1]))], $opList)){
			$str['name'] = "INVALID_QUERY"; 
			$res[] = $str;
		}

		//only one them befor -  _(...) / (...)_
		if((count($experessions[0]) == 1 || count($experessions[$count-1]) == 1) &&
			$count > 1){
			$str['name'] = "INVALID_QUERY";	
			$res[] = $str;
		}


		if(count($experessions[0]) == 2){

			$first_key1 = key($experessions[0]);//first key
			$last_key1 = count($experessions[0])-(1-$first_key1);

			if(in_array($experessions[0][$first_key1], $opList)){
				// "or x(...)"
				$str['name'] = "INVALID_QUERY";
				$res[] = $str;
			}

			else{
				$tempTerm = $experessions[0][$first_key1];
				array_splice($experessions[0], 0, 1);
				$tempArr = array("0" => $tempTerm);
				array_unshift($experessions,$tempArr);
			}

		}


		
		$num = count($experessions)-1;
		if(count($experessions[$num]) == 2){

			$first_key1 = key($experessions[$num]);//first key
			$last_key1 = count($experessions[$num])-(1-$first_key1);

			if(in_array($experessions[$num][$last_key1], $opList)){
				// "(...) x or"
				$str['name'] = "INVALID_QUERY";
				$res[] = $str;
			}

			else{
				$tempTerm = $experessions[$num][$last_key1];
				array_splice($experessions[$num], 1, 1);
				$experessions[] = array($tempTerm);
			}

		}

}



	//============================ Execute all the Expressions ====================================

		if(!$res){

		//=== Get The requierd Doc ID per SINGLE query ===

				$experessions = array_values($experessions);
				for($j=0 ; $j< count($experessions);$j++)
					if(!$experessions[$j])
						unset($experessions[$j]);

				$experessions = array_values($experessions);
				$num = count($experessions);
				$multipleDocIDList = array();
				//print_r($experessions);

				for($i=0;$i<$num;$i+=2){ //operands must be an odd positions

					$num1 = count($experessions[$i]);
					$firstKey = key($experessions[$i]);
					$lastKey = $num1 - (1-$firstKey);
					$termVal = $experessions[$i][$firstKey];


					//single term to search
					if($num1 == 1 && (!array_search($termVal,$stopList))){
						$termID = extractTermID($termVal, $conn);
						$multipleDocIDList[] = getDocID($termID, $conn);
					}


					//at least one expression - __ op __
					else if($num1 > 1){

						$termVal1 = $experessions[$i][$firstKey];
						$termVal2 = $experessions[$i][$lastKey];
						$operand = $experessions[$i][$lastKey-1];

						$termID1 = extractTermID($termVal1, $conn);//first term
						$termID2 = extractTermID($termVal2, $conn);//second term

						$inStopList1 = in_array($termVal1,$stopList);//first term
						$inStopList2 = in_array($termVal2,$stopList);//first term

						//if both terms are in the stopList. Example:
						//(it or is) and ___ OR ___ and (it or is)
						//delete the operand
						if($inStopList1 && $inStopList2){
							if($i==0)unset($experessions[$i+1]);//(it or is) and ___
							else unset($experessions[$i-1]);// ___ and (it or is)
						}
						
						//if just one of the term is in the stopList,
						//extract it's docID 
						else if($inStopList1){
							$multipleDocIDList[] = getDocID($termID2, $conn);
						}

						else if($inStopList2){
							$multipleDocIDList[] = getDocID($termID1, $conn);
						}
						//both terms are NOT in stopList
						else{
							switch ($operand) {
								case "or":
									$multipleDocIDList[] = orFunc($termID1, $termID2, $conn);
									break;
								case "and":
									$multipleDocIDList[] = andFunc($termID1, $termID2, $conn);
									break;
						
							}

						}
						
					}
					unset($experessions[$i]); // left with operand list
				}


				$operands = array_values($experessions);




//======================== Extract Doc Info ================================

			//=== Get The requierd Doc IDs between multiple queries ===


				$num_of_operands = count($operands);
				$num_of_docsID = count($multipleDocIDList);
				$final_doc_list = array("/");

				if(!$num_of_operands){
					if($num_of_docsID){
						$num3 = count($multipleDocIDList[0]);
							for($k=0;$k<$num3; $k++)
								array_push($final_doc_list, $multipleDocIDList[0][$k]);
					}
					else{
						$str['name'] = "NO_DOCS";
						$res[] = $str;
					}
				}


				//there are operands..
				else{
					$temp_arr;
					for ($i=0,$j=0; $i <($num_of_docsID-1) && $j< $num_of_operands; $i+=2,$j++) { 
						$current_operand = $operands[$j][0];
						switch ($current_operand) {
								case "or":
									$temp_arr = $multipleDocIDList[$i];
									$num3 = count($multipleDocIDList[$i+1]);
									for($k=0;$k<$num3; $k++)
										array_push($temp_arr, $multipleDocIDList[$i+1][$k]);
									$temp_arr = array_unique($temp_arr);
									break;
								case "and":
									$temp_arr = $multipleDocIDList[$i];
									//returns the equals values 
									$temp_arr = array_intersect($temp_arr, $multipleDocIDList[$i+1]);
									break;
								case "not":
									$temp_arr = $multipleDocIDList[$i];
									//returns the values in $temp_arr that are not present in $multipleDocIDList[$i+1]
									$temp_arr = array_diff($temp_arr, $multipleDocIDList[$i+1]);
									break;	
					}

					$temp_arr = array_values($temp_arr);
					for($k=0;$k<count($temp_arr); $k++)
						array_push($final_doc_list, $temp_arr[$k]);
					$final_doc_list = array_unique($final_doc_list);

				}

				
			}
		    unset($final_doc_list[0]);//remove "\"
			$final_doc_list = array_values($final_doc_list);

			$res = extractDocInfo($final_doc_list, $conn);
		}

		
		echo json_encode($res);


		//mysql_close($conn);
	}





?>