<?php
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
    ini_set('session.cookie_secure', 1);
}
ini_set('session.cookie_httponly', 1);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only run if user is not logged in
if (isset($_SESSION['user_name'])) {
    header("Location: /index.php");
    exit();
} else {
	ob_start();
    include 'header.php';
    include "lang/".get_config_value("Language")."/login.php";

    // Define path to PGP key files
    $currentDir = __DIR__;
    $parentDir = dirname($currentDir);
    $dataFolder = $parentDir . '/data/pgp_keys';

    // Define variables
    $error = '';
    $step = 1;
    $username = '';
    $pgpMessage = '';

    // Step 1: Check if form 1 was submitted
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['username'])) {
        	$username = trim($_POST['username']);
            $keyPath = $dataFolder . '/' . $username . '.txt';

            if (file_exists($keyPath)) {
            	$randomPass = bin2hex(random_bytes(16));
                $_SESSION['random_pass'] = $randomPass;

                $tmpPlain = tempnam(sys_get_temp_dir(), 'plain_');
                $tmpOutput = tempnam(sys_get_temp_dir(), 'enc_');

                file_put_contents($tmpPlain, $randomPass);

                $escapedKey = escapeshellarg($keyPath);
                $escapedPlain = escapeshellarg($tmpPlain);
                $escapedOut = escapeshellarg($tmpOutput);

                shell_exec("gpg --yes --batch --armor --trust-model always --recipient-file $escapedKey --output $escapedOut --encrypt $escapedPlain");
                
                $encrypted = file_get_contents($tmpOutput);
                $pgpMessage = trim($encrypted);

                unlink($tmpPlain);
                unlink($tmpOutput);

                $step = 2;
                
                $_SESSION['temp_user_name'] = $username;
            } else {
            	$error = htmlspecialchars($lang_login['noUser']);
            }
        } elseif (isset($_POST['decrypted'])) {
            $input = trim($_POST['decrypted']);
            if (isset($_SESSION['random_pass']) && $input === trim($_SESSION['random_pass'])) {
                session_regenerate_id(true);
                $_SESSION['user_name'] = $_SESSION['temp_user_name'];
                unset($_SESSION['temp_user_name']);
                unset($_SESSION['random_pass']);
                echo '<meta http-equiv="refresh" content="0;URL=/index.php">';
                exit();
            } else {
                $step = 2;
                $pgpMessage = $_POST['pgp_message']; // preserve PGP message
                $error = htmlspecialchars($lang_login['noDecrypted']);
            }
        }
    }
?>

<style>
    form {
        max-width: 400px;
        margin: 20px auto;
        padding: 15px;
        border: 1px solid #ccc;
        border-radius: 10px;
        font-family: sans-serif;
    }
    input, textarea {
        width: 100%;
        padding: 10px;
        margin-top: 10px;
        box-sizing: border-box;
        border-radius: 5px;
        border: 1px solid #ccc;
    }
    input[type="submit"] {
        background-color: #007bff;
        color: white;
        border: none;
        cursor: pointer;
    }
    input[type="submit"]:hover {
        background-color: #0056b3;
    }
    .error {
        color: red;
        margin-top: 10px;
        text-align: center;
    }
</style>

<?php if ($step === 1): ?>
<!-- Form 1: Ask for username -->
<form method="post">
    <h3><?= htmlspecialchars($lang_login['userName']) ?></h3>
    <input type="text" name="username" placeholder="<?= htmlspecialchars(str_replace(":", "", ($lang_login['userName']))) ?>" required>
    <input type="submit" value="<?= htmlspecialchars($lang_login['continue']) ?>">
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</form>

<?php elseif ($step === 2): ?>
<!-- Form 2: Show PGP message and input field -->
<form method="post">
    <h3><?= htmlspecialchars($lang_login['decrypt']) ?></h3>
    <label><?= htmlspecialchars($lang_login['encrypted']) ?></label>
    <textarea readonly rows="6"><?= htmlspecialchars($pgpMessage) ?></textarea>
    <input type="hidden" name="pgp_message" value="<?= htmlspecialchars($pgpMessage) ?>">
    <label><?= htmlspecialchars($lang_login['token']) ?></label>
    <input type="text" name="decrypted" placeholder="<?= htmlspecialchars($lang_login['enterToken']) ?>" required>
    <input type="submit" value="<?= htmlspecialchars($lang_login['login']) ?>">
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</form>
</body>
</html>
<?php endif; ?>

<?php } // end of else 
ob_end_flush();
?>