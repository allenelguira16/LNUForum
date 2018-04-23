var topic_change = 0,answer_change = 0,comment_change = 0;
var request;
var action = 'members';
var val;
$(document).ready(function(){
	up();
	var url = '/LNUForum/ajax/';
	$('.login').submit(function(){
		var main = $(this).hasClass('main-login');
		var parent = $(this).parent();
		var status = $('.main-login').siblings().hasClass('login-status');
		call_ajax($(this).serialize(),url,$(this).attr('method'), function(response){
			console.log(response);
			if(response == true){
				//location.reload();
				if(main){
					location.href = '/LNUForum/';
				}
				else{
					location.reload();
				}
			}
			else{
				if(main){
					if(status){
						$('.login-status').remove();
						parent.prepend('<span class="login-status">' + response + '</span>');
					}
					else{
						parent.prepend('<span class="login-status">' + response + '</span>');
					}
				}
				else{
					window.location.href = login_url();
				}
			}
		});
		return false;
	});
	$(document).on('submit', '#create-form', function(){
		var data = $(this).serializeArray();
		data.push({name:"content", value:$('#content').html()});
		call_ajax($.param(data),url,"POST", function(response){
			if(response.text == 'Verify'){
				location.href = "/LNUForum/Verify";
			}
				$('#response-msg').html(response.text);
			response_msg(response.txt);
		});
		return false;
	});
	$(document).on('submit', '#answer_js', function(e){
		var data = $(this).serializeArray();
		data.push({name:"content", value:$('#answer-content').html()});
		if($(this).children('#answer-context').children('.content-box').children().hasClass('placeholder') == false){
			call_ajax($.param(data),url,$(this).attr('method'), function(response){
				if(response){
					$('[contenteditable]').html('');
				}
			});
		}
		return false;
	});
	$(document).on('submit', '.comment_js', function(){
		//var thisme = $(this).parent().parent().siblings('.dyn_comment');
		var data = $(this).serializeArray();
		data.push({name:"content", value:$(this).children('.comment-context').children('.content-box').html()});

		//if($(this).children('.comment-context').children('.content-box').children().hasClass('placeholder') == false){
			call_ajax($.param(data),url,$(this).attr('method'), function(response){
				if(response){
					$('[contenteditable]').html('');
					comment_change = 1;
				}
			});
		//}
		return false;
	});
	$(document).on('submit', '#chat', function(){
		var data = $(this).serializeArray();
		data.push({name:"message", value:$(this).find('.content-box').html()});
		call_ajax($.param(data),url,$(this).attr('method'), function(response){
			//if(response){
				$('#messages').scrollTop($(this).prop("scrollHeight"));
				$('[contenteditable]').html('');
				//alert('gg');
			//}
		});
		return false;
	});	
	$(document).on('submit', '#edit_js', function(){
		var content = $(this).parents('#main-content').find('.edit-context').html();
		var form = $(this).serializeArray();
		form.push({name:"content", value:content},{name:'edit', value:'true'});
		call_ajax($.param(form),url,$(this).attr('method'), function(response){
			console.log(response);
		});
		console.log($.param(form));
		return false;
	});
	$(document).on('keydown', '#chat-content', function(e){
		if((e.keyCode || e.which) == 13 && !e.shiftKey ){
			$(this).parent().submit();
			return false;
		}
	});
	$('.logout').click(function(){
		call_ajax({logout:true,id:$(this).attr('data-id')},url,'POST', function(response){
			location.reload();
		});
		return false;
	});
	$(document).on('click', '.user-open', function(){
		$(this).siblings('.user-menu').toggleClass('hide');
	});
	$(document).on('click', '.menu-btn', function(){
		$(this).siblings('.menu-block').toggleClass('hide');
	});
	$(document).on('click', '.link-btn', function(){
		var val = $(this).attr('data-ref');
		if(val == 'clear-convo'){
			call_ajax({clear:true,id:$(this).attr('data-id')},url,'POST', function(error){
				if(error == true){
					$('#response').html('Error Deleting the convo');
				}
			});
			return false;
		}
	});
	$(document).on('click', '.up', function(){
		$('html, body').animate({scrollTop: "0px"});
	});	
	$(document).on('click', '.emoji', function(){
		var alt = $(this).attr('alt');
		var emoji = $(this).attr('src');
		$('#content').append('<img alt="' + alt + '" src="' + emoji + '" class=emoji>');
	});
	$(document).on('click', '#answer-context .emoji', function(){
		var alt = $(this).attr('alt');
		var emoji = $(this).attr('src');
		if(!$(this).hasClass('inserted')){
			$('#answer-content').append('<img alt="' + alt + '" src="' + emoji + '" class="emoji inserted">');
		}
	});
	$(document).on('click', '.comment-context .emoji', function(){
		var alt = $(this).attr('alt');
		var emoji = $(this).attr('src');
		if(!$(this).hasClass('inserted')){
			$(this).parent().parent().siblings('.content-box').append('<img alt="' + alt + '" src="' + emoji + '" class="emoji inserted">');
		}
	});
	$(document).on('click', '#chat-context .emoji', function(){
		var alt = $(this).attr('alt');
		var emoji = $(this).attr('src');
		if(!$(this).hasClass('inserted')){
			$(this).parents('#chat-context').find('.content-box').append('<img alt="' + alt + '" src="' + emoji + '" class="emoji inserted">');
		}
	});
	var previous = '';
	$(document).on('mouseup', function(e){
		var response = $('#response-box');
		var emojilist = $('.emojilist');
		var user = $('#mini-user-config');
		var btn = $('.btn');
		var user_change = $('.tr');
		if((!emojilist.is(e.target) && emojilist.has(e.target).length === 0) && !$('.btn').is(e.target)){
			emojilist.html("");
			emojilist.addClass('hide');
		}
		if(!$('.menu').is(e.target) && $('.menu').has(e.target).length === 0 && !$('.btn').is(e.target)){
			$('.menu').addClass('hide');
		}
		if(!btn.is(e.target)){
			btn.removeClass('btn-clicked');
			btn.removeClass('hide');
		}
		if(!user.is(e.target) && !user.has(e.target).length && !$('.btn').is(e.target)){
			user.addClass('hide');
		}
		if(!$('#search-res').is(e.target) && !$('#search-res').has(e.target).length){
			$('#search-res').fadeOut();
		}
	});
	$(document).on('keyup', '[contenteditable]', function(e){
		var list = {	
			"o:)" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Angel.png" class="emoji">',
			"&gt;:o" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Super_Angry_Face_Emoji_ios10.png" class="emoji">',
			":)" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Slightly_Smiling_Emoji_Icon.png"class="emoji">',
			":D" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Smiling.png" class="emoji">',
			":(" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Unhappy_Face_Emoji_Icon_ios10.png" class="emoji">',
			":'(" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Dissapointed_but_recovered.png" class="emoji">',
			":P" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Tongue_Out_Emoji_1.png" class="emoji">',
			"o.O" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Surprised_Emoji_Icon.png" class="emoji">',
			";)" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Smirk.png" class="emoji">',
			":o" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Surprised_Emoji_Icon.png" class="emoji">',
			"-_-" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Expressionless_Emoji_Icon.png" class="emoji">',
			":*" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Kiss_Emoji_Icon_2.png" class="emoji">',
			"^_^" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Shy_Emoji_Icon.png" class="emoji">',
			"8-)" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Nerd_Emoji_Icon.png" class="emoji">',
			"8|" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/SunGlass.png" class="emoji">',
			":|" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Confused.png" class="emoji">',
			":3" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Confounded.png" class="emoji">',
			'&lt;3' : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Heart_Eyes_Emoji_2.png" class="emoji">',
			"&gt;:(" : '<img alt="emoji" src="http://localhost/LNUForum/includes/php/emoji/emojiv2/Very_Mad_Emoji_Icon_ios10.png" class="emoji">'
		};
		for (code in list) {
			if($(this).html().indexOf(code) >= 0){
				console.log($(this).html().substring(0,code.length-1));
				replacement = list[code];
				$(this).html($(this).html().replace(code, replacement));
				range = document.createRange();
				selection = window.getSelection();
				range.setStart(this, this.childNodes.length);
				range.collapse(true);
				selection.removeAllRanges();
				return selection.addRange(range);
			}
		}
	});
	$(document).on('submit', '#search-form', function(){
		var search = $(this).find('.search-input').val();
		window.location.href = '/LNUForum/Search/' + search;
		return false;
	});
	$(document).on('keyup', '.search-input', function(){
		var parent = $(this).parent();
		call_ajax({search:$(this).val()},url,'POST', function(response){
			if(response != 'empty'){
				$('#search-res').fadeIn();
				$('#search-res').html(response);
			}
			else{
				$('#search-res').fadeOut();
			}
		});
	});
	var flag = 0;
	var old;
	var user_bool = false,id_bool = false, pass1_bool = false, pass2_bool = false, email_bool = false;
	$(document).on('keyup', '#register input', function(){
		var x = $(this).attr('name');
		var val = $(this).val();
		var image = $(this).siblings('.input-status');
		if(x == 'uname'){
			call_ajax({username:val},url,"POST", function(response){
				if(response == true){
					image.html("<img src=/LNUForum/includes/Css/css-icons/check.png>");
					id_bool = true;
				}
				else{
					image.html("<img src=/LNUForum/includes/Css/css-icons/wrong.png>");
					id_bool = false;
				}
			});
		}
		else if(x == 'pass1'){
			if(val.match(/[a-zA-Z0-9]/) && val.length > 8){
				$(this).siblings('.input-status').html("<img src=/LNUForum/includes/Css/css-icons/check.png>");
				pass1_bool = true;
			}
			else{
				$(this).siblings('.input-status').html("<img src=/LNUForum/includes/Css/css-icons/wrong.png>");
				pass1_bool = false;
			}
		}
		else if(x == 'pass2'){
			if(val == $('input[name="pass1"]').val() && val.match(/[a-zA-Z0-9]/) && val.length > 8){
				$(this).siblings('.input-status').html("<img src=/LNUForum/includes/Css/css-icons/check.png>");
				pass2_bool = true;
			}
			else{
				$(this).siblings('.input-status').html("<img src=/LNUForum/includes/Css/css-icons/wrong.png>");
				pass2_bool = false;
			}
		}
		else if(x == 'email'){
			if(val.match(/^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/)){
				$(this).siblings('.input-status').html("<img src=/LNUForum/includes/Css/css-icons/check.png>");
				email_bool = true;
			}
			else{
				$(this).siblings('.input-status').html("<img src=/LNUForum/includes/Css/css-icons/wrong.png>");
				email_bool = false;
			}
		}
	});
	$(document).on('submit', '#register', function(){
		if(id_bool == true && pass1_bool == true && pass2_bool == true && email_bool == true){
			call_ajax($(this).serialize(),url,"POST", function(response){
				if(response == true){
					window.location.href = "/LNUForum/Login";
				}
			});
		}
		return false;
	});
	$(document).on('submit', '#profile-form', function(){
		var file_data = $('#profile-input').prop('files')[0];
		var form_data = new FormData();
		form_data.append('file',file_data);
		$.ajax({
			url: url, // point to server-side PHP script 
			dataType: 'text',  // what to expect back from the PHP script, if anything
			cache: false,
			contentType: false,
			processData: false,
			data: form_data,                         
			type: 'post',
			success: function(response){
				if(response){
					window.location.reload();
				}
				//console.log(response);
			}
	     });
		return false;
	});
	$('#profile-input').change(function(){
		imgPreview(this);
	});
	$(document).on('click', '.btn',function(){
		var x = $(this).attr('data-ref');
		var y = $(this);
		if(x == 'close-popup'){
			$('#response').hide();
		}
		else if(x == 'verify-email'){
			$('#verify').submit();
		}
		else if(x == 'show'){
			$(this).siblings('.menu').toggleClass('hide');
		}
		else if(x == 'topic' || x == 'answer'){
			var ref_id = $(this).attr('data-id');
			var status = $(this).attr('status');
			var likeform = {ref_id:$(this).attr('data-id'),ref:x,status:$(this).attr('data-status'),like:true}
			call_ajax(likeform,url,"POST", function(response){
				if(response){
					if(x == 'topic'){
						topic_change = 1;
					}
					else if(x == 'answer'){
						answer_change = 1;
					}
				}
				console.log(response);
			});
		}
		else if(x == 'del-topic'){
			call_ajax({topic_id:$(this).attr('data-get'),del_topic:true},url,"POST",function(response){
				if(response == true){
					window.location.href = 'javascript:history.go(-1)';
				}
				console.log(response);
			});
		}
		else if(x == 'del-answer'){
			call_ajax({answer_id:$(this).attr('data-get'),del_answer:true},url,"POST", function(response){
				answer_change = 1;
			});
		}
		else if(x == 'emoji'){
			var emoji = $(this).siblings('.emojilist');
			call_ajax({emoji:true},url,"POST", function(response){
				if(!emoji.hasClass('hide')){
					emoji.fadeOut('slow').addClass('hide');
					emoji.fadeOut('slow').html("<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>");
				}
				else{
					emoji.removeClass('hide');
					emoji.html("<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>");
					//
					setTimeout(function(){
						emoji.fadeIn('slow').html(response);
					}, 200);
				}
			});
		}
		else if(x == 'Courses'){
			admin('course');
		}
		else if(x == 'Stats'){
			admin('stats');
		}
		else if(x == 'Members'){
			admin('members');
		}
		else if(x == 'add-course'){
			$('.popup-form').removeClass('hide');
			$('#add-course').removeClass('hide');
		}
		else if(x == 'close-form'){
			$('.popup-form').addClass('hide');
			$('#add-course').addClass('hide');
		}
		else if(x == 'add-subject'){
			$('.popup-form').removeClass('hide');
			$('#add-subject').removeClass('hide');
		}
		else if(x == 'open-content'){
			y.parents('.answer-content').find('.comment_js').toggleClass('hide');
			y.parents('.answer-content').find('.comment-context').toggleClass('clicked');
		}
		else if(x == 'open-chat-menu'){
			y.parents('.chat-menu').find('.menu-block').toggleClass('hide');
		}
		else if(x == 'open-user'){
			$('#mini-user-config').toggleClass('hide');
		}
		else if(x == 'edit-topic'){
			y.parents('.user-content').find('.content-topic').attr({contenteditable:'true',class:'edit-context'});
			$("#answer_js").attr('id','edit_js');
			y.parents('#main-content').find('input[name=answer]').remove();
			$('#answer-context').hide();
		}
		else if(x == 'edit-settings'){
			/*val = $(this).html();

			y.children('.td').last().attr('contenteditable',true);*/
			y.children('.edit').html('save');
			y.attr('data-ref','save-settings');
			var options = y.attr('data-options');
			var value = y.children('.td').last().html();
			y.children('.td').last().html('<input type="textbox" value="'+ value +'" name="'+ options +'">');
			//console.log(value);
		}
		else if(x == 'save-settings'){
			var id = y.attr('data-id');
			var option = y.attr('data-options');
			var form = $(this).serializeArray();
			form.push({name:"id", value:id},{name:"change", value:true});
			//var form = {id:id,option:option,change:true}
			call_ajax($.param(form),url,"POST",function(response){
				if(response == true){
					window.location.reload();
				}
				else{
					$('#response').show();
					$('#response-header').html(response.header);
					$('#response-msg').html(response.msg);
				}
			});
			console.log($.param(form));
		}
		else if(x == 'resend'){
			call_ajax({resent:true},url,"POST",function(response){
				if(response == true){
					$('#response').removeClass('hide');
					$('#response-msg').html('Email Sent');
					$("#response").css({left:"-100%"}).animate({"left":"0px"}, "fast");
				}
				console.log(response);
			});
		}
		else if(x == 'chat'){
			var val = y.attr('data-val');
			window.location.href = '/LNUForum/Chat/' + val;
		}
		return false;
		$(this).toggleClass('btn-clicked');
	}).children().on('click', '.table input[type="textbox"]' , function(e){
		return false;
	});
	$(document).on('keydown', '.td', function(e){
		if(e.keyCode == 13){
			return false;
		}
	});
	$(document).on('submit', '#add-course', function(){
		call_ajax($(this).serialize(),url,"POST", function(response){
			location.reload();
		});
		return false;
	});
	$(document).on('submit', '#add-subject', function(){
		call_ajax($(this).serialize(),url,"POST", function(response){
			location.reload();
		});
		return false;
	});
	$(document).on('submit', '#verify', function(){
		call_ajax($(this).serialize(),url,"POST",function(response){
			if(response == true){
				setTimeout(function() {
					response_msg('Account Verified');
				    window.location.href = 'javascript:history.go(-1)';
				}, 5000);
			}
			else{
				response_msg('Incorrect Code');
			}
		});
		return false;
	});
	$(document).on('mousedown', '.emoji', function(e){
	    var dr = $(this).addClass("drag").css("cursor", "move");
	    height = dr.outerHeight();
	    width = dr.outerWidth();
	    ypos = dr.offset().top + height - e.pageY,
	    xpos = dr.offset().left + width - e.pageX;
   		$(document.body).on('mousemove', function(e){
	        var itop = e.pageY + ypos - height;
	        var ileft = e.pageX + xpos - width;
	        if(dr.hasClass("drag")){
	            dr.offset({top: itop,left: ileft});
	        }
	    }).on('mouseup', function(e){
	            dr.removeClass("drag");
	    });
	});
});

function response_msg(msg){
		$('#response').removeClass('hide');
		$('#response-msg').html(msg);
		$("#response").css({left:"-100%"}).animate({"left":"0px"}, "fast");

	setTimeout(function(){
		$("#response").css({left:"0%"}).animate({"left":"100%"}, "fast");
	},2500);
}

function update_notif(user_id,from_id,table,date){
	var form = {user_id:user_id,from_id:from_id,table:table,date:date,notif:true}
	call_ajax(form,url,"POST", function(response){
		console.log(response);
	});
}

function admin(input){
	call_ajax({action:input},url,"POST", function(response){
		$('#admin-content').html(response);
		$('#admin-content').removeClass('loader');
	});
}

function imgPreview(img){
	if(img.files[0] && img.files){
		var freader = new FileReader();
		freader.onload = function(e){
			$('#profile-form').removeClass('hide');
			$('#profile').html('<img src="' + e.target.result + '">');
			$('label[for="profile-input"]').html('<img class="camera" src="/LNUForum/Icons/submit_photo.png">');
			$('label[for="profile-input"]').attr('for','profile-btn');
		}
		freader.readAsDataURL(img.files[0]);
	}
}


var url = '/LNUForum/ajax/';

function up(){
	if($("section.content").height() < $("body").height()){
		$('.up').removeClass('hide');
	}
}

function base_url(){
	var url = window.location.origin?window.location.origin+'/':window.location.protocol+'/'+window.location.host+'/';
	return url + 'LNUForum/';
}
function login_url(){
	var url = window.location.origin?window.location.origin+'/':window.location.protocol+'/'+window.location.host+'/';
	return url + 'LNUForum/Login';
}



function call_ajax(form,url,method,callback){
	/*if(request){
		request.abort();
	}
	var res;*/
	//request = \
	$.ajax({
		async: true,
		url: url,
		type: method,
		data: form,
		dataType: "JSON",
		success: callback,
		error: function(request,status,error){
			console.log(request.responseText);
		},
	});
	//return request;
}

