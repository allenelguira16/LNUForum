<?php
	class db{
		private $server;
		private $user;
		private $pass;
		private $db;
		protected $con;
		function __construct(){
			$this->server = 'localhost';
			$this->user = 'root';
			$this->pass = '';
			$this->db = 'LNUForum';
			$this->con = new mysqli($this->server,$this->user,$this->pass,$this->db) or die($this->con->error);
		}
		function close_con(){
			if(IsSet($this->con)){
				mysqli_close($this->con);
				unset($this->con);
			}
		}
		function query($sql){
			$res = mysqli_query($this->con,$sql);
			if(!$res){
				die("Database Query Error: ".$this->con->error."# ".$this->con->errno).__LINE__;
			}
			return $res;
		}
		function strip($content){
			//$res = htmlspecialchars($content,ENT_QUOTES,"UTF-8");
			$res = mysqli_real_escape_string($this->con,$content);
			//$res = stripslashes($content);
			return $res;
		}
		function ID($id){
			return $id;
		}
		function trim_str($content){
			return trim($content);
		}
		function hasSpecial($content){
			if(preg_match('/[\'^£$%&*()}{@#~><>,|=_+¬-]/', $content)){
				return true;
			}
			else{
				return false;
			}
		}
		function number_only($str){
			if(preg_match('/[0-9]/', $str)){
				return true;
			}
			else{
				return false;
			}
		}
	}
	class user extends db{
		function getState(){
			$sql = "SELECT * FROM Chat";
			$res = $this->query($sql);
			return json_encode((($res->num_rows != 0) ? $res->num_rows : 'empty'));
		}
		function getAnswerState(){
			/*$sql = "SELECT * FROM answer";
			$res = $this->query($sql);
			$response['answer'] = (($res->num_rows != 0) ? $res->num_rows : 'empty');
			$sql = "SELECT * FROM comments";
			$res = $this->query($sql);
			$response['comment'] = (($res->num_rows != 0) ? $res->num_rows : 'empty');*/
			$sql = "SELECT * FROM notif";
			$res = $this->query($sql);
			$response['answer'] = (($res->num_rows != 0) ? $res->num_rows : 'empty');
			return json_encode($response);
		}
		function username($user){
			$response = '';
			if(strlen($user) >= 6){
				$sql = "SELECT * FROM user WHERE username = '{$this->strip($user)}'";
				$res = $this->query($sql);
				if($res->num_rows == 0){
					$response = true; 
				}
				else{
					$response = false;
				}
			}
			return json_encode($response);
		}
		function secure($pass){
			return password_hash($pass,PASSWORD_DEFAULT);
		}
		function decrypt($pass,$dbpass){
			return password_verify($pass,$dbpass);
		}
		function resent(){
			$sql = "SELECT * FROM user WHERE user_id = '{$_SESSION['user_id']}'";
			$res = $this->query($sql);
			if($res->num_rows == 1){
				$data = $res->fetch_array();
				$response = $this->update_verification($data['email']);
			}
			else{
				$response = false;
			}
			return json_encode($response);
		}
		function update_verification($email){
			$hash = mt_rand(100000,999999);
			$headers =  'MIME-Version: 1.0' . "\r\n"; 
			$headers .= 'From: LNUForum <info@address.com>' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$msg = "
				<div>
					<header>LNUFourm Verification</header>
					<span>Verification Code: $hash</span>
				</div>
			";
			if(mail($email,"Verification Code", $msg, $headers)){
				$sql = "UPDATE verification SET hash = '$hash' WHERE user_id = {$_SESSION['user_id']}";
				if($this->query($sql)){
					return true;
				}
				else{
					return false;
				}
			}else{
				return false;
			}
		}
		function verification($user_id,$email){
			$hash = mt_rand(100000,999999);
			$headers =  'MIME-Version: 1.0' . "\r\n"; 
			$headers .= 'From: LNUForum <info@address.com>' . "\r\n";
			$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
			$msg = "
				<div>
					<header>LNUFourm Verification</header>
					<span>Verification Code: $hash</span>
				</div>
			";
			if(mail($email,"Verification Code", $msg, $headers)){
				$sql = "INSERT INTO verification(user_id,hash) VALUES ('$user_id','$hash')";
				if($this->query($sql)){
					return true;
				}else{
					return false;
				}
			}else{
				return false;
			}
		}
		function verify($code){
			$response = '';
			if($this->number_only($code)){
				$sql = "SELECT * FROM verification WHERE user_id = '{$_SESSION['user_id']}' AND hash = '$code'";
				$res = $this->query($sql);
				if($res->num_rows == 1){
					$sql = "UPDATE user SET verified = 1 WHERE user_id = '{$_SESSION['user_id']}'";
					if($this->query($sql)){
						$sql = "DELETE FROM verification WHERE user_id = '{$_SESSION['user_id']}'";
						if($this->query($sql)){
							$response = true;
						}else{
							$response = false;
						}
					}else{
						$response = false;
					}
				}else{
					$response = false;
				}
			}else{
				$response = false;
			}
			return json_encode($response);
		}
		function register($uname,$fname,$lname,$pass,$course,$year,$email){
			$sql = "SELECT * FROM user WHERE username = '$uname'";
			$res = $this->query($sql);
			if($res->num_rows == 0){
				$sql = "INSERT INTO 
							user(username,fname,lname,pass,course,year,email) 
						VALUES 
							('$uname','$fname','$lname','{$this->secure($pass)}','$course','$year','$email')";
				$this->query($sql);
				if($this->con->affected_rows){
					$response = $this->verification($this->con->insert_id,$email);
				}
				else{
					$response = false;
				}
			}
			else{
				$response = false;
			}
			return json_encode($response);
		}
		function user_login($user,$pass){
			$response = '';
			if(!empty($user) && !empty($pass)){
				$sql = "SELECT user_id,pass,user_type,email FROM user WHERE username = '{$this->strip($user)}'";
				$res = $this->query($sql);
				if($res->num_rows == 1){
					list($id,$dbpass,$type,$email) = $res->fetch_array();
					if($this->decrypt($pass,$dbpass)){
						if($type == 'admin'){
							$_SESSION['admin'] = true;
						}
						$_SESSION['user_id'] = $id;
						$response = true;
					}
					else{
						$response = 'Password incorrect';
					}
				}else{
					$response = "Username is incorrect!";
				}
			}
			else{
				$response = "Input boxes are empty!";
			}
			return json_encode($response);
		}
		function answer($content,$topic_id,$user_id){
			$sql = "SELECT topic_title FROM topic WHERE id = '$topic_id'";
			$res = $this->query($sql);
			list($title) = $res->fetch_array();
			$sql = "INSERT INTO answer(topic_id,user_id,answer_content) VALUES ('$topic_id','$user_id','{$this->strip($this->trim_str($content))}')";
			$this->query($sql);
			if($this->con->affected_rows){
				//$response = 
				$this->update_notif($user_id,'topic',$topic_id,'answer');
				$response = true;
			}
			else{
				$response = 'Error answering this topic';
			}
			return $response;
		}
		function edit_answer($content,$topic_id,$user_id){
			$sql = "UPDATE topic SET topic = '{$this->strip($this->trim_str($content))}' WHERE id = '$topic_id'";
			$res = $this->query($sql);
			if($this->con->affected_rows){
				//$response = 
				$this->update_notif($user_id,'topic',$topic_id,'answer');
				$response = true;
			}
			else{
				$response = 'Error answering this topic';
			}
			return $response;
		}
		function update_notif($user_id,$table,$table_id,$status){
			$sql = "SELECT user_id FROM $table WHERE id = $table_id";
			$res = $this->query($sql);
			list($from_id) = $res->fetch_array();
			$sql = "INSERT INTO notif(user_id,from_id,table_from,table_id,status) VALUES ('$from_id','{$_SESSION['user_id']}','$table','$table_id','$status')";
			$this->query($sql);
			if($this->con->affected_rows){
				$response = true;
			}
			return $response;
		}
		function comment($content,$answer_id,$user_id){
			$html = new html();
			$sql = "INSERT INTO comments(user_id,answer_id,comment) VALUES ('$user_id','$answer_id','{$this->strip($this->trim_str($content))}')";
			$this->query($sql);
			if($this->con->affected_rows){
				$response = $this->update_notif($user_id,'answer',$answer_id,'comment');
			}
			else{
				$response = 'Error answering this topic';
			}
			return json_encode($response);
		}
		function getUid($id){
			$sql = "SELECT user_id FROM user WHERE username = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['user_id'];
		}
		function send_chat($message,$id1,$id2){
			$message = $this->trim_str($message);
			if(!empty($message)){
				$sql = "INSERT INTO chat(message,user_id1,user_id2) VALUES ('{$this->strip($message)}','$id1','{$this->getUid($id2)}')";
				$this->query($sql);
				if($this->con->affected_rows){
					$this->getState($this->con->insert_id);
					$response = true;
				}
			}
			return json_encode($response);
		}
		function delete_chat($id1,$id2){
			$response = '';
			$sql = "DELETE FROM chat WHERE user_id1 IN (SELECT user_id FROM user WHERE user_id = '$id1' OR user_id = '{$this->getUid($id2)}') AND user_id2 IN (SELECT user_id FROM user WHERE user_id = '$id1' OR user_id = '{$this->getUid($id2)}')";
			$this->query($sql);
			if(!$this->con->affected_rows){
				$response = true;
			}
			return json_encode($response);
		}
		function verified(){
			$sql = "SELECT * FROM verification WHERE user_id = '{$_SESSION['user_id']}'";
			$res = $this->query($sql);
			if(!$res->num_rows){
				return true;
			}
			else{
				return false;
			}
		}
		function create_topic($course,$subject,$title,$content,$tags,$user_id){
			//$response = '';
			if(!$this->hasSpecial($title)){
				if(!empty($content) && !empty($title)){
					if($this->verified()){	
						$sql = "INSERT INTO 
									topic(subject_id,course_id,user_id,topic_title,topic,tags)
								VALUES 
									((SELECT id FROM subject WHERE subject = '{$this->strip($subject)}'),(SELECT id FROM course WHERE course = '{$this->strip($course)}'),'$user_id','{$this->strip($this->trim_str($title))}','{$this->strip($this->trim_str($content))}','{$this->strip($this->trim_str($tags))}');
						";
						$this->query($sql);
						if($this->con->affected_rows){
							$sql = "SELECT id,topic_title FROM topic WHERE id = '{$this->con->insert_id}'";
							$res = $this->query($sql);
							$data = $res->fetch_array();
							$link = urlencode($data['topic_title']);
							$response['text'] = "
								<div>Topic has been created!</div>
							";
							$response['head'] = 'Success!';
							$response['token'] = true;
						}
						else{
							$response['text'] = 'Error';
							$response['token'] = false;
						}
					}
					else{
						$response['token'] = false;
						$response['text'] = 'Verify';
					}
				}
				else{
					$response['head'] = 'Warning!';
					$response['token'] = false;
					$response['text'] = 'textbox fields are required';
				}
			}
			else{
				$response['head'] = 'Warning!';
				$response['token'] = false;
				$response['text'] = 'Special Characters are not Allowed in Title Box';
			}
			return json_encode($response);
		}
		function del_topic($tid){
			$response = '';
			$sql = "DELETE FROM topic WHERE id = '$tid'";
			$this->query($sql);
			if($this->con->affected_rows){
				$response = true; 
			}
			return json_encode($response);
		}
		function del_answer($id){
			$response = '';
			$sql = "DELETE FROM answer WHERE id = '$id'";
			$this->query($sql);
			if($this->con->affected_rows){
				$response = true; 
			}
			return json_encode($response);
		}
		function like($ref_id,$ref,$status){
			$response = '';
			$oppose = $status == 'like' ? 'unlike' : 'like';
			$sql = "SELECT * FROM likes WHERE user_id = '{$_SESSION['user_id']}' AND ref_id = $ref_id AND ref = '$ref' AND status = '$oppose'";
			$res = $this->query($sql);
			if($res->num_rows == 0){
				$sql = "SELECT * FROM likes WHERE user_id = '{$_SESSION['user_id']}' AND ref_id = $ref_id AND ref = '$ref' AND status = '$status'";
				$res = $this->query($sql);
				if($res->num_rows == 1){
					$sql = "DELETE FROM likes WHERE  user_id = '{$_SESSION['user_id']}' AND ref_id = $ref_id AND ref = '$ref' AND status = '$status'";
					$this->query($sql);
					$response = true;
				}
				elseif($res->num_rows == 0){
					$sql = "INSERT INTO likes(user_id,ref_id,ref,status) VALUES ('{$_SESSION['user_id']}','$ref_id','$ref','$status')";
					$this->query($sql);
					$response = true;
				}
			}
			if($res->num_rows == 1){
				$sql = "DELETE FROM likes WHERE  user_id = '{$_SESSION['user_id']}' AND ref_id = $ref_id AND ref = '$ref' AND status = '$oppose'";
				$this->query($sql);
				$response = true;
			}
			$this->update_notif($user_id,$ref,$ref_id,$status);
			return json_encode($response);
		}
		function profile_pic($tmp,$file){
			$response = '';
			$ext = strtolower(pathinfo($file,PATHINFO_EXTENSION));
			if(preg_match('/(jpg|png|jpeg|bmp|gif|webp)$/', $ext)){
				$directory = 'profile-pic/'.$_SESSION['user_id'].'/';
				if(!file_exists($directory)){
					mkdir($directory, 0777, true);
				}
				$file = $directory.md5(mt_rand(100000,999999)).".".$ext;
				if(move_uploaded_file($tmp, $file)){
					$sql = "UPDATE profile SET type = 'previous' WHERE user_id = '{$_SESSION['user_id']}' AND type = 'primary'";
					$this->query($sql);
					//if($this->con->affected_rows){
						$sql = "INSERT INTO profile(user_id,filename,type) VALUES ('{$_SESSION['user_id']}','$file','primary')";
						$this->query($sql);
					//}
				}
			}
			return json_encode($response);
		}
		function add_course($course,$desc){
			$response = '';
			if(!empty($course) && !empty($desc)){
				$sql = "INSERT INTO course(course,description) VALUES ('{$this->strip($course)}','{$this->strip($desc)}')";
				if($this->query($sql)){
					$response = true;
				}
			}
			return json_encode($response);
		}
		function add_subject($cid,$subject,$desc){
			$response = '';
			if(!empty($subject) && !empty($desc)){
				$sql = "INSERT INTO subject(course_id,subject,description) VALUES ('$cid','{$this->strip($subject)}','{$this->strip($desc)}')";
				if($this->query($sql)){
					$response = true;
				}
			}
			return json_encode($response);
		}
		function update_user($val,$row){
			$response = array();
			$exec = true;
			if($row == 'username'){
				$sql = "SELECT * FROM user WHERE username = '$val'";
				$res = $this->query($sql);
				if($res->num_rows == 1){
					//return "Username Already Exists";
					$response['header'] = "Warning";
					$response['msg'] = "Username Already Exists";
					$exec = false;
				}
			}
			if($exec == true){
				$sql = "UPDATE user SET $row = '{$this->strip($val)}' WHERE user_id = '{$_SESSION['user_id']}'";
				if($this->query($sql)){
					return true;
				}
			}
			return json_encode($response);
		}
		function notif(){
			$response = '';
			$sql = "SELECT * FROM notif WHERE user_id = '{$_SESSION['user_id']}'";
			return json_encode($response);
		}
	}
	class html extends db{
		function getUid($id){
			$sql = "SELECT user_id FROM user WHERE username = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['user_id'];
		}
		function secure($pass){
			return password_hash($pass,PASSWORD_DEFAULT);
		}
		function decrypt($pass){
			return password_verify($pass,$this->secure($pass));
		}
		function login(){
			$response = "
				<div class='box hide' id='mini-user-config'>
					<section>
						<form action='http://localhost/LNUForum/includes/php/ajax/data-handler.php' method='POST' class='login'>
							<div>
								<label>Username</label>
								<input type='textbox' name='user'>
								<label>Password</label>
								<input type='password' name='pass'>
								<input type='submit' name='login-btn' value='login'>
								<input type='hidden' name='login' value='login'>
								<span style='font-size: 16px;'><a href='/LNUForum/Reset' class='a12'>Reset Password</a></span>
								<span style='font-size: 16px;'><a href='/LNUForum/Register' class='a12'>Sign up</a></span>
							</div>
						</form>
					</section>
				</div>
			";
			return $response;
		}
		function username($uid){
			$sql = "SELECT username FROM user WHERE user_id = '$uid'";
			$res = $this->query($sql);
			list($name) = $res->fetch_array();
			return $name;
		}
		function users_data($uid){
			$response = '';
			$sql = "SELECT user_id, fname, lname FROM user WHERE user_id != '$uid'";
			$res = $this->query($sql);
			while(list($user_id,$fname,$lname) = $res->fetch_array()){
				$response .= "
					<div class='user'>
						<span style='cursor: pointer;' class='user-open'>{$fname} {$lname}</span>
						<div class='user-menu hide'>
							<a href=Profile/{$this->username($user_id)}>View Profile</a>
							<a href=Message/{$this->username($user_id)}>Send Private Chat</a>
						</div>
					</div>
				";
			}
			return $response;
		}
		function main_login(){
			$response = "
				<div class='box'>
					<header class='main-header'>
						<span>Login</span>
					</header>
					<section>
						<form action='http://localhost/LNUForum/includes/php/ajax/ajax/data-handler.php' method='POST' class='main-login login'>
							<div>
								<label>Username</label>
								<input type='textbox' name='user'>
								<label>Password</label>
								<input type='password' name='pass'>
								<input type='submit' name='login-btn' value='login'>
								<input type='hidden' name='login' value='login'>
								<span>New here? <a href='/LNUForum/?action=register'>Sign up now!</a></span>
							</div>
						</form>
					</section>
				</div>
			";
			return $response;
		}
		function register(){
			$response = "
				<div class='box'>
					<section>
						<form action='http://localhost/LNUForum/includes/php/ajax/data-handler.php' method='POST' id='register'>
							<div>
								<div>
									<label>First Name</label>
									<input type='textbox' name='fname'>
									<span class='input-status'></span>
								</div>
								<div>
									<label>Last Name</label>
									<input type='textbox' name='lname'>
									<span class='input-status'></span>
								</div>
							</div>
							<div>
								<label>Username</label>
								<input type='textbox' name='uname'>
								<span class='input-status'></span>
							</div>
							<div>
								<div>
									<label>Password</label>
									<input type='password' name='pass1'>
									<span class='input-status'></span>
								</div>
								<div>
									<label>Re-enter Password</label>
									<input type='password' name='pass2'>
									<span class='input-status'></span>
								</div>
							</div>
							<div>
								<label>Email</label>
								<input type='email' name='email'>
								<span class='input-status'></span>
							</div>
							<div>
								<div>
									<label>Course</label>
									<select name='course'>
										{$this->selectCourse()}
									</select>
								</div>
								<div>
									<label>Year</label>
									<select name='yearlvl'>
										<option value='1'>1</option>
										<option value='2'>2</option>
										<option value='3'>3</option>
										<option value='4'>4</option>
										<option value='5'>5</option>
										<option value='6'>6</option>
										<option value='7'>7</option>
										<option value='8'>8</option>
									</select>
								</div>
							</div>
							<div>
								<div>
									<a href='/LNUForum/Login' style='color: blue;'>Sign in instead</a>
								</div>
								<div class='clearfix'>
									<input type='submit' name='login-btn' value='Register' class='clearfix'>
									<input type='hidden' name='register' value='true'>
								</div>
							</div>
						</form>
					</section>
				</div>
			";
			return $response;
		}
		function selectCourse(){
			$sql = "SELECT * FROM course";
			$res = $this->query($sql);
			$response = '';
			while(list($id,$course) = $res->fetch_array()){
				$response .= "<option value='$course'>$course</option>";
			}
			return $response;
		}
		function user($user_id){
			$sql = "SELECT fname,lname FROM user WHERE user_id = '$user_id'";
			$res = $this->query($sql);	
			list($fname,$lname) = $res->fetch_array();
			$trash = $this->users_data($user_id);
			$response = "
				<div class='box hide' id='mini-user-config'>
					<section>
						<div id='user-details'>
							<div id='user-profile' class='clearfix'>
								{$this->mini_profile($user_id)}
							</div>
							<div style='margin-left:2px; vertical-align: middle;line-height: 3;'>
								<span>{$fname} {$lname}</span>
							</div>
						</div>
						<div>
							<a href=/LNUForum/Profile/{$this->username($user_id)}>Profile</a>
							<a href=/LNUForum/Settings/>Settings</a>
							<a href='#' class='logout' data-id={$user_id}>Logout</a>
						</div>
					</section>
				</div>
			";
			return $response;
		}
		function courses(){
			$response = '';
			$sql = "SELECT * FROM course";
			$res = $this->query($sql);
			list($id,$course,$desc) = $res->fetch_array();
			//while(list($id,$course,$desc) = $res->fetch_array()){
				$response .= "
					<div class='box whitebg'>
						<header class='main-box-header redbg'>
							<div>
								<span>Courses</span>
							</div>
						</header>
						<section>
							{$this->show_courses($course)}
						</section>
					</div>
				";
			//}
			
			return $response;
		}
		function show_courses($course){
			$response = '';
			$sql = "SELECT * FROM course";
			$res = $this->query($sql);
			while(list($id,$course,$desc) = $res->fetch_array()){
				$response .= "
					<div class='course-div clearfix'>
						<div class='course-content'>
							<div class='course-title'>
								<a href=/LNUForum/{$course}/>
									<span>{$course}</span>
								</a>
							</div>
							<div class='course-desc'>
								<span>{$desc}</span>
							</div>
						</div>
					</div>
				";
			}
			return $response;
		}
		function show_subjects($id,$course){
			$response = '';
			$sql = "SELECT * FROM Subject WHERE course_id = '$id'";
			$res = $this->query($sql);
			if(!empty($res->num_rows)){
				while(list($sid,$cid,$subject,$desc) = $res->fetch_array()){
					$response .= "
						<div class='course-div clearfix'>
							<div class='course-content'>
								<div class='course-title'>
									<a href=/LNUForum/{$course}/{$subject}/1/>
										<span>{$subject}</span>
									</a>
								</div>
								<div class='course-desc'>
									<span>{$desc}</span>
								</div>
							</div>
							<div class='course-details'>
								<span>{$this->total_topics($id)}</span>
								<span>{$this->total_answer($id)}</span>
							</div>
						</div>
					";
				}
			}
			else{
				$response = "
					<div class='course-div clearfix'>
						<div class='course-content'>
							<div class='course-title'>
								<span>No Subjects available</span>
							</div>
						</div>
					</div>
				";
			}
			return $response;
		}
		function total_topics($id){
			$sql = "SELECT count(*) as Total FROM topic WHERE id = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['Total'];
		}
		function total_answer($id){
			$sql = "SELECT count(*) as Total FROM answer WHERE topic_id IN (SELECT id FROM topic WHERE id = '$id')";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['Total'];
		}
		function course_only($course){
			$response = '';
			$sql = "SELECT * FROM course WHERE course = '{$this->strip($course)}'";
			$res = $this->query($sql);
			while(list($id,$course,$desc) = $res->fetch_array()){
				$response .= "
					<div class='box whitebg'>
						<header class='main-box-header redbg'>
							<div>
								<a href='/LNUForum/{$course}/'>{$course}</a>
							</div>
							<div class='course-detail'>
								<span>Subjects</span>
								<span>Topics</span>
							</div>
						</header>
						<section>
							{$this->show_subjects($id,$course)}
						</section>
					</div>
				";
			}
			
			return $response;
		}
		
		function getSid($subject){
			$sql = "SELECT id FROM subject WHERE subject = '$subject'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['id'];
		}
		function getCid($course){
			$sql = "SELECT id FROM course WHERE course = '$course'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['id'];
		}
		function bbcodeslist(){
			$response = '';
			$images = glob("emoji/emojiv2/*.*");
			for($i = 0; $i < count($images); $i++){
				$new_image = basename($images[$i]);
				$response .= "<img alt='emojis' src='/LNUForum/emoji/$new_image' class='emoji'>";
			}
			return json_encode($response);
		}
		function bbcodeparse($content){
			//$content = htmlspecialchars($content); 
			//$content = addslashes($content);
			//$content;
			$search = array(
				"/\[b\](.*?)\[\/b\]/is",
				"/\[i\](.*?)\[\/i\]/is",
				"/\[u\](.*?)\[\/u\]/is",
				"/\[img\](.*?)\[\/img\]/is",
				"/\[url=(.*?)\](.*?)\[\/url\]/is",
				"/\[url=http\:\/\/(.*?)\](.*?)\[\/url\]/is",
				"/\[url=https\:\/\/(.*?)\](.*?)\[\/url\]/is",
				"/\[font color=(.*?) size=(.*?) face=(.*?)\](.*?)\[\/font\]/is",
				"/\>\:\(/",
				"/\>\:o/",
				"/o\:\)/",
				"/\:\)/",
				"/\:D/",
				"/\:\(/",
				"/\:\'\(/",
				"/\:P/",
				"/o\.O/",
				"/\;\)/",
				"/:o/",
				"/\-\_\-/",
				"/\:\*/",
				"/\^\_\^/",
				"/8\-\)/",
				"/8\|/",
				"/\:\|/",
				"/\:3/",
				"/\<3/"
			);
			$replace = array(
				'<b>$1</b>',
				'<i>$1</i>',
				'<u>$1</u>',
				'<img src=$1 style="height: 20px;" alt="GG">',
				'<a href=http://$1 target=_blank style="text-decoration:underline; color: blue;">$2</a>',
				'<a href=http://$1 target=_blank style="text-decoration:underline; color: blue;">$2</a>',
				'<a href=https://$1 target=_blank style="text-decoration:underline; color: blue;">$2</a>',
				'<span style="color:$1; font-size:$2; font-family:$3; ">$4</span>',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Very_Mad_Emoji_Icon_ios10.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Super_Angry_Face_Emoji_ios10.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Angel.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Slightly_Smiling_Emoji_Icon.png"class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Smiling.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Unhappy_Face_Emoji_Icon_ios10.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Dissapointed_but_recovered.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Tongue_Out_Emoji_1.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Surprised_Emoji_Icon.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Smirk.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Surprised_Emoji_Icon.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Expressionless_Emoji_Icon.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Kiss_Emoji_Icon_2.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Shy_Emoji_Icon.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Nerd_Emoji_Icon.png" class="emoji"">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/SunGlass.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Confused.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Confounded.png" class="emoji">',
				'<img alt="emoji" src="http://localhost/LNUForum/emoji/Heart_Eyes_Emoji_2.png" class="emoji">'
			);	
			//$response['text'] = preg_replace($search, $replace, $content) or die(mysqli_error($this->con));
			//if(preg_match($search, $content)){
			//	$response['text'] = preg_replace($search, $replace, $content) or die(mysqli_error($this->con));
			//	$response['token'] = true;
			//}fde
			//else{
				$response['text'] = $content;
				$response['token'] = false;
			//}
			return json_encode($response);
		}
		function nav($subject,$course,$cpage){
			$response = '';
			$sql = "SELECT * FROM topic WHERE subject_id IN (SELECT id FROM subject WHERE subject = '$subject')";
			$res = $this->query($sql);
			$page = $res->num_rows / 10;
			$page = (is_float($page) ? intval($page + 1) : $page);
			$page = ($page == 0 ? 1 : $page);
			$back = (($cpage != 1) ? "<a href='/LNUForum/{$course}/{$subject}/".($cpage - 1)."'><</a>" : "");
			$next = (($cpage != $page) ? "<a href='/LNUForum/{$course}/{$subject}/".($cpage + 1)."'>></a>" : "");
			$paging = 
			(
				(!IsSet($_GET['id'])) ? 
					"<div style='float: right;'>
						<span>{$back} Page {$cpage} of {$page} {$next}</span>
					</div>": 
					""
			);
			$link = IsSet($_SESSION['user_id']) ? "/LNUForum/{$course}/{$subject}/Create" : "/LNUForum/login";
			$response = "
				<nav class='clearfix' id='subject-nav'>
					<div class='link-btn'>
						<a href='$link'><span>Add new Topic</span></a>
					</div>
					{$paging}
				</nav>
			";
			return $response;
		}
		function subject_only($subject,$course,$page){
			$response = '';
			$sql = "SELECT * FROM subject WHERE subject = '{$this->strip($subject)}'";
			$res = $this->query($sql);
			$response = $this->nav($subject,$course,$page);
			while(list($id,$cid,$subject,$desc) = $res->fetch_array()){
				$response .= "
					<div class='box whitebg'>
						<header class='main-box-header redbg'>
							<div>
								<span'>{$subject}</span>
							</div>
							<div class='details'>
								<div><span>votes</span></div>
								<div><span>answers</span></div>
								<div><span>views</span></div>
							</div>
						</header>
						<section>
							{$this->show_topics($id,$subject,$course,$page)}
						</section>
					</div>
				";
			}
			return $response;
		}
		function show_topics($id,$subject,$course,$page){
			$response = '';
			$limit = 10;
			$offset = (($page == 1) ? 0 : ($page * 10) - $limit);
			$sql = "SELECT id,subject_id,course_id,user_id,topic_title,topic,date,total_likes,views FROM topic WHERE subject_id = '$id' ORDER BY views DESC LIMIT {$offset}, {$limit}";
			$res = $this->query($sql);
			if(!empty($res->num_rows)){
				while(list($tid,$sid,$cid,$uid,$title,$topic,$date,$tlike,$views) = $res->fetch_array()){
					$link = urlencode("{$title}");
					$topic = str_replace(array("\r","\n"),"",$topic);
					$response .= "
						<div class='course-div clearfix'>
							<div class='course-content'>
								<div class='course-title'>
									<a href=/LNUForum/{$course}/{$subject}/Topic/{$tid}/$link>
										<span>{$title}</span>
									</a>
								</div>
								<div class='course-desc'>
									<span>{$topic}</span>
								</div>
							</div>
							{$this->topic_details($tid)}
						</div>
					";
				}
			}
			else{
				$response = "
					<div class='course-div clearfix'>
						<div class='course-content'>
							<div class='course-title'>
								<span>No Subjects available</span>
							</div>
						</div>
					</div>
				";
			}
			return $response;
		}
		function search_topics($subject,$course,$search){
			$response = '';;
			if(!empty($search)){
				$sql = "SELECT id,subject_id,course_id,user_id,topic_title,topic,date,total_likes,views FROM topic WHERE topic_title LIKE '%$search%' ORDER BY date DESC";
				$res = $this->query($sql);
				if(!empty($res->num_rows)){
					while(list($tid,$sid,$cid,$uid,$title,$topic,$date,$tlike,$views) = $res->fetch_array()){
						$response .= "
							<div class='course-div clearfix'>
								<div class='course-content'>
									<div class='course-title'>
										<a href=/LNUForum/{$course}/{$subject}/Topic-{$tid}/>
											<span>{$this->code($title)}</span>
										</a>
									</div>
									<div class='course-desc'>
										<span>{$this->code($topic)}</span>
									</div>
								</div>
								{$this->topic_details($tid)}
							</div>
						";
					}
				}
				else{
					$response = "
						<div class='course-div clearfix'>
							<div class='course-content'>
								<div class='course-title'>
									<span>No Results</span>
								</div>
							</div>
						</div>
					";
				}
			}
			else{
				$response = $old;
			}
			return json_encode($response);
		}
		function topic_details($tid){
			$response = '';
			$sql = "SELECT total_likes FROM topic WHERE id = '$tid'";
			$res = $this->query($sql);
			list($tlikes) = $res->fetch_array();
			$sql = "SELECT COUNT(*) FROM answer WHERE topic_id = '$tid'";
			$res = $this->query($sql);
			list($tanswers) = $res->fetch_array();
			$sql = "SELECT views FROM topic WHERE id = '$tid'";
			$res = $this->query($sql);
			list($tviews) = $res->fetch_array();
			$response = "
				<div class='details'>
					<div><span>{$tlikes}</span></div>
					<div><span>{$tanswers}</span></div>
					<div><span>{$tviews}</span></div>
				</div>
			";
			return $response;
		}
		function sys_date($date){
			return date("d M Y h:i A", strtotime($date));
		}
		function topic_nav($id){
			return "
				<nav class='topic_nav'>
					<div>
						<a href='Topic-{$id}/reply' type='buttton'>
							<span>Post a Reply</span>
						</a>
					</div>
					<div>
						<form>
							<input type='search' name='search' placeholder='search'>
							<input type='submit' name='search-tru-btn'>
						</form>
					</div>
				</nav>
			";
		}
		function create_topic($course,$subject,$uid){
			$response = "";
			$response = "
				<div class='box'>
					<section>
						<form action='http://localhost/LNUForum/includes/php/ajax/ajax/data-handler.php' method='POST' id='create-form'>
							<div>
								<label>Title:</label>
								<input type='textbox' name='title'>
							</div>
							<div>
								<label>Content:</label>
								<div id='content-context'>
									<div contenteditable='true' id='content' class='content-box' placeholder='Content of Topic'></div>
									<div class='emoji-platform'>
										<img alt='default-emoji' src='http://localhost/LNUForum/includes/php/emoji/default.png' class='btn default-emoji' data-ref='emoji'>
										<div class='emojilist hide loader' style='height: 20px;'>
											<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
										</div>
									</div>
								</div>
							<div class='clearfix'>
								<label>Tags:</label>
								<input type='textbox' placeholder='e.g. C++,PHP,Javascript' name='tags'>
								<input type='submit' name='create-btn' value='Post'>
								<input type='hidden' name='create' value='create'>
								<input type='hidden' name='course' value='{$_GET['course']}'>
								<input type='hidden' name='subject' value='{$_GET['subject']}'>
							</div>
						</form>
					</section>
				</div>
			";
			return $response;
		}
		function answer_form($tid){
			if(IsSet($_SESSION['user_id'])){
				$response =
				"		
					<form action='http://localhost/LNUForum/includes/php/ajax/data-handler.php' id='answer_js' method='POST'>
						<div id='answer-context'>
							<div contenteditable='true' id='answer-content' class='content-box' placeholder='Answer this Question'></div>
							<div class='emoji-platform'>
								<img alt='default-emoji' src='http://localhost/LNUForum/includes/php/emoji/default.png' class='btn default-emoji' data-ref='emoji'>
								<div class='emojilist hide loader' style='height: 20px;'>
									<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
								</div>
							</div>
						</div>
						<input type='hidden' name='topic_id' value='{$tid}' class='uid'>
						<input type='hidden' name='answer' value='true'>
						<input type='submit' value='Answer' class='input-box small_box pointer'>
					</form>
				";
			}
			else{
				/*$response = "
					<div id=answer-context>
						<a href='/LNUForum/?action=login' class='login-form'>Login to answer</a>
					</div>
				";*/
				$response =
				"		
					<form action='http://localhost/LNUForum/includes/php/ajax/data-handler.php' id='answer_js' method='POST' style='pointer-events: none;opacity: 0.5;'>
						<div id='answer-context'>
							<div contenteditable='true' id='answer-content' class='content-box' placeholder='Answer this Question'></div>
							<div class='emoji-platform'>
								<img alt='default-emoji' src='http://localhost/LNUForum/includes/php/emoji/default.png' class='btn default-emoji' data-ref='emoji'>
								<div class='emojilist hide loader' style='height: 20px;'>
									<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
								</div>
							</div>
						</div>
						<input type='hidden' name='topic_id' value='{$tid}' class='uid'>
						<input type='hidden' name='answer' value='true'>
						<input type='submit' value='Answer' class='input-box small_box pointer'>
					</form>
				";
			}
			return $response;
		}
		function comment_form($id){
			if(IsSet($_SESSION['user_id'])){
				$response =
				"
					<form action='http://localhost/LNUForum/includes/php/ajax/data-handler.php' class='comment_js clearfix hide' method='POST'>
						<div class='comment-context'>
							<div contenteditable='true' class='comment-content content-box' placeholder='Reply to this Answer'></div>
							<div class='emoji-platform'>
								<img alt='default-emoji' src='http://localhost/LNUForum/includes/php/emoji/default.png' class='btn default-emoji' data-ref='emoji'>
								<div class='emojilist hide loader' style='height: 20px;'>
									<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
								</div>
							</div>
						</div>
						<input type='hidden' name='answer_id' value=$id class='uid'>
						<input type='hidden' name='comment' value='true'>
						<input type='submit' value='Comment' class='input-box small_box pointer'>
					</form>
				";
			}
			else{
				/*$response = "
					<div class='comment-context'>
						<a href='/LNUForum/?action=login' class='login-form'>Login to answer</a>
					</div>
				";*/
				$response =
				"
					<form action='http://localhost/LNUForum/includes/php/ajax/data-handler.php' class='comment_js clearfix hide' method='POST' style='pointer-events: none;opacity:0.5;'>
						<div class='comment-context'>
							<div contenteditable='true' class='comment-content content-box' placeholder='Reply to this Answer'></div>
							<div class='emoji-platform'>
								<img alt='default-emoji' src='http://localhost/LNUForum/includes/php/emoji/default.png' class='btn default-emoji' data-ref='emoji'>
								<div class='emojilist hide loader' style='height: 20px;'>
									<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
								</div>
							</div>
						</div>
						<input type='hidden' name='answer_id' value=$id class='uid'>
						<input type='hidden' name='comment' value='true'>
						<input type='submit' value='Comment' class='input-box small_box pointer'>
					</form>
				";
			}
			return $response;
		}
		function add_views($id){
			$sql = "UPDATE topic SET views = views + 1 WHERE id = '$id'";
			$this->query($sql);
		}
		function topic_owner($uid,$id,$date,$ref){
			$sql = "SELECT CONCAT(fname,' ',lname) as fullname,username,course,user_type FROM user WHERE user_id = '$uid'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			if(IsSet($_SESSION['user_id']) && $uid == $_SESSION['user_id']){
				$ref2 = $ref == 'topic' ? $ref : 'answer';
				$owner = '
					<div data-id="'.$id.'" class="content-menu">
						<span id="topic_delete" data-ref="show" class="btn">≡</span>
						<div class="hide menu">
							<span class="btn" data-get="'.$id.'" data-ref="edit-'.$ref2.'" role=button>Edit</span>
							<span class="btn" data-get="'.$id.'" data-ref="del-'.$ref2.'" role=button>Delete</span>
						</div>
					</div>
				';
			}
			else{
				$owner = '';
			}
			$reply = (($ref == 'answer') ? "<a href='#' class='btn' data-ref='open-content'><img src='/LNUForum/Icons/reply.png'>Reply</a>" : "");
			$usertype = (($data['user_type'] == 'admin') ? " - {$data['user_type']}" :" - {$data['user_type']} at {$data['course']}");
			$response = "
				{$owner}
				<div class='topic-owner'>
					<h3>
						<a href='/LNUForum/Profile/{$data['username']}' style='color: #a10234;'>{$data['fullname']}</a>
						<span style='font-size: 14px;'>$usertype</span>
					</h3>
					<div>
						<span class='time-ago'>{$date}</span>
						$reply
					</div>
				</div>
			";
			return $response;
		}
		function topic_layout(){
			return "
				<div id='topic' class='box'>
					<div id='main-content' class='clearfix'>
					</div>
					<div class='dyn_answer loader clearfix'>
						<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
					</div>
				</div>
			";
		}
		function topic($subject,$course,$id){
			$this->add_views($id);
			$sql = "SELECT id,subject_id,course_id,user_id,topic_title,topic,date,total_likes,views FROM topic WHERE id = '{$this->strip($id)}'";
			//$res = mysqli_query($this->con,$sql);
			$res = $this->query($sql);
			list($tid,$sid,$cid,$uid,$title,$topic,$date,$tlike,$views) = $res->fetch_array();
			$response = '';
			if(!empty($res->num_rows)){
				$topic = nl2br($topic);
				$owner = '';
				$ago = $date;
				//$ago = $this->Ago($date);
				$name = $this->creator($uid);
				$response = "
					<div class='user-content clearfix'>
						{$this->likes($tid,'topic')}
						{$this->topic_owner($uid,$tid,$this->ago($date),'topic')}
						<div class='content-topic'>{$this->code($topic)}</div>
					</div>
					<div id='answer_form' class='clearfix'>{$this->answer_form($tid)}</div>
				";
			}
			else{
				$response =
					'<div class="topic_container">
						<div class="topic_content">
							<div class="topic">
								<p>The Topic you are looking is not existing, maybe the Topic was already</p>
								<p>deleted and if you don\'t know what is happening you can report it to the</p>
								<p>
									<a href="report.php">admin</a>
								</p>
							</div>
						</div>
					</div>
				';
			}
			return json_encode($response);
		}
		function total_answers($id){
			$sql = "SELECT COUNT(*) AS total FROM answer WHERE topic_id = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['total'];
		}
		function show_answers($tid){
			$iterate = 0;
			$response = '';
			$sql = "SELECT id,topic_id,user_id,answer_content,date FROM answer WHERE topic_id = '$tid' ORDER BY date DESC";
			$res = mysqli_query($this->con,$sql) or die($this->con->error);
			$response .= "<div class='separator'><h3>Answers</h3></div>";
			if($res->num_rows){
				while(list($id,$tid,$uid,$reply,$date) = $res->fetch_array()){
					$script_id = "script_comment_".$iterate++;
					$response .= "
						<div class='answer clearfix'>
							<div class='answer-content'>
								<div>
									<div class='aside-user'>
									</div>
									<div class='topic-content clearfix'>
										<div class='topic-title'>
											{$this->likes($id,'answer')}
											{$this->topic_owner($uid,$id,$this->ago($date),'answer')}
										</div>
										<div class='topic'>
											<span>{$this->code($reply)}</span>
										</div>
									</div>
								</div>
								<div class='comment-form'>{$this->comment_form($id)}</div>
							</div>
							<div class='sub-content dyn_comment'>
								{$this->show_comments($id)}
							</div>
						</div>
					";
				}
			}
			else{
				$response .= "
						<div class='answer'>
							<div class='answer-content'>
								<div>
									<span>No Answers Yet</span>
								</div>
							</div>
						</div>
					";
			}
			return json_encode($response);
		}
		function show_comments($id){
			$response = '';
			$sql = "SELECT id,user_id,comment,date FROM comments WHERE answer_id = '$id' ORDER BY date DESC";
			$res = $this->query($sql);
			while(list($aid,$uid,$comment,$date) = $res->fetch_array()){
				$response .= "
					<div>
						<div class='topic-content'>
							<div class='topic-title'>
								{$this->topic_owner($uid,$id,$this->ago($date),'del-comment')}
							</div>
							<div class='topic'>
								<div>{$this->code($comment)}</div>
							</div>
						</div>
					</div>
				";
			}
			return $response;
		}
		function creator($uid){
			$sql = "SELECT username,fname,lname FROM user WHERE user_id = '$uid'";
			$res = mysqli_query($this->con,$sql);
			list($uname,$fname,$lname) = $res->fetch_array();
			return "
				<h3>{$fname} {$lname}</h3>
			";
		}
		function like_status($id,$ref,$type){
			if(isset($_SESSION['user_id'])){
				$sql = "SELECT * FROM likes WHERE ref_id = '$id' AND ref = '$ref' AND status = '$type' AND user_id = '{$_SESSION['user_id']}'";
				$res = $this->query($sql);
				if($res->num_rows){
					$data = $res->fetch_array();
					if($type == "like"){
						return "<img src='/LNUForum/Icons/voted.png' class='btn' data-ref='$ref' data-id='$id' data-status='$type'>";
					}
					else{
						return "<img src='/LNUForum/Icons/unvoted.png' class='btn' data-ref='$ref' data-id='$id' data-status='$type'>";
					}
				}
				else{
					if($type == "like"){
						return "<img src='/LNUForum/Icons/vote.png' class='btn' data-ref='$ref' data-id='$id' data-status='$type'>";
					}
					else{
						return "<img src='/LNUForum/Icons/unvote.png' class='btn' data-ref='$ref' data-id='$id' data-status='$type'>";
					}
				}
			}
			else{
				if($type == "like"){
					return "
						<a href='/LNUForum/Login'>
							<img src='/LNUForum/Icons/vote.png'>
						</a>
					";
				}
				else{
					return "
						<a href='/LNUForum/Login'>
							<img src='/LNUForum/Icons/unvote.png'>
						</a>
					";
				}
			}
		}
		function total_likes($id,$ref){
			$sql = "SELECT count(*) AS 'likes' FROM likes WHERE ref_id = '$id' AND ref = '$ref' AND status = 'like'";
			$res = $this->query($sql);
			$data1 = $res->fetch_array();
			$sql = "SELECT count(*) AS 'unlikes' FROM likes WHERE ref_id = '$id' AND ref = '$ref' AND status = 'unlike'";
			$res = $this->query($sql);
			$data2 = $res->fetch_array();
			return $data1['likes'] - $data2['unlikes'];
		}
		function likes($id,$ref){
			$response = "
				<div class='content-like' class='clearfix'>
					<div class='like'>{$this->like_status($id,$ref,"like")}</div>
					<div class='total_likes'>{$this->total_likes($id,$ref)}</div>
					<div class='unlike'>{$this->like_status($id,$ref,"unlike")}</div>
				</div>
			";
			return $response;
		}
		function Ago($date){
			date_default_timezone_set('Asia/Manila');
			$timeago = strtotime($date);
			$current = time();
			$ago = '';
			$seconds = $current - $timeago;
			$minutes = round($seconds / 60);
			$hours = round($seconds / 3600);
			$days = round($seconds / 86400);
			if($seconds <= 60){
				$ago = "Recently Added";
			}
			elseif($minutes <= 60){
				if($minutes == 1){
					$ago = "a minute ago";
				}
				else{
					$ago = $minutes." minutes ago";
				}
			}
			else if($hours <= 24){
				if($hours == 1){
					$ago = "an hour ago";
				}
				else{
					$ago = $hours." hours ago";
				}
			}
			else{
				$ago = date('F j Y', $timeago);
			}
			return $ago;
		}
		function message_box($id2){
			$response = "
				<div class='box'>
					<header class='msg-header'>
						<div>{$id2}</div>
						<div class='chat-menu' style='float: right;'>
							<a href='#' class='btn' data-ref='open-chat-menu'>≡</a>
							<div class='hide menu-block'>
								<a href='#' rel='button' aria-label='click_show' class='link-btn' data-ref='clear-convo' data-id='{$id2}'>
									<span>Clear Conversation</span>
								</a>
							</div>
						</div>
					</header>
					<div class='flex-box'>
						<div style='width: 100%;min-height: 100%;position:relative;'>
							<div id='messages' class='loader'>
								<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
							</div>
							<form rel='async' method='POST' id='chat'>
								<span style='font-size:12px;'>Use enter as Send button<input aria-label='message-type' type='checkbox' class='enter' style='vertical-align: middle;' checked></span>
								<div>
									<div id='chat-context'>
										<div contenteditable='true' id='chat-content' class='content-box' placeholder='Answer this Question'></div>
										<div class='emoji-platform'>
											<img alt='default-emoji' src='http://localhost/LNUForum/includes/php/emoji/default.png' class='btn default-emoji' data-ref='emoji'>
											<div class='emojilist hide loader' style='height: 20px;'>
												<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
											</div>
										</div>
									</div>
									<input type='hidden' value='{$_GET['user']}' name='id'>
									<label for='send_chat' class='send-btn hide'>Send</label>
									<input type='submit' value='Go' id='send_chat' style='display: none;'>
								</div>
							</form>
						</div>
						{$this->users()}
					</div>
				</div>
			";
			return $response;
		}
		function getFirstChat(){
			$sql = "SELECT username FROM user WHERE user_id IN(SELECT user_id1 FROM chat WHERE user_id1 != {$_SESSION['user_id']} ORDER BY date DESC) OR user_id IN(SELECT user_id2 FROM chat WHERE user_id2 != {$_SESSION['user_id']} ORDER BY date DESC)";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['username'];
		}
		function messages($id1,$id2){
			$response = '';
			$sql = "SELECT * FROM user WHERE user_id = '{$this->getUid($id2)}'";
			$res = $this->query($sql);
			if($res->num_rows == 1){
				$sql = "SELECT * FROM chat WHERE user_id1 IN (SELECT user_id FROM user WHERE user_id = '$id1' OR user_id = '{$this->getUid($id2)}') AND user_id2 IN (SELECT user_id FROM user WHERE user_id = '$id1' OR user_id = '{$this->getUid($id2)}')";
				$res = $this->query($sql);
				if(!empty($res->num_rows)){
					while(list($id,$user_id1,$user_id2,$message) = $res->fetch_array()){
						if($user_id1 == $_SESSION['user_id']){
							$response .= "
								<div class='user-chat'>
									<!--div style='display: block;' class='clearfix'>{$this->mini_profile($user_id1)}</div-->
									<div class='chat-msg'>
										<span>{$this->code(nl2br($message))}</span>
									</div>
								</div>
							";
						}
						else{
							$response .= "
								<div class='friend-chat'>
									<div class='clearfix'>
										{$this->mini_profile($user_id1)}</div>
									<div class='chat-msg'>
										<span>{$this->code(nl2br($message))}</span>
									</div>
								</div>
							";
						}
					}
				}
				else{
					$response = "<span style='text-align: center;'>Chat</span>";
				}

			}
			else{
				$response = "
					<span>There are no users named \"$id2\"</span>
				";
			}
			return json_encode($response);
		}
		function active_topics($course){
			$response = '';
			$sql = "SELECT * FROM course WHERE course = '{$this->strip($course)}'";
			$res = $this->query($sql);
			while(list($id,$course,$desc) = $res->fetch_array()){
				$response .= "
					<div class='box whitebg'>
						<header class='main-box-header dark-grey'>
							<div>
								<span>Active Topics</span>
							</div>
							<div class='details'>
								<div><span>votes</span></div>
								<div><span>answers</span></div>
								<div><span>views</span></div>
							</div>
						</header>
						<section>
							{$this->active_topics_data($course)}
						</section>
					</div>
				";
			}
			
			return $response;
		}
		function getSubjectName($subject){
			$sql = "SELECT subject FROM subject WHERE id = '$subject'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['subject'];
		}
		function getTopicName($id){
			$sql = "SELECT topic_title FROM topic WHERE id = $id";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['topic_title'];
		}
		function active_topics_data($course){
			$response = '';
			$sql = "SELECT id,subject_id,course_id,user_id,topic_title,topic,date,total_likes,views FROM topic WHERE course_id = (SELECT course_id FROM course WHERE course = '$course') ORDER BY views DESC LIMIT 3";
			$res = $this->query($sql);
			if(!empty($res->num_rows)){
				while(list($tid,$sid,$cid,$uid,$title,$topic,$date,$tlike,$views) = $res->fetch_array()){
					//$title = urlencode($title);
					$response .= "
						<div class='course-div clearfix'>
							<div class='course-content'>
								<div class='course-title'>
									<a href=/LNUForum/{$course}/{$this->getSubjectName($sid)}/Topic/{$tid}/{$title}>
										<span>{$title}</span>
									</a>
								</div>
								<div class='course-desc'>
									<span>{$topic}</span>
								</div>
							</div>
							{$this->topic_details($tid)}
						</div>
					";
				}
			}
			else{
				$response = "
					<div class='course-div clearfix'>
						<div class='course-content'>
							<div class='course-title'>
								<span>No Subjects available</span>
							</div>
						</div>
					</div>
				";
			}
			return $response;
		}
		function mini_profile($user_id){
			$sql = "SELECT filename FROM profile WHERE user_id = '$user_id' AND type = 'primary'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			if($res->num_rows == 1){
				return "<img alt='err' src='/LNUForum/includes/php/{$data['filename']}' class='mini-profile'>";
			}
			else{
				return "<img alt='err' src='/LNUForum/includes/php/profile-pic/default.jpg' class='mini-profile'>";
			}
		}
		function header_profile($user_id){
			$sql = "SELECT filename FROM profile WHERE user_id = '$user_id' AND type = 'primary'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			if($res->num_rows == 1){
				return "<img alt='err' src='/LNUForum/includes/php/{$data['filename']}' class='header-profile mini-profile btn' data-ref='open-user'>";
			}
			else{
				return "<img alt='err' src='/LNUForum/includes/php/profile-pic/default.jpg' class='header-profile mini-profile btn' data-ref='open-user'>";
			}
		}
		function profile_pic($user_id){
			$sql = "SELECT filename FROM profile WHERE user_id = '$user_id' AND type = 'primary'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			if($res->num_rows == 1){
				return "<img alt='err' src='/LNUForum/includes/php/{$data['filename']}' class='header-profile'>";
			}
			else{
				return "<img alt='err' src='/LNUForum/includes/php/profile-pic/default.jpg' class='header-profile'>";
			}
		}
		function change_profile($id){
			$response = '';
			if(isset($_SESSION['user_id']) && $id == $_SESSION['user_id']){
				$response =  "
					<form method='POST' enctype='multipart/form-data' id='profile-form' class=hide>
						<label for='profile-input'><img  class='camera' src='/LNUForum/Icons/camera.png'></label>
						<input type='file' name='profile-pic' class='hide' id='profile-input'>
						<input type='hidden' name='profile'>
						<input type='submit' class='hide' id='profile-btn'>
					</form>
				";
			}
			return $response;
		}
		function ordinal($number) {
			$ends = array('th','st','nd','rd','th','th','th','th','th','th');
			return ((($number % 100) >= 11) && (($number%100) <= 13)) ? $number. 'th' : $number. $ends[$number % 10];
		}
		function main_profile($username){
			$response = '';
			$sql = "SELECT user_id,username,user_type,fname,lname,course,year,email,bdate,date_created FROM user WHERE username = '$username'";
			$res = $this->query($sql);
			list($user_id,$username,$type,$fname,$lname,$course,$year,$email,$bdate,$date_created) = $res->fetch_array();
			$details = $type != 'admin' ? "<span>{$this->ordinal($year)} Year at {$this->getCourse($course)}</span>" : "<span>Site Admin</span>";
			$response = "
				<div style='background: #FAFAFA;padding: 15px;' class='clearfix'>
					<section class='profile-section'>
						<div id='profile-pic'>
							<div>{$this->change_profile($user_id)}</div>
							<div id='profile'>{$this->profile_pic($user_id)}</div>
						</div>
						
						<h3>$username</h3>
					</section>
					<section class='profile-section'>
						<article>
							<div class='profile-sub-title'>Account Info</div>
							<div class='profile-sub-info'>
								<div>
									<span>{$fname} {$lname}</span>
								</div>
								<div>
									{$details}
								</div>
							</div>
						</article>
						<article>
							<div class='profile-sub-title'>Contact Info</div>
							<div class='profile-sub-info'>	
								<div>
									<span>Email: {$email}</span>
								</div>
							</div>
						</article>
						<div>Topics</div>
						<div class='profile-sub-info'>{$this->users_topic($user_id)}</div>
					</section>
				</div>
			";
			return $response;
		}
		function users_topic($id){
			$response = '';
			$sql = "SELECT id,subject_id,course_id,topic_title,topic,date,total_likes FROM topic WHERE user_id = '$id' ORDER BY id DESC";
			$res = $this->query($sql);
			while(list($id,$sid,$cid,$title,$topic,$date,$likes) = $res->fetch_array()){
				$response .= "
					<div>
						<a href='/LNUForum/{$this->getCourse($cid)}/{$this->getSubjectName($sid)}/topic/{$id}/{$title}'>$title</a>
					</div>
				";
			}
			return $response;
		}
		function footer(){
			$admin = (IsSet($_SESSION['admin']) ? "<p><a href='/LNUForum/admin/'>Admin</a></p>" : '');
			$response = "
				<footer class='clearfix'>
					<div class='footer-contents clearfix'>
						<div class='footer-content'>
							<h3>Who We Are</h3>
							<p>We are What we are and to Be who we Are On What we Are today</p>
						</div>
						<div class='footer-content'>
							<h3>You must Know</h3>
							<p><a href='?action=rules'>Rules</a></p>
							<p><a href='privacy'>Privacy Policy</a></p>
							<p><a href='terms'>Terms and Agreeement</a></p>
							<p><a href='BBCodes'>BBCodes</a></p>
							$admin
						</div>
						<div class='footer-content'>
							<h3>Contact Us</h3>
							<p>Admin e-mail: gg@gmail.com</p>
						</div>
					</div>
					<div class='last-footer'></div>
				</footer>
			";
			return $response;
		}
		function getSubName($subject){
			$sql = "SELECT description FROM subject WHERE subject = '$subject'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['description'];
		}
		function getCourse($id){
			$sql = "SELECT course FROM course WHERE id = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['course'];
		}
		function getCourseName($course){
			$sql = "SELECT description FROM course WHERE course = '$course'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['description'];
		}
		function up(){
			return "
				<div style='position: fixed;right: 20px; bottom: 20px; border: 1px solid #CBCBCB; background: #2C2C2C; color: #FFF;border-radius: 50%; height: 40px; width: 40px; text-align: center; font-size: 30px;' class='up hide'>
					<span>^</span>
				</div>";
		}
		function getUName($id){
			$sql = "SELECT username FROM user WHERE user_id = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['username'];
		}
		function search_engine($search){
			$response = '';
			if(!empty($search)){
				$sql = "
					SELECT user_id,CONCAT(fname,' ',lname),'user' as Keyword FROM user WHERE username LIKE '%{$this->strip($search)}%' 
						OR LOWER(CONCAT(fname,' ',lname)) LIKE LOWER('%{$this->strip($search)}%')
					UNION SELECT id,topic_title,'topic' as Keyword FROM topic WHERE topic_title LIKE '%{$this->strip($search)}%'
				";
				$res = $this->query($sql);
				if(!empty($res->num_rows)){
					while(list($id,$content,$type) = $res->fetch_array()){
						if($type == 'user')
							$response .= "
								<div class='search-content'>
									<span>{$this->mini_profile($id)}</span>
									<span>
										<a href='/LNUForum/Profile/{$this->getUName($id)}'>{$this->highlight($content,$search)}</a>
									</span>
								</div>
							";
						elseif($type == 'topic')
							$response .= "
								<div class='search-content'>
									<span>
										<a href='/LNUForum/{$this->SearchCourse($id)}/{$this->SearchSubject($id)}/Topic/{$id}/{$this->getTitle($id)}'>{$this->code($content)}</a>
									</span>
								</div>
							";
					}
				}
				else{
					$response .= "<span>No Results</span>";
				}
			}
			else{
				$response = 'empty';
			}
			return json_encode($response);
		}
		function SearchCourse($id){
			$sql = "SELECT course FROM course WHERE id IN (SELECT course_id FROM topic WHERE id = '$id')";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['course'];
		}
		function SearchSubject($id){
			$sql = "SELECT subject FROM subject WHERE id IN (SELECT course_id FROM topic WHERE id = '$id')";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['subject'];
		}
		function getTitle($id){
			$sql = "SELECT topic_title FROM topic WHERE id = '$id'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return urlencode($data['topic_title']);
		}
		function highlight($content,$search){
			$response = $content;
			if(strlen($search) > 2){
				$response = preg_replace('/'.preg_quote($search,"/").'/', "<span style='font-weight: bold;'>$0</span>", $response);
			}
			return $response;
		}
		function user_nav(){

		}
		function code($content){
			//$content = htmlspecialchars($content); 
			//$content = addslashes($content);
			//$content;
			$search = array(
				"/\[b\](.*?)\[\/b\]/is",
				"/\[i\](.*?)\[\/i\]/is",
				"/\[u\](.*?)\[\/u\]/is",
				"/\[img\](.*?)\[\/img\]/is",
				"/\[url=(.*?)\](.*?)\[\/url\]/is",
				"/\[url=http\:\/\/(.*?)\](.*?)\[\/url\]/is",
				"/\[url=https\:\/\/(.*?)\](.*?)\[\/url\]/is",
				"/\[font color=(.*?) size=([0-9]+) face=(.*?)\](.*?)\[\/font\]/is",
				"/\[font color=(.*?) size=([0-9]+)([px]+) face=(.*?)\](.*?)\[\/font\]/is"
			);
			$replace = array(
				'<b>$1</b>',
				'<i>$1</i>',
				'<u>$1</u>',
				'<img src=$1 style="height: 20px;" alt="contains image">',
				'<a href=http://$1 target=_blank style="text-decoration:underline; color: blue;">$2</a>',
				'<a href=http://$1 target=_blank style="text-decoration:underline; color: blue;">$2</a>',
				'<a href=https://$1 target=_blank style="text-decoration:underline; color: blue;">$2</a>',
				'<span style="color:$1; font-size:$2px; font-family:$3; ">$4</span>',
				'<span style="color:$1; font-size:$2$3; font-family:$4; ">$5</span>'
			);

			$response = preg_replace($search, $replace, $content);
			return $response;
		}
		function admin_content(){

		}
		function admin(){
			return "
				<nav id='admin-nav'>
					<div>
						<a href='#' data-ref='Custom' class='btn' role='button'>Customization</a>
					</div>
					<div>
						<a href='#' data-ref='Members' class='btn' role='button'>Members</a>
					</div>
					<div>
						<a href='#' data-ref='Courses' class='btn' role='button'>Courses</a>
					</div>
					<div>
						<a href='#' data-ref='Stats' class='btn' role='button'>Statistics</a>
					</div>
				</nav>
				<section id='admin-content' class='loader clearfix'>
					<img height='12' width='12' src='http://localhost/LNUForum/includes/php/icons/loader.svg' alt='Loading Contents'>
				</section>
			";	
		}
		function member_head(){
			return "
				<tr class='header'>
					<th>Username</th>
					<th>Fullname</th>
					<th>Course</th>
					<th>Year level</th>
					<th>Email</th>
				</tr>
			";
		}
		function course_head(){
			return "
				<tr class='header'>
					<th>Course</th>
					<th>Descriptive Title</th>
				</tr>
			";
		}
		function members(){
			$response = '';
			$sql = "SELECT * FROM user WHERE user_type != 'admin'";
			$res = $this->query($sql);
			$response .= "<table>";
			$response .= $this->member_head();
			if($res->num_rows){
				while(list($id,$uname,$type,$fname,$lname,$pass,$course,$year,$email,$bdate,$created) = $res->fetch_array()){
					$response .= "
						<tr>
							<td>$uname </td>
							<td>$fname $lname</td>
							<td>$course </td>
							<td>$year </td>
							<td>$email</td>
						</tr>
					";
				}
			}
			$response .= "</table>";
			return json_encode($response);
		}
		function admin_courses(){
			$response = '';
			$sql = "SELECT * FROM course";
			$res = $this->query($sql);
			$response .= "
				<nav>
					<a href='#' data-ref='add-course' class='btn'>Add Course</a>
					<a href='#' data-ref='add-subject' class='btn'>Add Subject</a>
				</nav>
			";
			$response .= $this->add_form();
			$response .= "<table>";
			$response .= $this->course_head();
			if($res->num_rows){
				while(list($id,$course,$desc) = $res->fetch_array()){
					$response .= "
						<tr>
							<td>$course</td>
							<td>$desc</td>
						</tr>
					";
				}
			}
			$response .= "</table>";
			$response .= $this->admin_subjects();
			return json_encode($response);
		}
		function add_form(){
			return "
				<div class='popup-form hide'>
					<form id='add-course' role='form' class='hide'>
						<h3>Add Course Form<span class='btn' role='button' data-ref='close-form'>X</span></h3>
						<input type='textbox' name='course' placeholder='Course Name'>
						<input type='textbox' name='desc' placeholder='Descriptive Title'>
						<input type='hidden' name='add-course'>
						<input type='submit' value='add course'>
					</form>
					<form id='add-subject' role='form' class='hide'>
						<h3>Add Subject Form<span class='btn' role='button' data-ref='close-form'>X</span></h3>
						<select name='course_id'>
							{$this->select_course()}
						</select>
						<input type='textbox' name='subject' placeholder='Subject Name'>
						<input type='textbox' name='desc' placeholder='Descriptive Title'>
						<input type='hidden' name='add-subject'>
						<input type='submit' value='add subject'>
					</form>
				</div>
			";
		}
		function select_course(){
			$sql = "SELECT id,course FROM course";
			$res = $this->query($sql);
			$response = '';
			while(list($id,$course) = $res->fetch_array()){
				$response .= "
					<option value='$id'>$course</option>
				";
			}
			return $response;
		}
		function admin_subjects(){
			$response = '';
			$sql = "SELECT * FROM subject";
			$res = $this->query($sql);
			$response .= "<table>";
			$response .= "
				<tr class='header'>
					<th>Subject</th>
					<th>Descriptive Title</th>
				</tr>
			";
			while(list($id,$cid,$subject,$desc) = $res->fetch_array()){
				$response .= "
					<tr>
						<td>$subject</td>
						<td>$desc</td>
					</tr>
				";
			}
			$response .= "</table>";
			return $response;
		}
		function stats(){
			$response = '';
			$sql = "SELECT COUNT(*) as topic FROM topic";
			$res = $this->query($sql);
			list($topic) = $res->fetch_array();
			$sql = "SELECT COUNT(*) as answer FROM answer";
			$res = $this->query($sql);
			list($answer) = $res->fetch_array();
			$sql = "SELECT COUNT(*) as members FROM user";
			$res = $this->query($sql);
			list($members) = $res->fetch_array();
			$response = "
				<div>
					<span>Topics: $topic</span>
					<span>Answer: $answer</span>
					<span>Members: $members</span>
				</div>
			";
			return json_encode($response);
		}
		function settings(){
			$response = '';
			$sql = "SELECT user_id,username,user_type,fname,lname,course,year,email,bdate,date_created FROM user WHERE user_id = '{$_SESSION['user_id']}'";
			$res = $this->query($sql);
			list($user_id,$username,$type,$fname,$lname,$course,$year,$email,$bdate,$date_created) = $res->fetch_array();
			$verified = $this->verified() == 1 ? "<a href='/LNUForum/Verify/'>Verify Account</a>" : '';
			return "
				<div class='whitebg' id='settings'>
					<div class='table'>
						<form class='tr btn' data-ref='edit-settings' data-id='$user_id' data-options='change-fname'>
							<span class='td'>First Name</span>
							<span class='td'>$fname</span>
							<span class='edit'>Edit</span>
						</form>
						<form class='tr btn' data-ref='edit-settings' data-id='$user_id' data-options='change-lname'>
							<span class='td'>Last Name</span>
							<span class='td'>$lname</span>
							<span class='edit'>Edit</span>
						</form>
						<form class='tr btn' data-ref='edit-settings' data-id='$user_id' data-options='change-username'>
							<span class='td'>Username</span>
							<span class='td'>$username</span>
							<span class='edit'>Edit</span>
						</form>
						<form class='tr btn' data-ref='edit-settings' data-id='$user_id' data-options='change-course'>
							<span class='td'>Course</span>
							<span class='td'>$course</span>
							<span class='edit'>Edit</span>
						</form>
						<form class='tr btn' data-ref='edit-settings' data-id='$user_id' data-options='change-email'>
							<span class='td'>Email</span>
							<span class='td'>$email</span>
							<span class='edit'>Edit</span>
						</form>
						$verified
					</div>
				</div>
			";
		}
		function verify(){
			$response = '';
			$sql = "SELECT * FROM user WHERE user_id = '{$_SESSION['user_id']}'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			$response = "
				<div class='whitebg' id='verification-page'>
					<div>
						<p>Enter the verification code we sent to your email</p>
						<p>With these, you will no have control to your account for change password, create topics etc.</p>
					</div>
					<div>
						<div style='width: 400px;' class='clearfix'>
							<form id='verify'>
								<input type='text' name='code' class='borderless'>
								<a href='#' data-ref='resend' class='btn right-btn'>Resend</a>
								<input type='submit' value='Verify' class='hide' id='verify'>
								<input type='hidden' name='verify'>
							</form>
							<span href='#' for='verify' class='btn small_box' data-ref='verify-email'>Verify</span>
						</div>
					</div>
				</div>
			";
			return $response;
		}
		function verified(){
			$sql = "SELECT * FROM user WHERE user_id = '{$_SESSION['user_id']}' AND verified = 0";
			$res = $this->query($sql);
			if($res->num_rows){
				return true;
			}
			else{
				return false;
			}
		}
		function getUserName($id){
			$sql = "SELECT username FROM user WHERE user_id = '{$id}'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['username'];
		}
		function getFullName($id){
			$sql = "SELECT CONCAT(fname,' ',lname) AS fullname FROM user WHERE user_id = '{$id}'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return $data['fullname'];
		}
		function notif(){
			return "
				<div class='whitebg' id='notif'>
					{$this->notif_data()}
				</div>
			";
		}
		function getTopicLink($id){
			$sql = "SELECT course_id,subject_id,topic_title FROM topic WHERE id = '{$id}'";
			$res = $this->query($sql);
			$data = $res->fetch_array();
			return "/LNUForum/{$this->getCourse($data['course_id'])}/{$this->getSubjectName($data['subject_id'])}/Topic/$id/{$data['topic_title']}";
		}
		function notif_data(){
			$response = '';
			$sql = "SELECT * FROM notif WHERE user_id = '{$_SESSION['user_id']}'";
			$res = $this->query($sql);
			while(list($id,$user_id,$from_id,$table,$table_id,$date,$status) = $res->fetch_array()){
				/*if($table == 'table'){
					$inner_sql = "SELECT * FROM table WHERE id = '$table_id'";
					$inner_res = $this->query($inner_sql);
					$data = $inner_res->fetch_array();
				}*/

				if($status == 'answer'){
					$response .= "
						<div>
							{$this->mini_profile($from_id)}
							<a href='{$this->getTopicLink($table_id)}'>{$this->getFullName($from_id)} answer your Topic</a>	
						</div>
					";
				}
				elseif($status == 'like'){
					$response .= "
						<div>
							{$this->mini_profile($from_id)}
							<a href='{$this->getTopicLink($table_id)}'>{$this->getFullName($from_id)} liked your Topic</a>
						</div>
					";
				}
				elseif($status == 'unlike'){
					$response .= "
						<div>
							{$this->mini_profile($from_id)}
							<a href='{$this->getTopicLink($table_id)}'>{$this->getFullName($from_id)} unliked your Topic</a>
						</div>
					";
				}
			}
			return $response;
		}
		function users(){
			$response = '';
			$sql = "SELECT user_id,username,fname,lname FROM user WHERE user_id != {$_SESSION['user_id']}";
			$res = $this->query($sql);
			$response .= "<div style='width: 40%;border-left: 1px solid #999; padding: 12px;'>";
				while(list($user_id,$username,$fname,$lname) = $res->fetch_array()){
					$response .= "
						<div class='btn' data-ref='chat' data-val='$username' role=button>
							{$this->mini_profile($user_id)}
							<span>$fname $lname</span>
						</div>
					";
				}
			$response .= "</div>";
			return $response;
		}
	}

?>