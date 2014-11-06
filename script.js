/* Javascript for the courseboard mainpage.
Author: Franz Weidmann
10/2014
*/
/*
Calls a php script which creates the post.

@param int courseid id of the course
@param int moduleid of the plugin in the course
@param int courseboardid id of the plugin overall
@param String skey the sessionkey as a string

*/
function courseboard_postInsert(courseid, coursemoduleid, courseboardid, skey) {
    if ($.trim($('#postinputfield').val()).length != 0) {
        var post = $('#postinputfield').val().replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var name = $('#name').val();

        $.ajax({

            url:'courseboard_ajax.php',
            type:'POST',
            timeout:5000,
            data:{fnc : 'postInsert' , q:post , s:name, k:courseid, b:courseboardid, r:coursemoduleid, sesskey:skey},

            beforeSend : function() {
                $('.posts').hide();
                $('#postsloading').show();

            },
            success : function(data) {
                courseboard_courseboardRefresh(courseid, coursemoduleid, courseboardid, skey);

            }
        });

        $('#emptyFieldWarning').hide();
        $('#postinputfield').val('');

    } else {
        $('#emptyFieldWarning').show();
    }

}


/*

Function which will be called when a user rates a post.
This function the calls a php script which calculates the new
rating of the post.

@param int id id of the post
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param int courseboardid id of the plugin overall
@param String skey the sessionkey as a string

*/

function courseboard_rate (id, courseid, coursemoduleid, courseboardid, skey) {
    var stars = $('#selectStar' + id).val();

    if (stars != 'noStar') {

        switch (stars) {
            case 'oneStar':
                stars = 1;
            break;

            case 'twoStars':
                stars = 2;
            break;

            case 'threeStars':
                stars = 3;
            break;

            case 'fourStars':
                stars = 4;
            break;

            case 'fiveStars':
                stars = 5;
            break;
        }

        $.ajax ({
            url:'courseboard_ajax.php',
            type:'POST',
            timeout:5000,
            data:{q:id,fnc:'rate',k:courseid,r:coursemoduleid, b:courseboardid, h:stars,sesskey:skey},

            beforeSend:function() {
                $('.posts').hide();
                $('#postsloading').show();

            },

            success : function(){
                courseboard_courseboardRefresh(courseid, coursemoduleid, courseboardid, skey);

            }

        });
    }

}

/*
    Function which will be called when a user creates a comment.
    Calls a php script which insert the comment into the database

@param int id  id of the post
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param int courseboardid id of the plugin overall
@param String skey the sessionkey as a string

*/
function courseboard_commInsert(id, courseid, coursemoduleid, courseboardid, skey) {
    if ($.trim($('#commtxtarea' + id).val()).length != 0) {
        var commtext = $('#commtxtarea' + id).val().replace(/</g, '&lt;').replace(/>/g, '&gt;');
        var name = $('#name').val();

        $.ajax ({
            type:'POST',
            url:'courseboard_ajax.php',
            timeout:5000,
            data:{o:name,q:commtext,s:id,fnc:'commentInsert',k:courseid, r:coursemoduleid, b:courseboardid, sesskey:skey},

            beforeSend: function(){
                $('#commloading' + id).show();
                $('.commShow' + id).hide();
            },
            success: function(){
                // This changes the text when the first comment was written, that is the case when the attribute 'cn'
                // is equal 0.Change is like : 'Write a comment' to 'Show comments (1)'.
                // Otherwise the amount of comments will be increased with 1.
                if($('#commShow' + id).attr('cn') == 0) {
                    $('#commShow' + id).val($('#commShow' + id).attr('data') + ' (1)');
                    $('#commShow' + id).attr('cn','1');

                } else {
                    var commbtnval = $('#commShow' + id).val();
                    var amountcomments = parseInt($('#commShow' + id).attr('cn')) + 1;
                    $('#commShow' + id).val($('#commShow' + id).attr('data') + ' (' + amountcomments + ')');
                    $('#commShow' + id).attr('cn',amountcomments);

                }

                courseboard_commsRefresh(id , courseid, coursemoduleid, courseboardid, skey);
            }

        });

        $('#emptyCommFieldwarning' + id).hide();

    } else {
        $('#emptyCommFieldwarning' + id).show();
    }

}

/*
makes the comment section of a post visible

@param int id id of the post
*/

function courseboard_commShow(id) {
    $('#comments' + id).show();
    $('#commfield' + id).show();
    $('#commShow' + id).hide();
    $('#commHide' + id).show();

}


/*
hides the comment section of a post.

@param int id id of the post

*/
function courseboard_commHide(id) {
    $('#comments' + id).hide();
    $('#commfield' + id).hide();
    $('#commShow' + id).show();
    $('#commHide' + id).hide();
}

/*
gets the newest comments of a post and put them into
the commentssection of a post.

@param int id id of the post
@param int courseid id of the course
@param int moduleid of the plugin in the course
@param int courseboardid id of the plugin overall
@param String skey the sessionkey as a string
*/
function courseboard_commsRefresh(id, courseid, coursemoduleid, courseboardid, skey) {
    $.ajax({
            type:'POST',
            url:'courseboard_ajax.php',
            timeout:5000,
            data:{q:id,k:courseid,r:coursemoduleid,fnc:'commentsRefresh', b:courseboardid, sesskey:skey},

            beforeSend: function(){

                $('#commloading' + id).show();
                $('.commanShow' + id).hide();
            },

            success: function(data){
                $('.commanShow' + id).show(500,function(){
                    $('#commfield' + id).html(data);
                });

                $('#commloading' + id).hide();
                $('#commfield' + id).show();
            }

        });

}

/*
refreshs the courseboard with the newest
posts and comments.

@param int courseid id of the course
@param int moduleid of the plugin in the course
@param int courseboardid id of the plugin overall
@param String skey the sessionkey as a string
*/

function courseboard_courseboardRefresh(courseid, coursemoduleid, courseboardid, skey) {
    var sort = $('#sortmenu').val();

    $.ajax ({
            url:'courseboard_ajax.php',
            type:'POST',
            timeout:5000,
            data:{q:sort,fnc:'courseboardRefresh',k:courseid, r:coursemoduleid, b:courseboardid, sesskey:skey},

            beforeSend : function() {
                $('.posts').hide();
                $('#postsloading').show();

            },

            success : function(data) {

                $('#postsloading').hide();
                $('#maindiv').html(data);

            }
        });
}
