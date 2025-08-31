<?php
// install.php
include(dirname(__FILE__)."/../include/file_manager.php");
$installed = false;
$currentDir = __DIR__;
$parentDir = dirname($currentDir);
$dataFile = dirname($parentDir) . '/data/config.json';
if (is_file($dataFile)) {
	$installed = true;

    //Read value
    $language = get_config_value("Language") ?? 'English'; // Fallback: English

}
if (!$installed) {
	// Detect available languages from the 'lang' directory
    $langDir = dirname(__DIR__) . '/lang';
    $languages = [];

    if (is_dir($langDir)) {
        $items = scandir($langDir);
        foreach ($items as $item) {
            if ($item !== '.' && $item !== '..' && is_dir($langDir . '/' . $item)) {
                $languages[] = $item;
            }
        }
    }

    // Sort languages alphabetically
    sort($languages);

    // Default language
    $defaultLang = 'English';

    // Handle form submission
    $errors = [];

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $adminName = trim($_POST['admin_name'] ?? '');
        $pgpKey = trim($_POST['pgp_key'] ?? '');
        $language = $_POST['language'] ?? $defaultLang;

        // Simple validation
        if ($adminName === '') {
            $errors[] = 'Administrator name is required.';
        }
        if ($pgpKey === '') {
            $errors[] = 'PGP key is required.';
        }
        if (!in_array($language, $languages)) {
            $errors[] = 'Invalid language selected.';
        }

        if (empty($errors)) {
            $installed = true;

            global $data_path;
            $data = [
                'name' => $adminName,
                'user_type' => 'Administrator',
                'id' => get_last_id('users.json')
            ];
            mkdir($data_path . "/pgp_keys", 0777, true);
            append_to_json_file($data, 'users.json');
        
            file_put_contents($data_path . "/pgp_keys/" . $adminName . ".txt", $pgpKey);
        
            set_config_value("Language", $language);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Installation</title>
    <!-- Make the page responsive on mobile devices -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            font-family: sans-serif;
            background-color: #f8f8f8;
            padding: 20px;
        }
        .form-container {
            background: white;
            max-width: 500px;
            margin: auto;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
        }
        input, select, textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            box-sizing: border-box;
        }
        button {
            margin-top: 20px;
            width: 100%;
            padding: 10px;
            background-color: #007acc;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background-color: #005fa3;
        }
        .message {
            padding: 15px;
            background-color: #e0ffe0;
            border: 1px solid #00aa00;
            color: #006600;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .error {
            padding: 15px;
            background-color: #ffe0e0;
            border: 1px solid #aa0000;
            color: #660000;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Installation</h2>

    <?php if ($installed): ?>
        <?php
            $langFile = dirname(__DIR__) . "/lang/".$language."/install.php";
            if (file_exists($langFile)) {
            	include($langFile);
            }
        ?>
        <div class="message">
        	<?= $lang_install['success'] ?><br>
        	<?= $lang_install['index'] ?>
        </div>
    <?php else: ?>
        <!-- Show errors if any -->
        <?php if (!empty($errors)): ?>
            <div class="error">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Installation form -->
        <form action="" method="post">
            <!-- Administrator name input -->
            <label for="admin_name">Administrator Name</label>
            <input type="text" id="admin_name" name="admin_name" value="<?= htmlspecialchars($_POST['admin_name'] ?? '') ?>" required>

            <!-- PGP key input -->
            <label for="pgp_key">PGP Public Key</label>
            <textarea id="pgp_key" name="pgp_key" rows="6" required><?= htmlspecialchars($_POST['pgp_key'] ?? '') ?></textarea>

            <!-- Language selection -->
            <label for="language">Language</label>
            <select id="language" name="language">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= htmlspecialchars($lang) ?>" <?= ($lang === ($_POST['language'] ?? $defaultLang)) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Submit button -->
            <button type="submit">Install</button>
        </form>
    <?php endif; ?>
</div>

</body>
</html>
