<?php
header("Content-Security-Policy: default-src 'self'; style-src 'self' 'unsafe-inline';");
header('Referrer-Policy: no-referrer');
// Session starten, falls noch nicht gestartet
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include "include/file_manager.php";
include "lang/".get_config_value("Language")."/header.php";
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forum</title>
    <style>
        body {
            margin: 0;
            font-family: sans-serif;
        }
        header {
            background-color: #2c3e50;
            color: white;
            padding: 1rem 1rem 1rem 0.5rem;
        }
        nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        nav a {
            color: white;
            text-decoration: none;
            margin-left: 1rem;
        }
    </style>
</head>
<body>
	<div><strong>My forum</strong></div>
    <header>
        <nav>
            <div>
            	<a href="/index.php"><?= htmlspecialchars($lang_header['index']) ?></a>
                <?php if (isset($_SESSION['user_name'])): ?>
                    <a href="/logout.php"><?= htmlspecialchars($lang_header['log_out']) ?></a>
                <?php else: ?>
                    <a href="login.php"><?= htmlspecialchars($lang_header['log_in']) ?></a>
                    <a href="register.php"><?= htmlspecialchars($lang_header['register']) ?></a>
                <?php endif; ?>
            </div>
        </nav>
    </header>
    <?php if (isset($_SESSION['user_name'])): ?>
        <div><?= htmlspecialchars(str_replace("%s", $_SESSION['user_name'], ($lang_header['loggedInAs']))) ?></div>
    <?php endif; ?>