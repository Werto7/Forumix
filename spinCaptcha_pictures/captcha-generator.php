<?php

$baseDir     = __DIR__;
$rotations   = [10, 80, 150, 220, 290];
$size        = 300;
$radius      = 150;

//Folder paths
$originalsDir = "$baseDir/originals";
$rotatedDir   = "$baseDir/rotated";
$tempDir      = "$baseDir/temp"; //Will be deleted at the end

//Create folder
@mkdir($originalsDir, 0777, true);
@mkdir($rotatedDir, 0777, true);
@mkdir($tempDir, 0777, true);

if ($argc > 1 && $argv[1] === 'repair') {
    echo "🛠 Repair mode activated...\n";

    $answersPath = "$baseDir/answers.json";
    $answers = file_exists($answersPath) ? json_decode(file_get_contents($answersPath), true) : [];
    if (!is_array($answers)) $answers = [];

    //Auxiliary function
    function removeFileIfExists($path) {
        if (file_exists($path)) {
            unlink($path);
            echo "🗑 File deleted: $path\n";
        }
    }

    //Capture all existing text files
    $originalTxts = array_map('basename', glob("$originalsDir/*.txt"));
    $rotatedTxts  = array_map('basename', glob("$rotatedDir/*.txt"));

    $answersNames = array_column($answers, 'name');

    //Step 1: Clean up answers.json
    $newAnswers = [];
    foreach ($answers as $entry) {
        $name = $entry['name'];

        $existsOriginal = in_array($name, $originalTxts);
        $existsRotated  = in_array($name, $rotatedTxts);

        if ($existsOriginal || $existsRotated) {
            $newAnswers[] = $entry; //Keep
        } else {
            echo "❌ No valid file entry for $name → Remove from answers.json\n";
            //If necessary, delete the file from other folders (if it is there)
            removeFileIfExists("$originalsDir/$name");
            removeFileIfExists("$rotatedDir/$name");
        }
    }

    //Step 2: Check originals/
    foreach ($originalTxts as $file) {
        if (!in_array($file, $rotatedTxts) || !in_array($file, $answersNames)) {
            echo "❌ Inconsistency in $file → Remove from originals/ + rotated/ + answers.json\n";
            removeFileIfExists("$originalsDir/$file");
            removeFileIfExists("$rotatedDir/$file");
            $newAnswers = array_filter($newAnswers, fn($a) => $a['name'] !== $file);
        }
    }

    //Step 3: Check rotated/
    foreach ($rotatedTxts as $file) {
        if (!in_array($file, $originalTxts) || !in_array($file, $answersNames)) {
            echo "❌ Inconsistency in $file → Remove from rotated/ + originals/ + answers.json\n";
            removeFileIfExists("$rotatedDir/$file");
            removeFileIfExists("$originalsDir/$file");
            $newAnswers = array_filter($newAnswers, fn($a) => $a['name'] !== $file);
        }
    }

    //Update answers.json
    file_put_contents($answersPath, json_encode(array_values($newAnswers), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    echo "✅ Repair completed.\n";
    exit;
}

//Prepare or read answers.json
$answersPath = "$baseDir/answers.json";
if (file_exists($answersPath)) {
    $answers = json_decode(file_get_contents($answersPath), true);
    if (!is_array($answers)) $answers = [];
} else {
    $answers = [];
}

$imageFiles = glob("$baseDir/*.{jpg,jpeg,png}", GLOB_BRACE);

foreach ($imageFiles as $filePath) {
    $filename = basename($filePath);
    $base     = pathinfo($filename, PATHINFO_FILENAME);
    echo "🔄 Process: $filename\n";

    //Step 1: scale + center to 300x300
    $resized = "$tempDir/{$base}_resized.png";
    exec("magick \"$filePath\" -resize {$size}x{$size}^ -gravity center -extent {$size}x{$size} \"$resized\"");

    //Step 2: Create a round mask
    $mask = "$tempDir/{$base}_mask.png";
    exec("magick -size {$size}x{$size} xc:none -fill white -draw \"circle {$radius},{$radius} {$radius},0\" \"$mask\"");

    //Step 3: Apply mask
    $rounded = "$tempDir/{$base}_round.png";
    exec("magick \"$resized\" \"$mask\" -alpha off -compose CopyOpacity -composite \"$rounded\"");

    //Step 4: Random rotation
    $angle = $rotations[array_rand($rotations)];
    echo "↪️  Rotated by {$angle}°\n";

    //Step 5: Save rotated (rotated)
    $rotatedWebp = "$rotatedDir/{$base}.webp";
    exec("magick \"$rounded\" -background none -distort SRT $angle \"$rotatedWebp\"");

    //Step 6: Save unrotated (original)
    $originalWebp = "$originalsDir/{$base}.webp";
    exec("magick \"$rounded\" -define webp:lossless=true \"$originalWebp\"");

    //Step 7: Save Base64
    file_put_contents("$rotatedDir/{$base}.txt", base64_encode(file_get_contents($rotatedWebp)));
    file_put_contents("$originalsDir/{$base}.txt", base64_encode(file_get_contents($originalWebp)));
    unlink($rotatedWebp);
    unlink($originalWebp);

    //Step 8: JSON entry – update existing or add new
    $entryName = $base . '.txt';
    $updated = false;

    foreach ($answers as &$entry) {
        if (isset($entry['name']) && $entry['name'] === $entryName) {
            $entry['rotation'] = $angle;
            $updated = true;
            break;
        }
    }
    unset($entry); //Remove reference

    if (!$updated) {
        $answers[] = [
            'name' => $entryName,
            'rotation' => $angle
        ];
    }
}

//Step 9: Write answers.json
file_put_contents("$baseDir/answers.json", json_encode($answers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

//Step 10: Delete source images
foreach ($imageFiles as $filePath) {
    unlink($filePath);
}

//Step 11: Delete the temp folder
array_map('unlink', glob("$tempDir/*"));
@rmdir($tempDir);

echo "✅ Done! Files saved in:\n";
echo "📁 $originalsDir (Originals)\n";
echo "📁 $rotatedDir (Rotated)\n";
echo "📝 $baseDir/answers.json\n";
