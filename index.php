<?php
  session_start(); //Начало сессии

if ( isset($_POST['logout'] ) ) {
    header("Location: logout.php");
    return;
}

require_once "pdo.php";

$stmt = $pdo->query("SELECT first_name, last_name, headline, profile_id, user_id FROM Profile");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>


<!DOCTYPE html>
<html>
<head>
  <title>Yerassyl Turkestanov. JavaScript/Profiles Assignment. Main Page</title>
</head>

<body>
  <div class="container">
    <h1>Welcome to Amazing Application</h1>

<table border="1">
  <tr>
    <th>Name</th>
    <th>Headline</th>
    <th>Action</th>
  </tr>

<?php
foreach ( $rows as $row ) {
    echo "<tr><td>";
    $full_name = htmlentities($row['first_name']." ".$row['last_name']);
    echo('<a href="view.php?profile_id='.$row['profile_id'].'">'.$full_name.'<a/>');
    echo("</td><td>");
    echo(htmlentities($row['headline']));
    echo("</td><td>");
    if (isset($_SESSION['user_id'])){
      if ($_SESSION['user_id'] == $row['user_id']){
        echo('<a href="edit.php?profile_id='.$row['profile_id'].'">Edit</a> / ');
        echo('<a href="delete.php?profile_id='.$row['profile_id'].'">Delete</a>');}}
    echo("</td></tr>\n");
}
?>
</table>

</div>

<?php
    if ( isset($_SESSION["error"]) ) {
        echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
        unset($_SESSION["error"]);
    }
?>

<?php
    if ( isset($_SESSION["success"]) ) {
        echo('<p style="color:green">'.$_SESSION["success"]."</p>\n");
        unset($_SESSION["success"]);
    }
?>

<?php 
    if ( ! isset($_SESSION["email"]) ) {
      echo '<p><a href="login.php">Please log in</a></p>';
    } else {
      echo '<p><a href="add.php">Add New Entry</a></p><p></p>';
      echo '<p><a href="logout.php">Logout</a></p><p></p>';
    }
?>

</body>
