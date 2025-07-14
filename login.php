<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Only run if user is not logged in
if (isset($_SESSION['user_name'])) {
    header("Location: /index.php");
    exit();
} else {
    include 'header.php';

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
            $filename = $dataFolder . '/' . $username . '.txt';

            // Check if file exists
            if (file_exists($filename)) {
                $pgpKey = file_get_contents($filename);

                // Simulate a PGP-encrypted message
                $randomPass = bin2hex(random_bytes(16));
                file_put_contents("/tmp/pass.txt", $randomPass);
                $messageToEncrypt = $decryptedText;
                $pgpMessage = "-----BEGIN PGP MESSAGE-----\n\n" . base64_encode($messageToEncrypt) . "\n\n-----END PGP MESSAGE-----";

                $step = 2;
            } else {
                $error = 'User not found.';
            }
        } elseif (isset($_POST['decrypted'])) {
            $input = trim($_POST['decrypted']);
            if ($input === $decryptedText) {
                $_SESSION['user_name'] = 'AuthenticatedUser';
                echo '<meta http-equiv="refresh" content="0;URL=/index.php">';
                exit();
            } else {
                $step = 2;
                $pgpMessage = $_POST['pgp_message']; // preserve PGP message
                $error = 'Incorrect decryption.';
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
    <h3>Enter your username</h3>
    <input type="text" name="username" placeholder="Username" required>
    <input type="submit" value="Continue">
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</form>

<?php elseif ($step === 2): ?>
<!-- Form 2: Show PGP message and input field -->
<form method="post">
    <h3>Decrypt the message</h3>
    <label>Encrypted Message:</label>
    <textarea readonly rows="6"><?= htmlspecialchars($pgpMessage) ?></textarea>
    <input type="hidden" name="pgp_message" value="<?= htmlspecialchars($pgpMessage) ?>">
    <label>Your Decrypted Text:</label>
    <input type="text" name="decrypted" placeholder="Enter decrypted message" required>
    <input type="submit" value="Login">
    <?php if ($error): ?>
        <div class="error"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
</form>
</body>
</html>
<?php endif; ?>

<?php } // end of else ?>