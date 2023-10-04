<?php

function flashMessages() {
    if ( isset($_SESSION["success"]) ) {
        echo('<p style="color:green">'.htmlentities($_SESSION["success"])."</p>\n");
        unset($_SESSION["success"]);
    }

    if ( isset($_SESSION["error"]) ) {
        echo('<p style="color:red">'.htmlentities($_SESSION["error"])."</p>\n");
        unset($_SESSION["error"]);
    }
}


function validateProfile(){
    if (strlen($_POST['first_name']) < 1  || strlen($_POST['last_name']) < 1 || strlen($_POST['email']) < 1
            || strlen($_POST['headline']) < 1 || strlen($_POST['summary']) < 1 ) {
        return "All fields are required";
    }
    
    if ((strpos($_POST["email"], '@')) === false) {
        return "Email must have an at-sign (@)";
    }

    return true;
}

function validatePos() {
    for ($i=1; $i<=9; $i++) {
        if (! isset($_POST['year'.$i]) )  continue;
        if (! isset($_POST['desc'.$i]) )  continue;
        $year = $_POST['year'.$i];
        $desc = $_POST['desc'.$i];
        if (strlen($year) == 0 || strlen($desc) == 0 ) {
            return "All fields are required";
        }

        if ( ! is_numeric($year) ) {
            return "Postition year must be numeric";
        }
    }
    return true;
}

function loadPos($pdo, $profile_id) {
    $stmt = $pdo->prepare ("SELECT * FROM Position
        WHERE profile_id = :pid ORDER BY rank");
    $stmt->execute(array( ':pid' => $profile_id));
    $positions = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $positions[] = $row;
    }
    return $positions;
}

function validateEdu() {
    for ($i=1; $i<=9; $i++) {
        if (! isset($_POST['eyear'.$i]) )  continue;
        if (! isset($_POST['education'.$i]) )  continue;
        $eyear = $_POST['eyear'.$i];
        $education = $_POST['education'.$i];
        if (strlen($eyear) == 0 || strlen($education) == 0 ) {
            return "All fields are required";
        }

        if ( ! is_numeric($eyear) ) {
            return "Education year must be numeric";
        }
    }
    return true;
}

function loadEdu($pdo, $profile_id) {
    $stmt = $pdo->prepare ("SELECT e.institution_id, e.rank, e.year, institution.institution_id, institution.name
        FROM education as e LEFT JOIN institution ON e.institution_id = institution.institution_id WHERE profile_id = :pid ORDER BY e.rank");
    $stmt->execute(array( ':pid' => $profile_id));
    $educations = array();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $educations[] = $row;
    }
    return $educations;
}