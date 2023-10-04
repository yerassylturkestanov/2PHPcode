<?php
require_once "pdo.php";
require_once "util.php";

session_start();

if ( ! isset($_SESSION["email"]) ) {
    die("<p>Not logged in</p>");
    return;
}

if ( isset($_POST['cancel']) ) {
    header("Location: index.php");
    return;
}

if (isset($_POST['first_name']) == false && isset($_POST['last_name']) == false
    && isset($_POST['email']) == false  && isset($_POST['headline']) == false
    && isset($_POST['summary']) == false) {} else {

    $msg = validateProfile();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg =  validatePos();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $msg = validateEdu();
    if (is_string($msg)) {
        $_SESSION['error'] = $msg;
        header("Location: add.php");
        return;
    }

    $sql = "INSERT INTO profile (first_name, last_name, email, headline, summary, user_id)
            VALUES (:fname, :lname, :em, :hl, :sum, :uid)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':fname' => $_POST['first_name'],
            ':lname' => $_POST['last_name'],
            ':em' => $_POST['email'],
            ':hl' => $_POST['headline'],
            ':sum' => $_POST['summary'],
            ':uid' => $_SESSION['user_id']));
    
    $profile_id = $pdo->lastInsertId();

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

    $rank = 1;
    for ($i=1; $i<=9; $i++){
        if (! isset($_POST['eyear'.$i]) )  continue;
        if (! isset($_POST['education'.$i]) )  continue;
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

        $sql = "INSERT INTO Education (profile_id, institution_id, rank, year)
                VALUES ( :pid,  :education_id, :rank, :year)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(array(
            ':pid' => $profile_id,
            ':education_id' => $education_id,
            ':rank' => $rank,
            ':year' => $eyear));
        $rank++;
    }


    $_SESSION["success"] = "Profile was added";
    header("Location: index.php");
    return;

}

?>


<!DOCTYPE html>
<html>
<head>
<title>Yerassyl Turkestanov Add Page</title>
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


<?php
if ( isset($_SESSION["email"]) ) {
    echo "<h1>Yerassyl Turkestanov. Adding Profile for ";
    echo htmlentities($_SESSION["email"]);
    echo "</h1>\n";
  }
?>

<?php
if ( isset($_SESSION["error"]) ) {
    echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
    unset($_SESSION["error"]);
}
?>

<form method="post">
<p>First Name:
<input type="text" name="first_name" size="40"></p>
<p>Last Name:
<input type="text" name="last_name"></p>
<p>Email:
<input type="text" name="email"></p>
<p>Headline:</p>
<p><input type="text" name="headline" size="100"></p>
<p>Summary:</p>
<p><input type="text" name="summary" size="255"></p>

<p>Education: <input type="submit" id ="addEdu" value="+">
<div id="education_fields"></div></p>

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



<p>Position: <input type="submit" id ="addPos" value="+">
<div id="position_fields"></div></p>

<script>
    countPos = 0;
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

<p><input type="submit" value="Add"/>
<input type="submit" name="cancel" value="Cancel"></p>
</form>
</body>
</html>
