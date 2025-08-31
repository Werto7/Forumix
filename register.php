<?php
if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
	ini_set('session.cookie_secure', 1);
}
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect if already logged in
if (isset($_SESSION['user_name'])) {
    header("Location: /index.php");
    exit();
}

ob_start();
include 'header.php';
include "lang/" . get_config_value("Language") . "/register.php";
require_once 'spinCaptcha.php';
$captcha = new CaptchaBox();

// Define data folder path
$currentDir = __DIR__;
$parentDir = dirname($currentDir);
$dataFolder = $parentDir . '/data/pgp_keys';

// Define variables
$error = '';
$success = '';
$step = 1;
$username = '';
$encryptedMessage = '';
$randomPass = '';
$userInput = '';
$pgpMessage = '';

// Check first form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Step 1: Process PGP Key input
    if (isset($_POST['pgp_key'])) {
    	$pgpKey = trim($_POST['pgp_key']);
        $name = '';
        $email = null;

        // Start gpg process to extract UID info without importing key
        $descriptorSpec = [
            0 => ['pipe', 'r'], // stdin
            1 => ['pipe', 'w'], // stdout
            2 => ['pipe', 'w'], // stderr
        ];

        $process = proc_open('gpg --with-colons --import-options show-only --import', $descriptorSpec, $pipes);

        if (is_resource($process)) {
        	fwrite($pipes[0], $pgpKey);
            fclose($pipes[0]);

            $output = stream_get_contents($pipes[1]);
            fclose($pipes[1]);
            fclose($pipes[2]);
            proc_close($process);

            $lines = explode("\n", $output);
            foreach ($lines as $line) {
            	if (strpos($line, 'uid:') === 0) {
            	    $parts = explode(':', $line);
                    if (isset($parts[9])) {
                    	$uid = $parts[9]; //e.g. "Max Diesel <max@example.com>"

                        if (preg_match('/<([^>]+)>/', $uid, $emailMatch)) {
                        	$email = $emailMatch[1];
                            $name = trim(str_replace($emailMatch[0], '', $uid));
                        } else {
                        	$name = trim($uid);
                        }

                        break; //Use only the first uid
                    }
                }
            }
        }

        if (empty($name)) {
        	$error = htmlspecialchars($lang_register['error_noName']);
        } else {
        	$filename = $dataFolder . '/' . $name . '.txt';
            if (file_exists($filename)) {
            	$error = htmlspecialchars($lang_register['error_userExists']);
            } 
            elseif (!empty($email)) {
        	    $error = htmlspecialchars($lang_register['error_emailFound']);
            } else {
            	// Save username in session and proceed
                $_SESSION['pgp_key'] = $pgpKey;
                $_SESSION['username'] = $name;
                $step = 2;

                generate();
                $pgpMessage = $_SESSION['pgp_message'] ?? '';
            }
        }
    } elseif (isset($_POST['decrypted_pass'])) {// Step 2: Validate user decryption input
        $userInput = trim($_POST['decrypted_pass']);
        $randomPass = $_SESSION['passphrase'] ?? '';
        $username = $_SESSION['username'] ?? '';

        if ($userInput !== $randomPass) {
            $error = htmlspecialchars($lang_register['error_decrypt']);
            $step = 2;
            generate();
            $pgpMessage = $_SESSION['pgp_message'] ?? '';
        } else {
        	$result = $captcha->isVerified();
            if ($result === "timeout"){
        	    $error = "Time out";
                $step = 2;
                generate();
                $pgpMessage = $_SESSION['pgp_message'] ?? '';
            } else if ($result === false) {
        	    $error = "Wrong Captcha";
                $step = 2;
                generate();
                $pgpMessage = $_SESSION['pgp_message'] ?? '';
            } else {
                //Register user by saving public key
                $filename = $dataFolder . '/' . $username . '.txt';
                file_put_contents($filename, $_SESSION['pgp_key']);
                $success = "Registrierung erfolgreich! Willkommen, " . htmlspecialchars($username) . ".";
                session_destroy();
            }
        }
    }
}

function generate() {
    // erzeugen Passphrase
    $randomPass = bin2hex(random_bytes(16));
    $_SESSION['passphrase'] = $randomPass;

    $tmpPlain  = tempnam(sys_get_temp_dir(), 'plain_');
    $tmpOutput = tempnam(sys_get_temp_dir(), 'enc_');
    $tmpPgp    = tempnam(sys_get_temp_dir(), 'pgp_');

    file_put_contents($tmpPlain, $randomPass);
    file_put_contents($tmpPgp, $_SESSION['pgp_key']);

    $escapedKey   = escapeshellarg($tmpPgp);
    $escapedPlain = escapeshellarg($tmpPlain);
    $escapedOut   = escapeshellarg($tmpOutput);

    // Befehl ausführen und Rückgabewert prüfen
    $cmd = "gpg --yes --batch --armor --trust-model always --recipient-file $escapedKey --output $escapedOut --encrypt $escapedPlain 2>&1";
    exec($cmd, $outputLines, $returnVar);

    $encryptedMessage = '';
    if ($returnVar === 0 && file_exists($tmpOutput) && filesize($tmpOutput) > 0) {
        $encryptedMessage = trim(file_get_contents($tmpOutput));
        $_SESSION['pgp_message'] = $encryptedMessage;
        unset($_SESSION['pgp_error']);
    }

    // Aufräumen
    @unlink($tmpPlain);
    @unlink($tmpPgp);
    @unlink($tmpOutput);
}

// Show Form 1 (PGP Key input)
if ($step === 1):
?>
<form method="post" style="max-width: 600px; margin: auto; padding: 1em;">
	<h2><?= htmlspecialchars($lang_register['step1']) ?></h2>
	<?php if (!empty($error)): ?>
		<p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>
	<label for="pgp_key"><?= htmlspecialchars($lang_register['insert_pgp']) ?></label><br>
	<textarea name="pgp_key" rows="12" style="width: 100%;" required><?php echo isset($_POST['pgp_key']) ? htmlspecialchars($_POST['pgp_key']) : ''; ?></textarea><br><br>
    <input type="submit" value="<?= htmlspecialchars($lang_register['continue']) ?>" style="width: 100%; padding: 0.5em;">
</form>

<?php
// Show Form 2 (Verification)
elseif ($step === 2):
    $username = $_SESSION['username'];
?>
<form method="post" style="max-width: 600px; margin: auto; padding: 1em;">
	<h2><?= htmlspecialchars($lang_register['step2']) ?></h2>
	<?php if (!empty($error)): ?>
		<p style="color: red;"><?php echo htmlspecialchars($error); ?></p>
	<?php endif; ?>
	<label><?= htmlspecialchars($lang_register['user_name']) ?><strong><?php echo htmlspecialchars($username); ?></strong></label><br><br>
	<label for="encrypted"><?= htmlspecialchars($lang_register['enc_msg']) ?></label><br>
	<textarea readonly rows="10" style="width: 100%;"><?php echo htmlspecialchars($pgpMessage); ?></textarea><br><br>
	<label for="decrypted_pass"><?= htmlspecialchars($lang_register['token']) ?></label><br>
	<input type="text" name="decrypted_pass" style="width: 100%;" required><br><br>
	<div>
    <?php
	    $captcha->showCaptchaInline();
	?>
	</div>
	<input type="submit" value="<?= htmlspecialchars($lang_register['register']) ?>" style="width: 100%; padding: 0.5em;">
</form>

<?php
// Show success message
elseif (!empty($success)):
?>
<div style="max-width: 600px; margin: auto; padding: 1em;">
    <p style="color: green;"><?php echo htmlspecialchars($success); ?></p>
</div>
<?php endif; ?>