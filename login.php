<?php
require_once "pdo.php";
require_once "head.php";
session_start();

$host = $_SERVER['HTTP_HOST'];
$ruta = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
$url = "http://$host$ruta";

if (isset($_POST["cancel"])) {
    header("Location: $url/index.php");
    die();
}

if (isset($_POST["email"]) && isset($_POST["pass"])) {
    unset($SESSION["name"]);
    unset($SESSION["user_id"]);

    $salt = 'XyZzy12*_';
    $check = hash("md5", $salt . $_POST["pass"]);

    $stmt = $pdo->prepare(
        'SELECT user_id, name, email
        FROM account
        WHERE
        email = :em AND
        password = :pw'
    );
    $stmt->execute(array(':em' => $_POST['email'], ':pw' => $check));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row !== false) {
        error_log("Login success " . $_POST['email'] . "\n", 3, "logs.log");
        $_SESSION["user_id"] = $row["user_id"];
        $_SESSION["name"] = $row["name"];
        $_SESSION['email'] = $row['email'];
        $_SESSION["success"] = "Logged in.";
        header("Location: $url/index.php");
        die();
    } else {
        $_SESSION["error"] = "Incorrect email or password";
        error_log("Login fail " . $_POST['email'] . " $check" . "\n", 3, "logs.log");
        header("Location: $url/login.php");
        die();
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <title>7e5c43ae</title>
</head>

<body style="margin-left: 40px;" >
    <h1>
        Please Log In
    </h1>
    <p>remember to enter in lowercase</p>
    <?php
    if (isset($_SESSION["error"])) {
        echo ('<p style="color: red;">' . $_SESSION["error"]);
        unset($_SESSION["error"]);
    }
    ?>
    <form method="post">
        <label>Email</label>
        <input type="text" name="email" autocomplete="off" id="id_email">
        <br>
        <label>Password</label>
        <input type="password" name="pass" id="id_1723">
        <br>
        <input type="submit" onclick="return doValidate();" value="Log In">
        <input type="submit" name="cancel" value="Cancel">
    </form>
    <script>
        function doValidate() {
            console.log("Validating...");
            try {
                email = document.getElementById("id_email").value;
                pw = document.getElementById("id_1723").value;
                console.log("Validating email=" + email);
                console.log("Validating pw=" + pw);
                if (pw == null || pw == "" || email == null || email == "") {
                    alert("Both fields must be filled out");
                    return false;
                }
                if (email.search("@") === -1) {
                    alert("Email address must contain @");
                    return false;
                }
                return true;
            } catch (e) {
                return false;
            }
            return false;
        }
    </script>
</body>

</html>	