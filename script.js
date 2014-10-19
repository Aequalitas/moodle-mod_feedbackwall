
/* Javascript for the feedbackwall mainpage.

Author: Franz Weidmann 
10/2014
*/


/*
Calls a php script which creates the feedback.


@param int courseid id of the course
@param int moduleid of the plugin in the course
@param String skey the sessionkey as a string

*/
function feedbackwall_feedbackInsert(courseid, coursemoduleid, skey) {
    if($.trim($("#feedbackinputfield").val()).length !=0) {
        var feedback = $("#feedbackinputfield").val().replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var name = $("#name").val();

        $.ajax({

            url:"ajaxquery.php",
            type:"POST",
            data:{fnc : "feedbackInsert" , q:feedback , s:name, k:courseid, r:coursemoduleid, sesskey:skey},

            beforeSend : function() {
                $(".feedbacks").hide();
                $("#feedbacksloading").show();

            },
            success : function(data) {
                feedbackwall_feedbackwallRefresh(courseid, coursemoduleid, skey);

            }
        });

        $("#emptyFieldWarning").hide();
        $("#feedbackinputfield").val("");

    } else {
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
@param String skey the sessionkey as a string

*/

function feedbackwall_rate(id, courseid, coursemoduleid, skey)
{
    var stars = $("#selectStar"+id).val();

    if(stars != "noStar") {

        switch(stars) {
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
            url:"ajaxquery.php",
            type:"POST",
            data:{q:id,fnc:"rate",k:courseid,r:coursemoduleid,h:stars,sesskey:skey},

            beforeSend:function() {
                $(".feedbacks").hide();
                $("#feedbacksloading").show();

            },

            success : function(){
                feedbackwall_feedbackwallRefresh(courseid,coursemoduleid,skey);

            }

        });
    }

}

/*
Function which clears the focused textarea

@param int id id of the feedback
*/
function feedbackwall_clearArea(id) {
    $("#"+id).val("");
}


/*
	Function which will be called when a user creates a comment.
	Calls a php script which insert the comment into the database

@param int id  id of the feedback
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param String skey the sessionkey as a string

*/
function feedbackwall_commInsert(id, courseid, coursemoduleid, skey)
{
    if($.trim($("#commtxtarea"+id).val()).length !=0) {
        var commtext = $("#commtxtarea"+id).val().replace(/</g, "&lt;").replace(/>/g, "&gt;");
        var name = $("#name").val();

        $.ajax({
            type:"POST",
            url:"ajaxquery.php",
            data:{o:name,q:commtext,s:id,fnc:"commentInsert",k:courseid,r:coursemoduleid,sesskey:skey},

            beforeSend: function(){
                $("#commloading"+id).show();
                $(".commShow"+id).hide();
            },
            
            success: function(){
                feedbackwall_commsRefresh(id,courseid,coursemoduleid,skey);
            }

        });

        $("#emptyCommFieldwarning"+id).hide();
        
        } else {
            $("#emptyCommFieldwarning"+id).show();
        }

}

/*
makes the comment section of a feedback visible

@param int id id of the feedback
*/

function feedbackwall_commShow(id) {
    $("#comments"+id).show();
    $("#commfield"+id).show();
    $("#commShow"+id).hide();
    $("#commHide"+id).show();

}


/*
hides the comment section of a feedback.

@param int id id of the feedback

*/
function feedbackwall_commHide(id) {
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
@param String skey the sessionkey as a string
*/
function feedbackwall_commsRefresh(id, courseid, coursemoduleid, skey) {
    $.ajax({
            type:"POST",
            url:"ajaxquery.php",
            data:{q:id,k:courseid,r:coursemoduleid,fnc:"commentsRefresh",sesskey:skey},

            beforeSend: function(){

                $("#commloading"+id).show();
                $(".commanShow"+id).hide();
            },

            success: function(data){

                $(".commanShow"+id).show(500,function(){
                    $("#commfield"+id).html(data);
                });

                $("#commloading"+id).hide();
                $("#commfield"+id).show();
            }

        });

}

/*
refreshs the feedbackwall with the newest 
feedbacks and comments.

@param int courseid id of the course
@param int moduleid of the plugin in the course
@param String skey the sessionkey as a string
*/

function feedbackwall_feedbackwallRefresh(courseid, coursemoduleid, skey) {
    var sort = $("#sortmenu").val();

    $.ajax({
            url:"ajaxquery.php",
            type:"POST",
            data:{q:sort,fnc:"feedbackwallRefresh",k:courseid, r:coursemoduleid, sesskey:skey},
            
            beforeSend : function() {
                $(".feedbacks").hide();
                $("#feedbacksloading").show();

            },

            success : function(data) {
                $("#feedbacksloading").hide();
                $("#maindiv").html(data);

            }
        });
}
