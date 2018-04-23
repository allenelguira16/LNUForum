<?php
	session_start();
	require_once('functions.php');
	$user = new user;
	$html = new html;
	if(IsSet($_POST['login'])){
		print($user->user_login($_POST['user'],$_POST['pass']));
	}
	elseif(IsSet($_POST['topic'])){
		print($html->topic($_POST['subject'],$_POST['course'],$_POST['topic_id']));
	}
	elseif(IsSet($_POST['answer'])){
		if(!empty($_POST['content'])){
			print($user->answer($_POST['content'],$_POST['topic_id'],$_SESSION['user_id']));
		}
	}
	elseif(IsSet($_POST['edit'])){
		if(!empty($_POST['content'])){
			print($user->edit_answer($_POST['content'],$_POST['topic_id'],$_SESSION['user_id']));
		}
	}
	elseif(IsSet($_POST['comment'])){
		if(!empty($_POST['content'])){
			print($user->comment($_POST['content'],$_POST['answer_id'],$_SESSION['user_id']));
		}
	}
	elseif(IsSet($_POST['logout'])){
		session_destroy();
		print(1);
	}
	elseif(IsSet($_POST['message'])){
		print($user->send_chat($_POST['message'],$_SESSION['user_id'],$_POST['id']));
	}
	elseif(IsSet($_POST['thestate'])){
		print($user->getState());
	}
	elseif(IsSet($_POST['answers_state'])){
		print($user->getAnswerState());
	}
	elseif(IsSet($_POST['comment_state'])){
		print($user->getCommentState());
	}
	elseif(IsSet($_POST['chat'])){
		//print(json_encode('value'));
		print($html->messages($_POST['id1'],$_POST['id2']));
	}
	elseif(IsSet($_POST['topic_id']) && IsSet($_POST['course'])){
		print($html->topic_header());
		print($html->topic($_POST['subject'],$_POST['course'],$_POST['topic_id']));
	}
	elseif(IsSet($_POST['clear'])){
		print($user->delete_chat($_SESSION['user_id'],$_POST['id']));
	}
	elseif(IsSet($_POST['answers'])){
		print($html->show_answers($_POST['tid']));
	}
	elseif(IsSet($_POST['comments'])){
		print($html->show_comments($_POST['answer_id']));
	}
	elseif(IsSet($_POST['search'])){
		print($html->search_engine($_POST['search']));
	}
	elseif(IsSet($_GET['search_topic'])){
		print($html->search_topics($_GET['subject'],$_GET['course'],$_GET['search_topic']));
	}
	elseif(IsSet($_POST['create'])){
		print($user->create_topic($_POST['course'],$_POST['subject'],$_POST['title'],$_POST['content'],$_POST['tags'],$_SESSION['user_id']));
	}
	elseif(IsSet($_POST['contenteditable'])){
		print($html->bbcodeparse($_POST['contenteditable']));
		//echo json_encode($response);
	}
	elseif(IsSet($_POST['username'])){
		print($user->username($_POST['username']));
	}
	elseif(IsSet($_POST['register'])){
		print($user->register($_POST['uname'],$_POST['fname'],$_POST['lname'],$_POST['pass1'],$_POST['course'],$_POST['yearlvl'],$_POST['email']));
	}
	elseif(IsSet($_FILES['file']['tmp_name'])){
		print($user->profile_pic($_FILES['file']['tmp_name'],$_FILES['file']['name']));
	}
	elseif(IsSet($_POST['del_topic'])){
		print($user->del_topic($_POST['topic_id']));
	}
	elseif(IsSet($_POST['del_answer'])){
		print($user->del_answer($_POST['answer_id']));
	}
	elseif(IsSet($_POST['emoji'])){
		print($html->bbcodeslist());
	}
	elseif(IsSet($_POST['action'])){
		if($_POST['action'] == 'members'){
			print($html->members());
		}
		elseif($_POST['action'] == 'course'){
			print($html->admin_courses());
		}
		elseif($_POST['action'] == 'stats'){
			print($html->stats());
		}
	}
	elseif(IsSet($_POST['add-course'])){
		print($user->add_course($_POST['course'],$_POST['desc']));
	}
	elseif(IsSet($_POST['add-subject'])){
		print($user->add_subject($_POST['course_id'],$_POST['subject'],$_POST['desc']));
	}
	elseif(IsSet($_POST['like'])){
		print($user->like($_POST['ref_id'],$_POST['ref'],$_POST['status']));
		//echo json_encode($_POST['ref_id'].$_POST['ref'].$_POST['status']);
	}
	elseif(IsSet($_POST['notifs'])){
		echo json_encode('GG');
	}
	elseif(IsSet($_POST['change'])){
		if(isset($_POST['change-fname'])){
			echo $user->update_user($_POST['change-fname'],'fname');
		}
		elseif(isset($_POST['change-lname'])){
			echo $user->update_user($_POST['change-lname'],'fname');
		}
		elseif(isset($_POST['change-username'])){
			echo $user->update_user($_POST['change-username'],'username');
		}
		elseif(isset($_POST['change-course'])){
			echo $user->update_user($_POST['change-course'],'course');
		}
		elseif(isset($_POST['change-email'])){
			echo $user->update_user($_POST['change-email'],'email');
		}
	}
	elseif(IsSet($_POST['resent'])){
		print($user->resent());
	}
	elseif(IsSet($_POST['verify'])){
		print($user->verify($_POST['code']));
	}
	elseif(IsSet($_POST['notif'])){
		print($user->getAnswerState());
	}
?>
