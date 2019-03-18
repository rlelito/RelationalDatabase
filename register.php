<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>RBD - project - register</title>
	<style>
		#goBack {
			font-size: 25px;
			border-radius: 10px;
			padding: 5px;
			margin:15px;
		}
		div {
			width: 1200px;
			margin: auto;
			margin-bottom: 100px;
		}
		table {
			border-spacing: 1em 1rem;
			text-align: center;
		}
		button, input, select {
			border-radius: 5px;
			padding: 5px;
		}
	</style>
</head>

<body>
<?php
	# Connecting with database
	include("infoConnect.php");
	$db = mysqli_connect($serverName, $userName, $password, $dbName);
	if(mysqli_connect_errno()){
		{echo "wystąpił błąd z połączeniem do dazy";
		exit;}
	}

	# add new user
	if(isset($_GET['action']) && $_GET['action'] == 'addUser') {
		if(isset($_POST['aLogin']) && isset($_POST['aMail']) && isset($_POST['aPassword'])) {
			$login = $_POST['aLogin'];
			$mail = $_POST['aMail'];
			$password = $_POST['aPassword'];
			$lang = $_POST['aLang'];
			$dbName = 'pU'.$_POST['aLogin'];

			$arrUsers[] = '';
			$aUsers = $db->query("SELECT login FROM pUsers;");
			foreach ($aUsers as $a)
				array_push($arrUsers, $a['login']);

			if(!in_array($login, $arrUsers)) {
				$add = $db->prepare("INSERT INTO pUsers VALUES(0, '$login', '$mail', '$password', $lang, '$dbName');");

				$sql = "CREATE TABLE `".$dbName."` (
					id_pos INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
					id_series INT,
					id_status INT,
					epCurrent INT
				);";
				if($add->execute() == true && $db->query($sql) == true)
					echo '<script>alert("User and watching database added successfully to database");</script>';
			}
			else
				echo '<script>alert("User wtih login: '.$login.' already exist");</script>';
		}
	}
	# delete user
	if(isset($_GET['action']) && $_GET['action'] == 'delUser') {
		$id = $_POST['id'];
		$dbName = $_POST['dbName'];
		$del = $db->prepare("DELETE FROM pUsers WHERE id_user = $id;");
		$sql = "DROP TABLE `".$dbName."`;";
		$delDB = $db->prepare($sql);
		if($del->execute() == true && $delDB->execute() == true)
			echo '<script>alert("User and watching database deleted successfully");</script>';
	}


	echo '<div>';
	echo '<a href="index.html"><button id=\'goBack\'><b>Go Back</b></button></a><br/><br/>';

	# showing users data in table
	echo '<h2>Users</h2><table><tr><th>ID</th><th>Login</th><th>Mail</th><th>Password</th><th>Prefered language</th><th>Database name</th></tr>';
	$user = $db->query("SELECT id_user, login, mail, password, pLanguage.lang, dbName FROM pUsers INNER JOIN pLanguage ON pUsers.id_lang = pLanguage.id_lang ORDER BY id_user;");
	foreach ($user as $u) {
		echo '<tr><td>'.$u['id_user'].'</td><td>'.$u['login'].'</td><td>'.$u['mail'].'</td><td>'.$u['password'].'</td><td>'.$u['lang'].'</td><td>'.$u['dbName'].'</td>';
		echo '<td><form action="register.php?action=delUser" method="POST">
			<input type="submit" value="Delete"/>
			<input name="id" type="hidden" value="'.$u['id_user'].'"/>
			<input name="dbName" type="hidden" value="'.$u['dbName'].'"/></form></td></tr>';
	}
	echo '</table>';

	# add user section
	echo '<h3>Add user:</h3>
		<form action="register.php?action=addUser" method="POST">
		Login: <input type="text" name="aLogin" required />&nbsp;&nbsp;
		Mail: <input type="text" name="aMail" required />&nbsp;&nbsp;
		Password: <input type="text" name="aPassword" required />&nbsp;&nbsp;';
	echo 'Prefered language: <select name="aLang" required>';
		$selectLang = $db->query("SELECT * from pLanguage;");
		foreach ($selectLang as $sL) {
			echo '<option value="'.$sL['id_lang'].'">'.$sL['lang'].'</option>';
		}
		echo '</select>&nbsp;&nbsp;';
	echo '<input value="Add" type="submit"/></form>';


	echo '</div>';
?>
</body>
</html>