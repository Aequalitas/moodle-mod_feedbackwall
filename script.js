
/* Javascript for the feedbackwall mainpage.

Author: Franz Weidmann 

*/

//This part will be called when the site finished loading.

$(function(){

// creates two @@ when you press return in the feedbackinputfield.

	$("#feedbackinputfield").keyup(function(e) {
		
		if ($("#feedbackinputfield").is(":focus")) {
			if (e.which == 13 ) {
				
				var text = $("#feedbackinputfield").val().replace(/</g, "&lt;").replace(/>/g, "&gt;");
				text = text + "@@";
				$("#feedbackinputfield").val(text);
			}
		}
		
	});
	
	
});

////////

/*
creates two @@ when you press return in the commentinputfield.

@param object e event that appears when a key is pressed
@param int id id of the feedback in the feedbackwall

*/
function textjump(e,id)
{
	if ($("#commtxtarea"+id).is(":focus")) {
			if (e.which == 13 ) {
				
				var text =$("#commtxtarea"+id).val().replace(/</g, "&lt;").replace(/>/g, "&gt;");
				text = text + "@@";
				$("#commtxtarea"+id).val(text);
			}
		}
}

/*
Calls a php script which creates the feedback.


@param int courseid id of the course
@param int moduleid of the plugin in the course
@param string date of creation of the feedback
*/
function feedbackInsert(courseid,coursemoduleid,dateInt)
	{

		
		if($.trim($("#feedbackinputfield").val()).length !=0)
		{
			var feedback=$("#feedbackinputfield").val().replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/@@/g,"<br>");
			var name=var name=$("#name").text();
			
			
			
			$.ajax({
			
				
				url:"view.php",
				type:"POST",
				data:{fnc : "feedbackInsert" , q:feedback , s:name, l:courseid,k:coursemoduleid,r:dateInt},
				
				beforeSend : function(){
				
					$(".feedbacks").hide();
					$("#feedbacksloading").show();
				
				},
				
				success : function(){
				
					
					feedbackwallRefresh(courseid,coursemoduleid,dateInt);
					
				}
			
			});
			
			$("#emptyFieldWarning").hide();
			$("#feedbackinputfield").val("");
			
		}
		else
		{
			$("#emptyFieldWarning").show();
		}
		
		
	}


/*
	
Function which will be called when a user rates a feedback.
This function the calls a php script which calculates the new 
rating of the feedback.

@param int id id of the feedback
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param string date of creation of the feedback

*/

function rate(id,courseid,coursemoduleid,date)
{
	var stars = $("#selectStar"+id).val();
	
	if(stars != "noStar") 
	{
	
		switch(stars)
		{
			case "oneStar":
			stars = 1;
			break;
			
			case "twoStars":
			stars = 2;
			break;
			
			case "threeStars":
			stars = 3;
			break;
			
			case "fourStars":
			stars = 4;
			break;
			
			case "fiveStars":
			stars = 5;
			break;
		}
		
		
		
		$.ajax({
		
			url:"view.php",
			type:"POST",
			
			data:{q:id,fnc:"rate",s:courseid,k:coursemoduleid,h:stars},
			
			beforeSend:function(){
			
				
					$(".feedbacks").hide();
					$("#feedbacksloading").show();
						
			},
						
			success : function(){
						
					feedbackwallRefresh(courseid,coursemoduleid,date);
					
			}
		
		});
	}
	
}

/*
Function which clears the focused textarea

@param int id id of the feedback
*/
function clearArea(id)
{
	
	$("#"+id).val("");
}


/*
	Function which will be called when a user creates a comment.
	Calls a php script which insert the comment into the database

@param int id  id of the feedback
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param string date of creation of the feedback


*/
function commInsert(id,courseid,coursemoduleid,date)
{
		
		if($.trim($("#commtxtarea"+id).val()).length !=0)
		{
			var commtext= $("#commtxtarea"+id).val().replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/@@/g,"<br>");
			var name = var name=$("#name").text();
			
			
			
			$.ajax({
				
				type:"POST",
				url:"view.php",
				data:{o:name,q:commtext,s:id,fnc:"commentInsert",k:courseid,r:coursemoduleid,l:date},
				
				beforeSend: function(){
					
					$("#commloading"+id).show();
					$(".commShow"+id).hide();
				
				},
				
				success: function(){
				
					commsRefresh(id,courseid,coursemoduleid,date);
				}
			
			});
			
			$("#emptyCommFieldwarning"+id).hide();
			
		}
		else
		{
			$("#emptyCommFieldwarning"+id).show();
		}
	
}

/*
makes the comment section of a feedback visible

@param int id id of the feedback
*/

function commShow(id)
{

	$("#comments"+id).show();
	$("#commfield"+id).show();
	$("#commShow"+id).hide();
	$("#commHide"+id).show();
	
}


/*
hides the comment section of a feedback.

@param int id id of the feedback

*/
function commHide(id)
{
	$("#comments"+id).hide();
	$("#commfield"+id).hide();
	$("#commShow"+id).show();
	$("#commHide"+id).hide();
}

/*
gets the newest comments of a feedback and put them into 
the commentssection of a feedback.

@param int id id of the feedback
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param string date of creation of the feedback
*/
function commsRefresh(id,courseid,coursemoduleid,dateInt)
{
	
	
	$.ajax({
					
				type:"POST",
				url:"view.php",
				data:{q:id,k:courseid,r:coursemoduleid,fnc:"commentsRefresh",d:dateInt},
				
				beforeSend: function(){
					
					$("#commloading"+id).show();
					$(".commanShow"+id).hide();
				
				},

				success: function(data){
				
					$(".commanShow"+id).show(500,function(){
					
						$("#commfield"+id).html(data);
						
					
					});
					
					$("#commloading"+id).hide();
					
				}	
					
			});
	
}

/*
refreshs the feedbackwall with the newest 
feedbacks and comments.

@param int courseid id of the course
@param int moduleid of the plugin in the course
@param string date of creation of the feedback
*/

function feedbackwallRefresh(courseid,coursemoduleid,dateInt)
{
	
	var sort=$("#sortmenu").val();
	
	
	
	$.ajax({
		
			
		
			url:"view.php",
			type:"POST",
			data:{q:sort,fnc:"feedbackwallRefresh",s:courseid,l:coursemoduleid,d:dateInt},
			
			
			beforeSend : function(){
					
				$(".feedbacks").hide();
				$("#feedbacksloading").show();
					
			},
					
			success : function(data){
					
				$(".feedbacks").show(500,function(){
					
					$("#maindiv").html(data);
				
				});
				$("#feedbacksloading").hide();		
				
			}
		
		});
}
