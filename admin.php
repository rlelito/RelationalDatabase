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
	# connecting with database
	include("infoConnect.php");
	$db = mysqli_connect($serverName, $userName, $password, $dbName);
	if(mysqli_connect_errno()){
		{echo "wystąpił błąd z połączeniem do dazy";
		exit;}
	}


	# add series
	if(isset($_GET['action']) && $_GET['action'] == 'addSeries') {
		if(isset($_POST['aTitle']) && isset($_POST['aStudio']) && isset($_POST['aEpisodes']) && isset($_POST['aLang']) ) {
			$title = $_POST['aTitle'];
			$studio = $_POST['aStudio'];
			$episodes = $_POST['aEpisodes'];
			$lang = $_POST['aLang'];
			$add = $db->prepare("INSERT INTO pSeries VALUES(0, '$title', $studio, $episodes, $lang);");
			if($add->execute() == true)
				echo '<script>alert("Series added successfully to database");</script>'; 
		}
	}
	#delete series
	if(isset($_GET['action']) && $_GET['action'] == 'delSeries') {
		$id = $_POST['id'];
		$del = $db->prepare("DELETE FROM pSeries WHERE id_series = $id;");
		if($del->execute() == true)
			echo '<script>alert("Series deleted successfully");</script>';
	}


	# add language
	if(isset($_GET['action']) && $_GET['action'] == 'addLang') {
		if(isset($_POST['aLang']) ) {
			$lang = $_POST['aLang'];
			$add = $db->prepare("INSERT INTO pLanguage VALUES(0, '$lang');");
			if($add->execute() == true)
				echo '<script>alert("Language added successfully to database");</script>'; 
		}
	}
	# delete language
	if(isset($_GET['action']) && $_GET['action'] == 'delLang') {
		$id = $_POST['id'];
		$del = $db->prepare("DELETE FROM pLanguage WHERE id_lang = $id;");
		if($del->execute() == true)
			echo '<script>alert("Language deleted successfully");</script>';
	}


	# add studio
	if(isset($_GET['action']) && $_GET['action'] == 'addStudio') {
		if(isset($_POST['aStudio']) ) {
			$studio = $_POST['aStudio'];
			$add = $db->prepare("INSERT INTO pStudio VALUES(0, '$studio');");
			if($add->execute() == true)
				echo '<script>alert("Studio added successfully to database");</script>'; 
		}
	}
	# delete studio
	if(isset($_GET['action']) && $_GET['action'] == 'delStudio') {
		$id = $_POST['id'];
		$del = $db->prepare("DELETE FROM pStudio WHERE id_studio = $id;");
		if($del->execute() == true)
			echo '<script>alert("Studio deleted successfully");</script>';
	}


	echo '<div>';
	echo '<a href="index.html"><button id=\'goBack\'><b>Go Back</b></button></a><br/><br/>';

# showing TV Series list
	$arrSeries[] = '';
	$aSeries = $db->query("SELECT DISTINCT pSeries.id_series FROM pUwojtek INNER JOIN pSeries ON pUwojtek.id_series = pSeries.id_series;");
	foreach ($aSeries as $a)
		array_push($arrSeries, $a['id_series']);

	echo '<h2>Series</h2><table><tr><th>Title</th><th>Studio</th><th>Episodes</th><th>Voice language</th></tr>';
	$series = $db->query("SELECT id_series, title, pStudio.name, episodes, pLanguage.lang FROM ((pSeries INNER JOIN pStudio ON pSeries.id_studio = pStudio.id_studio) INNER JOIN pLanguage ON pSeries.id_lang = pLanguage.id_lang) ORDER BY title;");
	foreach ($series as $s) {
		echo '<tr><td>'.$s['title'].'</td><td>'.$s['name'].'</td><td>'.$s['episodes'].'</td><td>'.$s['lang'].'</td>';
		if (!in_array($s['id_series'], $arrSeries)) {
			echo '<td><form action="admin.php?action=delSeries" method="POST">
				<input type="submit" value="Delete"/>
				<input name="id" type="hidden" value="'.$s['id_series'].'"/></form></td></tr>';
		}
		else
			echo '<td style="color: red;">Series is currently used</td></tr>';
	}
	echo '</table>';

# section to add new series to database
	echo '<h3>Add series:</h3>
		<form action="admin.php?action=addSeries" method="POST">
		Title: <input type="text" name="aTitle" size="50" required />&nbsp;&nbsp;
		Studio: <select name="aStudio" required>';
		$selectStudio = $db->query("SELECT * from pStudio;");
		foreach ($selectStudio as $sS) {
			echo '<option value="'.$sS['id_studio'].'">'.$sS['name'].'</option>';
		}
		echo '</select>&nbsp;&nbsp;';
	echo 'Episodes: <input type="number" name="aEpisodes" required />&nbsp;&nbsp;
		Voice language: <select name="aLang" required>';
		$selectLang = $db->query("SELECT * from pLanguage;");
		foreach ($selectLang as $sL) {
			echo '<option value="'.$sL['id_lang'].'">'.$sL['lang'].'</option>';
		}
		echo '</select>&nbsp;&nbsp;';
	echo '<input value="Add" type="submit"/></form>';


# showing current language list
	$arrLang[] = '';
	$aLang = $db->query("SELECT DISTINCT pLanguage.id_lang FROM pLanguage INNER JOIN pSeries ON pSeries.id_lang = pLanguage.id_lang;");
	foreach ($aLang as $a)
		array_push($arrLang, $a['id_lang']);
	$aLang = $db->query("SELECT DISTINCT pLanguage.id_lang FROM pLanguage INNER JOIN pUsers ON pUsers.id_lang = pLanguage.id_lang;");
	foreach ($aLang as $a)
		if (!in_array($a['id_lang'], $arrLang))
			array_push($arrLang, $a['id_lang']);

	echo '<br/><hr><br/><h2>Languages</h2><table>';
	$lang = $db->query("SELECT id_lang, lang FROM pLanguage ORDER BY lang;");
	foreach ($lang as $l) {
		echo '<tr><td>'.$l['lang'].'</td>';
		if (!in_array($l['id_lang'], $arrLang)) {
			echo '<td><form action="admin.php?action=delLang" method="POST">
				<input type="submit" value="Delete"/>
				<input name="id" type="hidden" value="'.$l['id_lang'].'"/></form></td></tr>';
		}
		else
			echo '<td style="color: red;">Language is currently used</td></tr>';
	}
	echo '</table>';

# section to add new language
	echo '<h3>Add language:</h3>
		<form action="admin.php?action=addLang" method="POST">
		Language: <input type="text" name="aLang" required />&nbsp;&nbsp;';
	echo '<input value="Add" type="submit"/></form>';


# showing studio list
	$arrStudio[] = '';
	$aStudio = $db->query("SELECT DISTINCT pStudio.id_studio FROM pStudio INNER JOIN pSeries ON pSeries.id_studio = pStudio.id_studio;");
	foreach ($aStudio as $a)
		array_push($arrStudio, $a['id_studio']);

	echo '<br/><hr><br/><h2>Studio</h2><table>';
	$studio = $db->query("SELECT id_studio, name FROM pStudio ORDER BY name;");
	foreach ($studio as $st) {
		echo '<tr><td>'.$st['name'].'</td>';
		if (!in_array($st['id_studio'], $arrStudio)) {
			echo '<td><form action="admin.php?action=delStudio" method="POST">
				<input type="submit" value="Delete"/>
				<input name="id" type="hidden" value="'.$st['id_studio'].'"/></form></td></tr>';
		}
		else
			echo '<td style="color: red;">Studio is currently used</td></tr>';
	}
	echo '</table>';

# section to add new studio
	echo '<h3>Add studio:</h3>
		<form action="admin.php?action=addStudio" method="POST">
		Studio: <input type="text" name="aStudio" required />&nbsp;&nbsp;';
	echo '<input value="Add" type="submit"/></form>';


	echo '</div>';
?>
</body>
</html>