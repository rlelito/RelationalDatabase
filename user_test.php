<!DOCTYPE html>
<html lang="pl">
<head>
	<meta charset="utf-8">
	<title>RBD - project - admin</title>
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
	# połączenie z bazą danych
	include("infoConnect.php");
	$db = mysqli_connect($serverName, $userName, $password, $dbName);
	if(mysqli_connect_errno()){
		{echo "wystąpił błąd z połączeniem do dazy";
		exit;}
	}

	$obecny = "testowy";

	echo '<div>';
	echo '<a href="index.html"><button id=\'goBack\'><b>Go Back</b></button></a>';
	echo '<a href="user_test.php"><button id=\'goBack\'><b>Refresh</b></button></a>';
	echo '<br/><br/>';

//User info
	if(isset($_GET['action']) && $_GET['action'] == 'updateLang')
	{
		$id = $_POST['id'];
		$up = $db->prepare("UPDATE pUsers SET id_lang = $id WHERE login = \"".$obecny."\";");
		if ($up->execute() == true)
			echo '<script>alert("Language updated");</script>';
	}

	$user = $db->query("SELECT id_user, login, mail, password, pUsers.id_lang, pLanguage.lang, dbName FROM pUsers INNER JOIN pLanguage ON pUsers.id_lang = pLanguage.id_lang WHERE login = \"".$obecny."\";");
	foreach ($user as $u) {
		echo '<h2>'.$u['login'].'\'s Watching List</h2>';
		echo '<b>Mail:</b> '.$u['mail'];
		echo '<br/><b>Password:</b> '.$u['password'];
		echo '<br/><b>DB Name:</b> '.$u['dbName'];
		$dbName = $u['dbName'];
		echo '<br/><b>Language:</b> '.$u['lang'];
		echo '&nbsp;&nbsp;&nbsp;<a href="user_test.php?action=editLang"><button>Edit</button></a><br/>';
		$id_lang = $u['id_lang'];
	}
	if(isset($_GET['action']) && $_GET['action'] == 'editLang') {
		$aLang = $db->query("SELECT * from pLanguage;");
		echo '<br/><br/>Choose language:<form action="user_test.php?action=updateLang" method="POST"><select name="id">';
		foreach ($aLang as $aL)
		{	
			if ($id_lang == $aL['id_lang'])
				echo '<option value="'.$aL['id_lang'].'"selected="selected">'.$aL['lang'].'</option>' ;
			else
				echo '<option value="'.$aL['id_lang'].'">'.$aL['lang'].'</option>' ;
		}
		echo '</select><input type="submit" value="Change"/></form>';
	}


//Add - functions
	if(isset($_GET['action']) && $_GET['action'] == 'addPos') {
		$title = $_POST['aTitle'];
		$currEp = $_POST['aCurrEpisode'];
		$status = $_POST['aStatus'];

		$add = $db->prepare("INSERT INTO ".$dbName." VALUES(0, $title, $status, $currEp);");

		if($add->execute() == true)
			echo '<script>alert("Added series to watching list");</script>';
	}

//Lists - functions
	if(isset($_GET['action']) && $_GET['action'] == 'updateCurrentEp') {
		$id = $_POST['id'];
		$maxEp = $_POST['maxEp'];
		$epCurr = $_POST['epCurr'] + 1;

		if($epCurr >= $maxEp)
			$up = $db->prepare("UPDATE ".$dbName." SET epCurrent = ".$maxEp.", id_status = 3 WHERE id_pos = $id;");
		else
			$up = $db->prepare("UPDATE ".$dbName." SET epCurrent = ".$epCurr." WHERE id_pos = $id;");

		if($up->execute() == true)
			echo '<script>alert("Current episodes number updated");</script>';
	}
	if(isset($_GET['action']) && $_GET['action'] == 'updateStatus') {
		$id = $_POST['id'];
		$status = $_POST['aStatus'];
		$up = $db->prepare("UPDATE ".$dbName." SET id_status = ".$status." WHERE id_pos = $id;");
		if($up->execute() == true)
			echo '<script>alert("Status changed");</script>';
	}
	if(isset($_GET['action']) && $_GET['action'] == 'delPos') {
		$id = $_POST['id'];
		$del = $db->prepare("DELETE FROM ".$dbName." WHERE id_pos = $id;");
		if($del->execute() == true)
			echo '<script>alert("Series deleted successfully");</script>';
	}


//Add
	echo '<br/><hr><br/><h3>Add position:</h3>
		<form action="user_test.php?action=addPos" method="POST">
		<b>Title:</b> <select name="aTitle" required>';
		$selectTitle = $db->query("SELECT id_series, title FROM pSeries;");
			$arr[] = '';
			$arrTitle = $db->query("SELECT id_series FROM ".$dbName.";");
			foreach ($arrTitle as $aT) {
				array_push($arr, $aT['id_series']);
			}
		foreach ($selectTitle as $st) {
			if(!in_array($st['id_series'], $arr) )
				echo '<option value="'.$st['id_series'].'">'.$st['title'].'</option>';
		}
		echo '</select>&nbsp;&nbsp;';
	echo '<b>Current episode:</b> <input type="number" value="0" name="aCurrEpisode" required />&nbsp;&nbsp;';
	echo '<b>Status:</b> <select name="aStatus" required>';
		$selectStatus = $db->query("SELECT * FROM pStatus;");
		foreach ($selectStatus as $sS) {
			echo '<option value="'.$sS['id_status'].'">'.$sS['name'].'</option>';
		}
	echo '</select>&nbsp;&nbsp;&nbsp;';
	echo '<input value="Add" type="submit"/></form>';


//Lists
	echo '<br/><hr><br/><table>';
	echo '<tr><td><h2>Watching</h2></td></tr>';
	$watching = $db->query("SELECT id_pos, pSeries.title, pStudio.name, id_status, epCurrent, pSeries.episodes FROM ((".$dbName." INNER JOIN pSeries ON ".$dbName.".id_series = pSeries.id_series) INNER JOIN pStudio ON pStudio.id_studio = pSeries.id_studio) WHERE id_status = 2;");
	echo '<tr><th>Title</th><th>Studio</th><th>Current episode</th><th>+</th><th>Episodes</th><th>Change status</th></tr>';
	foreach ($watching as $w) {
		echo '<tr><td>'.$w['title'].'</td><td>'.$w['name'].'</td><td>'.$w['epCurrent'].'</td>';
		echo '<td><form action="user_test.php?action=updateCurrentEp" method="POST">
			<input type="submit" value="+"/>
			<input name="id" type="hidden" value="'.$w['id_pos'].'"/>
			<input name="maxEp" type="hidden" value="'.$w['episodes'].'"/>
			<input name="epCurr" type="hidden" value="'.$w['epCurrent'].'"/></form></td>';
		echo '<td>'.$w['episodes'].'</td>';
		echo '<td><form action="user_test.php?action=updateStatus" method="POST">
			<select name="aStatus" required>';
			$selectStatus = $db->query("SELECT * from pStatus;");
			foreach ($selectStatus as $sS) {
				if ($w['id_status'] == $sS['id_status'])
					echo '<option value="'.$sS['id_status'].'" selected="selected">'.$sS['name'].'</option>';
				else
					echo '<option value="'.$sS['id_status'].'">'.$sS['name'].'</option>';
			}
		echo '</select>&nbsp;&nbsp;
			<input name="id" type="hidden" value="'.$w['id_pos'].'"/>
			<input value="Update" type="submit"/></form></td>';
		echo '<td><form action="user_test.php?action=delPos" method="POST">
			<input type="submit" value="Delete"/>
			<input name="id" type="hidden" value="'.$w['id_pos'].'"/></form></td></tr>';
	}
//	echo '</table>';

	echo '<tr></tr><tr><td><h2>Completed</h2></td></tr>';
	$completed = $db->query("SELECT id_pos, pSeries.title, pStudio.name, id_status, epCurrent, pSeries.episodes FROM ((".$dbName." INNER JOIN pSeries ON ".$dbName.".id_series = pSeries.id_series) INNER JOIN pStudio ON pStudio.id_studio = pSeries.id_studio) WHERE id_status = 3;");
	echo '<tr><th>Title</th><th>Studio</th><th>Current episode</th><th></th><th>Episodes</th><th>Change status</th></tr>';
	foreach ($completed as $c) {
		echo '<tr><td>'.$c['title'].'</td><td>'.$c['name'].'</td><td>'.$c['epCurrent'].'</td>';
		echo '<td></td>';
		echo '<td>'.$c['episodes'].'</td>';
		echo '<td><form action="user_test.php?action=updateStatus" method="POST">
			<select name="aStatus" required>';
			$selectStatus = $db->query("SELECT * from pStatus;");
			foreach ($selectStatus as $sS) {
				if ($c['id_status'] == $sS['id_status'])
					echo '<option value="'.$sS['id_status'].'" selected="selected">'.$sS['name'].'</option>';
				else
					echo '<option value="'.$sS['id_status'].'">'.$sS['name'].'</option>';
			}
		echo '</select>&nbsp;&nbsp;
			<input name="id" type="hidden" value="'.$c['id_pos'].'"/>
			<input value="Update" type="submit"/></form></td>';
		echo '<td><form action="user_test.php?action=delPos" method="POST">
			<input type="submit" value="Delete"/>
			<input name="id" type="hidden" value="'.$c['id_pos'].'"/></form></td></tr>';
	}
//	echo '</table>';

	echo '<tr></tr><tr><td><h2>Stopped</h2></td></tr>';
	$stopped = $db->query("SELECT id_pos, pSeries.title, pStudio.name, id_status, epCurrent, pSeries.episodes FROM ((".$dbName." INNER JOIN pSeries ON ".$dbName.".id_series = pSeries.id_series) INNER JOIN pStudio ON pStudio.id_studio = pSeries.id_studio) WHERE id_status = 4;");
	echo '<tr><th>Title</th><th>Studio</th><th>Current episode</th><th></th><th>Episodes</th><th>Change status</th></tr>';
	foreach ($stopped as $s) {
		echo '<tr><td>'.$s['title'].'</td><td>'.$s['name'].'</td><td>'.$s['epCurrent'].'</td>';
		echo '<td></td>';
		echo '<td>'.$s['episodes'].'</td>';
		echo '<td><form action="user_test.php?action=updateStatus" method="POST">
			<select name="aStatus" required>';
			$selectStatus = $db->query("SELECT * from pStatus;");
			foreach ($selectStatus as $sS) {
				if ($s['id_status'] == $sS['id_status'])
					echo '<option value="'.$sS['id_status'].'" selected="selected">'.$sS['name'].'</option>';
				else
					echo '<option value="'.$sS['id_status'].'">'.$sS['name'].'</option>';
			}
		echo '</select>&nbsp;&nbsp;
			<input name="id" type="hidden" value="'.$s['id_pos'].'"/>
			<input value="Update" type="submit"/></form></td>';
		echo '<td><form action="user_test.php?action=delPos" method="POST">
			<input type="submit" value="Delete"/>
			<input name="id" type="hidden" value="'.$s['id_pos'].'"/></form></td></tr>';
	}
//	echo '</table>';

	echo '<tr></tr><tr><td><h2>Plan to watch</h2></td></tr>';
	$planToWatch = $db->query("SELECT id_pos, pSeries.title, pStudio.name, id_status, epCurrent, pSeries.episodes FROM ((".$dbName." INNER JOIN pSeries ON ".$dbName.".id_series = pSeries.id_series) INNER JOIN pStudio ON pStudio.id_studio = pSeries.id_studio) WHERE id_status = 1;");
	echo '<tr><th>Title</th><th>Studio</th><th>Current episode</th><th></th><th>Episodes</th><th>Change status</th></tr>';
	foreach ($planToWatch as $p) {
		echo '<tr><td>'.$p['title'].'</td><td>'.$p['name'].'</td><td>'.$p['epCurrent'].'</td>';
		echo '<td></td>';
		echo '<td>'.$p['episodes'].'</td>';
		echo '<td><form action="user_test.php?action=updateStatus" method="POST">
			<select name="aStatus" required>';
			$selectStatus = $db->query("SELECT * from pStatus;");
			foreach ($selectStatus as $sS) {
				if ($p['id_status'] == $sS['id_status'])
					echo '<option value="'.$sS['id_status'].'" selected="selected">'.$sS['name'].'</option>';
				else
					echo '<option value="'.$sS['id_status'].'">'.$sS['name'].'</option>';
			}
		echo '</select>&nbsp;&nbsp;
			<input name="id" type="hidden" value="'.$p['id_pos'].'"/>
			<input value="Update" type="submit"/></form></td>';
		echo '<td><form action="user_test.php?action=delPos" method="POST">
			<input type="submit" value="Delete"/>
			<input name="id" type="hidden" value="'.$p['id_pos'].'"/></form></td></tr>';
	}
	echo '</table>';

	echo '</div>';
?>
</body>
</html>