<!DOCTYPE html>
<html lang='EN'>
<?php require_once('head.php');?>
<body id="loader">
	<div id="main">
		<header id="header-main" class="clearfix">
			<div class="content">
				<div id="header-logo">
					<a href="/LNUForum/">
						<span>LNUForum</span>
						<img src="/LNUForum/includes/css/css-icons/lnulogo.png" alt="Lyceans">
					</a>
					<?php 
						if(IsSet($_SESSION['user_id'])){
							echo "
								<a href='/LNUForum/Notifications' data-ref='open-notif' id='notif'>
									<span>Notifications</span>
									<img>
								</a>
								<a href='/LNUForum/Chat' data-ref='open-message'>
									<span>Chat</span>
									<img>
								</a>
							";
						}
					?>
				</div>
				<div id="header-profile">
					<?php echo (
						IsSet($_SESSION['user_id'])) ? 
						"<span>{$html->header_profile($_SESSION['user_id'])}</span>" 
						: 
						"<span style='color: #000; cursor: pointer;' class='logintxt btn' data-ref='open-user'>Login</span>";
					?>
					<?php
						if(!isset($_SESSION['user_id'])){
							print($html->login());
						}
						else{
							print($html->user($_SESSION['user_id']));
						}
					?>
				</div>
				<div id="header-search-box">
					<form action="ajax/data-handler.php" id="search-form">
						<input aria-label='search' type="text" name="text" placeholder="search" class='search-input' autocomplete="off">
						<label for="search-btn" class="img-btn"><img alt="search" src="/LNUForum/includes/Css/css-icons/search-btn.png"></label>
						<input type="submit" name="search" id="search-btn" style="display: none;">
					</form>
					<div id="search-res"></div>
				</div>
			</div>
		</header>
		<nav class='clearfix' id='main-nav'>
			<div class='content'>
				<?php 
					if(isset($_GET['action'])){
						if($_GET['action'] == 'search'){
							echo "
								<h1>Search Page</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'profile'){
							echo "
								<h1>". ((isset($_SESSION['user_id']) && $user->getUID($_GET['user']) == $_SESSION['user_id']) ? "Profile Page": "Viewing {$_GET['user']}")."</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'create'){
							echo "
								<h1>Post New Topic</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a> / <a href='/LNUForum/{$_GET['course']}/'>Course</a> / <a href='/LNUForum/{$_GET['course']}/{$_GET['subject']}/1/'>Subject</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'Register'){
							echo "
								<h1>Registration</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'Admin'){
							echo "
								<h1>Control Panel</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'Login'){
							echo "
								<h1>Login</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'settings'){
							echo "
								<h1>Account Settings</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'notif'){
							echo "
								<h1>Notifications</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
						elseif($_GET['action'] == 'message'){
							echo "
								<h1>Chat</h1>
								<span style='float: right;'>
									<a href='/LNUForum/'>Home</a>
								</span>
							";
						}
					}
					elseif(IsSet($_GET['topic_id'])){
						echo "
							<h1>{$_GET['title']}</h1>
							<span style='float: right;'>
								<a href='/LNUForum/'>Home</a> / <a href='/LNUForum/{$_GET['course']}/'>Course</a> / <a href='/LNUForum/{$_GET['course']}/{$_GET['subject']}/1/'>Subject</a>
							</span>
						";
					}
					elseif(isset($_GET['subject'])){
						echo "
							<h1>{$html->getSubName($_GET['subject'])}</h1>
							<span style='float: right;'>
								<a href='/LNUForum/'>Home</a> / <a href='/LNUForum/{$_GET['course']}/'>Course</a> / <a href='/LNUForum/{$_GET['course']}/{$_GET['subject']}/{$_GET['page']}/'>Subject</a>
							</span>
						";
					}
					elseif(isset($_GET['course'])){
						echo "
							<h1>{$html->getCourseName($_GET['course'])}</h1>
							<span style='float: right;'>
								<a href='/LNUForum/'>Home</a> / <a href='/LNUForum/{$_GET['course']}/'>Course</a>
							</span>
						";
					}
					else{
						echo "
							<h1>Home</h1>
						";
					}
				?>
			</div>
		</nav>
		<section class="content">
			<?php 
				$contentclass = (IsSet($_GET['topic_id'])) ? 'main-topic' : '';
				$contentclass .= (IsSet($_GET['course']) && !IsSet($_GET['topic_id'])) ? 'wide-content' : '';
				$contentclass .= (IsSet($_GET['action']) && $_GET['action'] == 'Admin') ? 'admin' : '';
				$contentclass .= (IsSet($_GET['action']) && $_GET['action'] == 'message') ? 'chat' : '';
			?>
			<article  class="content <?php print($contentclass);?>">
				<?php 
					if(isset($_GET['action'])){
						if(IsSet($_SESSION['user_id'])){
							if(isset($_GET['user']) && $_GET['action'] == 'message'){
								print($html->message_box($_GET['user']));
							}
							elseif(!isset($_GET['user']) && $_GET['action'] == 'message'){
								header("Location: /LNUForum/Chat/{$html->getFirstChat()}");
							}
							elseif($_GET['action'] == 'profile'){
								print($html->main_profile($_GET['user']));
							}
							elseif($_GET['action'] == 'create'){
								print($html->create_topic($_GET['course'],$_GET['subject'],$_SESSION['user_id']));
							}
							elseif($_GET['action'] == 'settings'){
								print($html->settings());
							}
							elseif($_GET['action'] == 'verify'){
								printf($html->verify());
							}
							elseif($_GET['action'] == 'notif'){
								printf($html->notif());
							}
						}
						elseif(!isset($_SESSION['user_id'])){
							if($_GET['action'] == 'Register'){
								print($html->register());
							}
							elseif($_GET['action'] == 'Login'){
								print($html->main_login());
							}
							else{
								header("Location: /LNUForum/");
							}
						}
						elseif(isset($_SESSION['admin'])){
							if($_GET['action'] == 'Admin'){
								print($html->admin());
							}
						}
					}
					elseif(isset($_GET['topic_id'])){
						//print($html->nav($_GET['subject'],$_GET['course'],0));
						printf($html->topic_layout());
					}
					elseif(isset($_GET['subject'])){
						print($html->subject_only($_GET['subject'],$_GET['course'],$_GET['page']));
					}
					elseif(isset($_GET['course'])){
						print($html->course_only($_GET['course']));
						print($html->active_topics($_GET['course']));
					}
					elseif(!IsSet($_GET['course'])){
						print($html->courses());
						//print($html->lounge());
					}
				?>
			</article>
		</section>
		<?php 
			print($html->footer());
			if(IsSet($_GET['topic_id'])){
				print($html->up());
			}
		?>
	</div>
	<?php if(IsSet($_GET['action']) && IsSet($_SESSION['user_id']) && $_GET['action'] == 'message'):?>
	<script type="text/javascript">
		var num_rows = 0;
		var change = 1;
		function ChatState(){
			call_ajax({thestate:true},url,'POST', function(response){
				if(response){
					if(response == "empty"){
						change = 1;
						update_chat();
					}	
					else if(num_rows == 0){
						num_rows = response;
						change = 1;
						update_chat();
					}
					else if(num_rows != response){
						num_rows = response;
						change = 1;
						update_chat();
					}
				}
			});
		}
		function update_chat(){
			var form = {chat:true,id1:'<?php echo $_SESSION['user_id'];?>',id2:'<?php echo $_GET['user'];?>'}
			call_ajax(form,url,'POST', function(response){
				if(change == 1){
					$('#messages').html(response);
					$('#messages').scrollTop($('#messages').prop("scrollHeight"));
					$('#messages').removeClass('loader');
					change = 0;
				}
			});
		}
		setInterval(ChatState,500);
	</script>
	<?php elseif(IsSet($_GET['topic_id'])):?>
	<script type="text/javascript">
	var answer_form = {answers:true,tid:'<?php echo $_GET['topic_id'];?>'}
	var topic_form = {topic:true,topic_id:'<?php echo $_GET['topic_id'];?>',course:'<?php echo $_GET['course'];?>',subject:'<?php echo $_GET['subject'];?>'}	


	var change1 = 1;
	var change2 = 1;
	var num_rows = 0;
	var num_rows2 = 0;
	setInterval(function(){
		State();
	},500);
	function State(){
		call_ajax({answers_state:true},url,'POST', function(response){
			if(response){
				if(response == "empty"){
					change1 = 1;change2 = 1;
				}
				else if(num_rows == 0 || num_rows2 == 0){
					num_rows = response.answer;
					num_rows2 = response.comment;
					change1 = 1;change2 = 1;
				}	
				else if(num_rows != response.answer || num_rows2 != response.comment){
					num_rows = response.answer;
					num_rows2 = response.comment;
					change1 = 1;change2 = 1;
				}
				update_topic();
				update_answer();
			}
		});
	}
	function update_topic(){
		//console.log(form);
		if(change1 == 1){
			call_ajax(topic_form,url,'POST',function(response){
				$('#main-content').html(response);
				$('#main-content').removeClass('loader');
				change1 = 0;
			});
		}
	}
	function update_answer(){
		call_ajax(answer_form,url,'POST',function(response){
			if(change2 == 1){
				//console.clear();
				$('.dyn_answer').html(response);
				$('.dyn_answer').removeClass('loader');
				change2 = 0;
			}
		});
	}
	</script>
	<?php elseif(IsSet($_GET['action']) && $_GET['action'] == 'Admin'):?>
	<script type="text/javascript">
		admin('members');
	</script>
	<?php endif;?>
	<div id="response" class='hide'>
		<div id="response-box">
			<div class="response-content">
				<span id="response-msg"></span>
			</div>
		</div>
	</div>
</body>
</html>