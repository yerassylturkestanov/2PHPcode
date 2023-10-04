<?php

require_once "pdo.php";  
require_once "util.php";

session_start();

if ( ! isset($_SESSION["email"]) ) {
    die("<p>ACCESS DENIED</p>");
    return;
}

/*if ( isset($_POST['cancel']) ) {
  header("Location: index.php");
  return;
}*/

if(! isset($_GET['profile_id']) ) {
  $_SESSION['error'] = "Missing profile_id";
  header('Location: index.php');
  return;
}

$profile_id = $_GET['profile_id'];

$stmt = $pdo->prepare("SELECT * FROM profile where profile_id = :xyz AND user_id = :uid");
$stmt->execute(array(":xyz" => $_GET['profile_id'], ":uid" => $_SESSION['user_id']));
$row = $stmt->fetch(PDO::FETCH_ASSOC);
if ( $row === false ) {
  $_SESSION['error'] = 'Could not load profile';
  header( 'Location: index.php' ) ;
  return;
}

if (isset($_POST['first_name']) == false && isset($_POST['last_name']) == false
    && isset($_POST['email']) == false  && isset($_POST['headline']) == false
    && isset($_POST['summary']) == false) {
    } else {
      $msg = validateProfile();
      if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: edit.php?profile_id=".$_GET["profile_id"]);
        return;
      }

      $msg = validatePos();
      if (is_string($msg)) {
        $_SESSION["error"] = $msg;
        header("Location: edit.php?profile_id=".$_GET["profile_id"]);
        return;
      }

      $msg = validateEdu();
      if (is_string($msg)) {
          $_SESSION['error'] = $msg;
          header("Location: edit.php?profile_id=".$_GET["profile_id"]);
          return;
      }

      $sql = "UPDATE profile SET first_name = :fname,
      last_name = :lname, email = :em,
      headline = :headline, summary = :summary
      WHERE profile_id = :pid";
      $stmt = $pdo->prepare($sql);
      $stmt->execute(array(
        ':fname' => $_POST['first_name'],
        ':lname' => $_POST['last_name'],
        ':em' => $_POST['email'],
        ':headline' => $_POST['headline'],
        ':summary' => $_POST['summary'],
        ':pid' => $_GET['profile_id']));



      $statement = $pdo->prepare("DELETE FROM education WHERE profile_id = :pid");
      $statement->execute(array(
          ":pid" => $_GET['profile_id']));

      $rank = 1;
      for($i = 1; $i <= 9; $i++) {
        if (! isset($_POST['eyear'.$i])) continue;
        if (! isset($_POST['education'.$i])) continue;
          $eyear = $_POST['eyear'.$i];
          $education = $_POST['education'.$i];

          $statement = $pdo->prepare("SELECT * FROM institution WHERE name = :education");
          $statement->execute(array(
                ":education" =>  $education));

          $row = $statement->fetch(PDO::FETCH_ASSOC);
          if ($row == false) {
              $insertStmt = $pdo->prepare("INSERT INTO institution (name) VALUES (:education)");
              $insertStmt->execute(array(
                    ":education" => $education));

              $education_id = $pdo->lastInsertId();
            } else {
                $education_id = $row['institution_id'];
            }
            
            $statement = $pdo->prepare("INSERT INTO education (profile_id, institution_id, rank, year)
                                        VALUES (:pid, :education_id, :rank, :year)");
            
            $statement->execute(array(
                ":pid" => $_GET['profile_id'],
                ":education_id" => $education_id,
                ":rank" => $rank,
                ":year" => $eyear));
            $rank++;
        }

      $stmt = $pdo->prepare('DELETE FROM Position WHERE profile_id=:pid');
      $stmt->execute(array(':pid'=>$_GET['profile_id']));

      $rank = 1;
      for ($i=1; $i<=9; $i++){
        if (! isset($_POST['year'.$i]) )  continue;
        if (! isset($_POST['desc'.$i]) )  continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
    
        $sql = "INSERT INTO Position (profile_id, rank, year, description)
                VALUES ( :pid, :rank, :year, :desc)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':rank' => $rank,
            ':year' => $year,
            ':desc' => $desc));
        $rank++;
      }

      $_SESSION['success'] = "Profile_updated";
      header("Location: index.php");
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
<html lang="en" dir="ltr">
<head>
    <meta charset="utf-8">
    <title>Yerassyl Turkestanov. Editing Page</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" integrity="sha384-1q8mTJOASx8j1Au+a5WDVnPi2lkFfwwEAa8hDDdjZlpLegxhjVME1fgjWPGmkzs7" crossorigin="anonymous">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap-theme.min.css" integrity="sha384-fLW2N01lMqjakBkx3l/M9EahuwpSfeNvV63J5ezn3uZzapT0u7EYsXMjQV+0En5r" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.2.1.js" integrity="sha256-DZAnKJ/6XZ9si04Hgrsxu/8s717jcIzLy3oi35EouyE=" crossorigin="anonymous"></script>
    <? require_once "head.php"; ?>
</head>

<style>
.ui-autocomplete {
    background: #87ceeb;
    z-index: 2;
}
</style>

<body>


<p>Edit Profile</p>
<?php flashMessages(); ?>
<form method="post">
<input type="hidden" name="profile_id" value ="<?=htmlentities($_GET['profile_id']); ?>"/>
<p>First Name:
<input type="text" name="first_name" size="40" value="<?= $fname ?>"></p>
<p>Last Name:
<input type="text" name="last_name" value="<?= $lname ?>"></p>
<p>Email:
<input type="text" name="email" value="<?= $em ?>"></p>
<p>Headline:</p>
<p><input type="text" name="headline" size="100" value="<?= $headline ?>"></p>
<p>Summary:</p>
<p><input type="text" name="summary" size="255" value="<?= $summary ?>"></p>
<input type="hidden" name="profile_id" value="<?= $profile_id ?>">

<?php

$edu = 0;
echo('<p>Education: <input type="submit" id="addEdu" value="+">'."\n");
echo('<div id="education_fields">'."\n");
foreach($educations as $education) {
  $edu++;
  echo('<div id="education'.$edu.'">'."\n");
  echo('<p>Year: <input type="text" name="eyear'.$edu.'"');
  echo(' value="'.$education['year'].'" />'."\n");
  echo('<input type="button" value="-" ');
  echo('onclick="$(\'#education'.$edu.'\').remove(); return false;">'."\n");
  echo("</p>\n");
  echo('<p>University: <input class= "school" type="text" name="education'.$edu.'" value="'.htmlentities($education['name']).'"/></p>');
}
echo("</div></p>\n");

?>

<script>
    countEdu = 0;
    $(document).ready(function(){
        window.console && console.log('Document ready called');
        $('#addEdu').click(function(event){
            event.preventDefault();
            if (countEdu >= 9) {
                alert("Maximum of nine education entries exceeded");
                return;
            }
            countEdu++;
            window.console && console.log("Adding education "+countEdu);
            $('#education_fields').append(
                '<div id="education'+countEdu+'"> \
                <p>Year: <input type="text" name="eyear'+countEdu+'" value="" /> \
                <input type="button" value="-" \
                    onclick="$(\'#education'+countEdu+'\').remove(); return false;"></p> \
                <p> University: <input class= "school" type="text" name="education'+countEdu+'" value=""></p> \
                </div>');
                $(".school").autocomplete({
                source: "school.php"})
        });
    });
</script>

<?php

$pos = 0;
echo('<p>Position: <input type="submit" id="addPos" value="+">'."\n");
echo('<div id="position_fields">'."\n");
foreach($positions as $position) {
  $pos++;
  echo('<div id="position'.$pos.'">'."\n");
  echo('<p>Year: <input type="text" name="year'.$pos.'"');
  echo(' value="'.$position['year'].'" />'."\n");
  echo('<input type="button" value="-" ');
  echo('onclick="$(\'#position'.$pos.'\').remove(); return false;">'."\n");
  echo("</p>\n");
  echo('<textarea name="desc'.$pos.'" rows="8" cols="80">'."\n");
  echo(htmlentities($position['description'])."\n");
  echo("\n</textarea>\n</div>\n");
}

echo("</div></p>\n");

?>
<p><input type="submit" value="Save"/>
<a href="index.php">Cancel</a></p>
</form>

<script>
    countPos = <?= $pos ?>;
    $(document).ready(function(){
        window.console && console.log('Document ready called');
        $('#addPos').click(function(event){
            event.preventDefault();
            if (countPos >= 9) {
                alert("Maximum of nine position entries exceeded");
                return;
            }
            countPos++;
            window.console && console.log("Adding position "+countPos);
            $('#position_fields').append(
                '<div id="position'+countPos+'"> \
                <p>Year: <input type="text" name="year'+countPos+'" value="" /> \
                <input type="button" value="-" \
                    onclick="$(\'#position'+countPos+'\').remove(); return false;"></p> \
                <textarea name="desc'+countPos+'" rows="8" cols="80"></textarea> \
                </div>');
        });
    });
</script>

</body>
</html>
