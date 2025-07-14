<?php
//Absolute path to the current directory (Forumsoftware/)
$currentDir = __DIR__;

//Path to the parent directory
$parentDir = dirname($currentDir);

//Path to the expected "config.json" file in the parent directory
$dataFile = $parentDir . '/data/config.json';

//Check if the file "config.json" exists
if (!is_file($dataFile)) {
    //HTML output with meta tag and note
    echo <<<HTML
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forumix</title>
    <style>
        body {
            font-family: sans-serif;
            padding: 2em;
            background-color: #f8f8f8;
            color: #222;
        }
        a {
            color: #0066cc;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <p>No file named <strong>config.json</strong> was found. The forum software might still need to be 
    <a href="admin/install.php">installed</a>.</p>
</body>
</html>
HTML;
    exit;
}
else {
	include 'header.php';
	?>
	</body>
    </html>
    <?php
}
?>