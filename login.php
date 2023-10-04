<?php
    session_start();

    if ( isset($_POST['cancel'] ) ) {
        // Redirect the browser to index.php
        header("Location: index.php");
        return;
    }

    $salt = 'XyZzy12*_';

    require_once "pdo.php";

    if ( isset($_POST["email"]) && isset($_POST["pass"]) ) {
        unset($_SESSION["email"]);  // Logout current user
        if ((strpos($_POST["email"], '@')) == false) {
            $_SESSION["error"] = "Email must have an at-sign (@)";
            error_log("Login fail ".$_POST['account']." $check");
            header( 'Location: login.php' ) ;
            return;
        } else {
                $sql = "SELECT user_id FROM users WHERE email = :em and password = :pass";
                $stmt = $pdo->prepare($sql);
                $stmt->execute(array(
                'em' => $_POST['email'],
                'pass' => hash('md5', $salt.$_POST["pass"])));
                
                $count = $stmt->rowCount();
                if($count>0){
                    $_SESSION["email"] = $_POST["email"];

                    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    foreach ( $rows as $row ) {
                        $_SESSION["user_id"] = $row['user_id'];
                    }

                    $_SESSION["success"] = "Logged in.";
                    error_log("Login success ".$_SESSION["email"]);
                    header( 'Location: index.php' );
                    return;
                } else {
                    $_SESSION["error"] = "Incorrect email or password";
                    header( 'Location: login.php' );
                    return;
                }
        } 
    }
?>

<!DOCTYPE html>
<html>
<head>
<title>Yerassyl Turkestanov Login Page</title>
</head>

<body style="font-family: sans-serif;">
<div class="container">
<h1>Yerassyl Turkestanov Login Page</h1>

<?php
    if ( isset($_SESSION["error"]) ) {
        echo('<p style="color:red">'.$_SESSION["error"]."</p>\n");
        unset($_SESSION["error"]);
    }
?>

<form method="POST" action="login.php">
<label for="email">Email</label>
<input type="text" name="email" id="email"><br/>
<label for="id_1723">Password</label>
<input type="password" name="pass" id="id_1723"><br/>
<p></p>
<input type="submit" onclick="return doValidate();" value="Log In">
<input type="submit" name="cancel" value="Cancel">
</form>
<script>
    function doValidate() {
    console.log('Validating...');
    try {
        pw = document.getElementById('id_1723').value;
        console.log("Validating pw="+pw);
        if (pw == null || pw == "") {
            alert("Both fields must be filled out");
            return false;
        }
        return true;
    } catch(e) {
        return false;
    }
    return false;
}
</script>

</div>
</body>
</html>
