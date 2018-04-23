<?php
	session_set_cookie_params(99999999);
	session_start();
	require_once('includes/php/functions.php');
	$html = new html();
	$user = new user();
	if(isset($_GET['logout'])){
		session_destroy();
		header('Location: /LNUForum/');
	}
	require_once('includes/php/home.php');
?>