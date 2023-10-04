<?php
  session_start();

  if ( ! isset($_SESSION["email"]) ) {
      die("<p>ACCESS DENIED</p>");
    }

    require_once "pdo.php";

    if ( isset($_POST['delete']) && isset($_POST['profile_id']) ) {
        $sql = "DELETE FROM profile WHERE profile_id = :pid";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(':pid' => $_POST['profile_id']));
        $_SESSION['success'] = 'Record deleted';
        header( 'Location: index.php' ) ;
        return;
    }

    // Guardian: Make sure that auto_id is present
    if ( ! isset($_GET['profile_id']) ) {
      $_SESSION['error'] = "Missing profile_id";
      header('Location: index.php');
      return;
    }

    $stmt = $pdo->prepare("SELECT email, profile_id FROM profile where profile_id = :xyz");
    $stmt->execute(array(":xyz" => $_GET['profile_id']));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ( $row === false ) {
        $_SESSION['error'] = 'Bad value for profile_id';
        header( 'Location: index.php' ) ;
        return;
    }
?>

<!DOCTYPE html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="utf-8">
    <title>Yerassyl Turkestanov. Deleting Page</title>
  </head>
  <body>

<p>Confirm: Deleting <?= htmlentities($row['email']) ?></p>

<form method="post">
<input type="hidden" name="profile_id" value="<?= $row['profile_id'] ?>">
<input type="submit" value="Delete" name="delete">
<a href="index.php">Cancel</a>
</form>
</body>
</html>
