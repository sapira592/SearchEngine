$(document).ready(function(){
	  	  
	  uploadFiles();
    hide();
    show();

});	



function uploadFiles(){
	$('#uploadFiles').submit(function(){

        setTimeout(
          function() 
          {
             $('#loaderBox').addClass("loader");
          }, 5000);
       
	 
        //The FormData object lets you compile a set of key/value pairs to send using XMLHttpRequest
        var formData = new FormData(this);
	        $.ajax({
            url: 'http://localhost/searchEngine/serverPHP/upload.php',
            type: 'POST',
            data: formData,
            cache: false,
            contentType: false,
            processData: false,
            success: function(res)
            {
                if(res.indexOf("ok") >= 0){
                   alert("Upload Success!");
                }
                else{
                    alert("Upload failed - No Files");
                }

                $('#loaderBox').removeClass("loader");
	        }
        });
        return false;
	});
}





function hide(){
    $('#hideDoc').submit(function(){
     
       var name = $('#hideDocLable').val();
       var type = 0;

         $.ajax({
           type: "post",
           url: "http://localhost/searchEngine/serverPHP/availability.php",
           data: {"name": name, "type":type}, // serializes the form's elements.
           success: function(res)
           {
                var ok = "ok";
                if(res.localeCompare(ok)){
                    alert("The doc has been hidden");
                }
                else{
                    alert("Doc does't exist");
                } 
           }
        });
        return false; //to prebet from submmision
    });    

}


function show(){
    $('#reshowDoc').submit(function(){
     
       var name = $('#reshowDocLable').val();
       var type = 1;

         $.ajax({
           type: "post",
           url: "http://localhost/searchEngine/serverPHP/availability.php",
           data: {"name": name, "type":type}, // serializes the form's elements.
           success: function(res)
           {
                var ok = "ok";
                if(res.localeCompare(ok)){
                    alert("The doc is available");
                }
                else{
                    alert("Doc does't exist");
                } 
           }
        });
        return false; //to prebet from submmision
    }); 

}




