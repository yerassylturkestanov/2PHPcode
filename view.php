<?php
session_start();

require_once "pdo.php";
require_once "util.php";

if(! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz AND user_id = :uid");
$stmt->execute(array(":xyz" => $_GET['profile_id'], ":uid" => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
  $_SESSION['error'] = 'Could not load profile';
  header( 'Location: index.php' ) ;
  return;
}

$fname = htmlentities($row['first_name']);
$lname = htmlentities($row['last_name']);
$em = $row['email'];
$headline = $row['headline'];
$summary = $row['summary'];
$profile_id = $_GET['profile_id'];

$positions = loadPos($pdo, $_GET['profile_id']);
$educations = loadEdu($pdo, $_GET['profile_id']);
?>


<!DOCTYPE html>
<html>
<head>
<title>Yerassyl Turkestanov View Page</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
<script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
<? require_once "head.php"; ?>
</head>

<body>
<h1>Yerassyl Turkestanov View Page</h1>

<p>First Name: <?=htmlentities($fname); ?></p>
<p>Last Name: <?=htmlentities($lname); ?></p>
<p>Email: <?= $em ?></p>
<p>Headline: <?=htmlentities($headline); ?></p>
<p>Summary: <?=htmlentities($summary); ?></p>

<p>Educations: </p>
<ul>

<?php
$edu = 0;
foreach($educations as $education) {
$edu++;
echo('<li>'.$education['year'].': ');
echo(htmlentities($education['name']).'</li>');
}
?>

</ul>

<p>Positions: </p>
<ul>

<?php
$pos = 0;
foreach($positions as $position) {
$pos++;
echo('<li>'.$position['year'].': ');
echo(htmlentities($position['description']).'</li>');
}
?>

</ul>
<a href="index.php">Done</a>

</body>
</html>
