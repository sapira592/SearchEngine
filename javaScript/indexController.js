$(document).ready(function(){
	  
	  $('[data-toggle="popover"]').popover({placement: 'right'});//help popover
	  
	  setSubmitLogin();

	  setSearch();

});	  	


var arr, query;


function setSubmitLogin(){
	$('#loginForm').submit(function(){
		var user = $('#user').val().toLowerCase();
		var pass = $('#pass').val().toLowerCase();
		 
		 $.ajax({
           type: "post",
           url: "http://localhost/searchEngine/serverPHP/login.php",
           data: {"user": user, "pass":pass}, // serializes the form's elements.
           success: function(res)
           {
           		if(res=="ok"){
	           	 	window.location.replace("http://localhost/searchEngine/manager.html");
	           	}
	           	else{
	           		window.location.replace("http://localhost/searchEngine/index.html");
	           		alert("Not a manager");
	           	} 
	       }
        });
    	return false; //to prebet from submmision
	});
}


function setSearch(){
	$('#searchForm').submit(function(){
		query = $('#searchLable').val();
		if(query){
			 $.ajax({
	           type: "post",
	           url: "http://localhost/searchEngine/serverPHP/userSearch.php",
	           data: {"query": query}, // serializes the form's elements.
	           success: function(res)
	           {
	           		arr = JSON.parse(res);
	           		//alert(res);
	           		if(arr[0]['name'] ==  "INVALID_QUERY"){
	           			alert("Invalid Query");
	           		}
	           		else if(arr[0]['name'] ==  "NO_DOCS"){
	           			clear();
	           			$("#resultsBox").append("There are no suitable docs for this search");
	           		}
	           		else{
						console.log("ok");// Dump all data of the Object in the console
		           		presentResults(); 
	           		}
	           		
		       }
	        });
		}
		else{
	        alert("No Query");
		}
		 
    	return false; //to prebet from submmision
	});
}


//=====================================================

function presentResults(){

	clear();
	var single_doc = "";
	var brief =""; 
	var terms_to_mark = termToMark();

	 for (var key in arr) {
	    if (arr.hasOwnProperty(key)) {

	      single_doc += "<section class='result'> <a onclick='newPage("+ key +")'>" + 
	      				 arr[key]["theme"]+" - "+ arr[key]["author"] +
	      				 "</a> <br>";
	      brief += mark(terms_to_mark,key);
	      single_doc +=  brief + "...</section>";
	      console.log(arr[key]["name"] + ", " + arr[key]["brief"]);
	      
	    }
	    brief = "";
  	}
	$("#resultsBox").append(single_doc);

}


function clear(){
	$('#resultsBox').addClass('results');
	$("#resultsBox").html("<h5>Results</h5>");
}


function newPage(index){
	var fileName = "uploads/" + arr[index]['name'] + ".txt";
	var myWindow = window.open("", "", "width=700");
    myWindow.document.write("<html><head></head><body><embed width=600 height=600 src='"+fileName+"'><button onclick='window.print()'>Print this page</button></body></html>");
}


function termToMark(){
	//var str = query.toLowerCase();
	var words = query.split(/[\-_()\s]/);
	var i=0;
	for(i=0; i<words.length;i++){
		if(words[i] == "and" || words[i] == "or" || words[i] == "" || words[i] == " "){
			words.splice(i, 1);
			i--;
		}
		if(words[i] == "not")
			words.splice(i,(words.length-i));
	}

	//console.log(words);
	return words;
}


function mark(terms, index){
	var content = arr[index]["brief"].toLowerCase();
	var content_arr = content.split(/[\s,]+/);
	var i,j;
	
	for(i=0;i<content_arr.length;i++){
		for(j=0;j<terms.length;j++){
			if(content_arr[i] == terms[j]){
				content_arr.splice(i, 0, "<span class='highlighted'>");
				if(i+2<content_arr.length)
					content_arr.splice(i+2, 0, "</span>");
				else
					content_arr.push("</span>");
				i += 2;
			}
		}
	}
	console.log(content_arr);
	var str = content_arr.toString();
	var final_res = str.replace(/,/g, " ");
	return (final_res);
}

