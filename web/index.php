<?php
session_start();
$_SESSION["userID"] = "";
session_unset();
session_destroy(); 
?>
<html>
<head>
</head>
<body>
	<h1></h1><br>
	<h2></h2><br>
	<hr>
	<form action="login.php" method="POST">
		<b>Username</b>
		<input type="text" placeholder="username" name="user_name"><br><br>
		<b>Password</b>
		<input type="password" placeholder="password" name="user_pass"><br><br>
		<input type="submit" value="Sign in">
	<form>
</body>
</html>