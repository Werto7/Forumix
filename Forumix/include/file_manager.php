<?php
$data_path = dirname(__DIR__) . '/../data/';

function append_to_json_file(array $data, string $filename, ?callable $callback = null): bool {
    global $data_path;

    $filepath = rtrim($data_path, '/') . '/' . $filename;

    //If file does not exist, start with empty array
    $entries = [];

    if (file_exists($filepath)) {
        $json = file_get_contents($filepath);
        $entries = json_decode($json, true);

        //If file is empty or corrupted, start new array
        if (!is_array($entries)) {
            $entries = [];
        }
    }

    //Apply callback if available
    if ($callback !== null) {
        $data = $callback($data);
    }

    //Attach new record
    $entries[] = $data;

    //Save as JSON with pretty print
    $json_encoded = json_encode($entries, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    return file_put_contents($filepath, $json_encoded) !== false;
}

function get_last_id(string $filename): int {
    global $data_path;

    $filepath = rtrim($data_path, '/') . '/' . $filename;

    // Return 1 if file doesn't exist
    if (!file_exists($filepath)) {
        return 1;
    }

    // Read and decode JSON
    $json = file_get_contents($filepath);
    $entries = json_decode($json, true);

    // Return 1 if file is empty or malformed
    if (!is_array($entries) || count($entries) === 0) {
        return 1;
    }

    // Get the last entry
    $last = end($entries);

    // Return the ID if it exists and is numeric
    if (isset($last['id']) && is_numeric($last['id'])) {
        return (int) $last['id'];
    }

    // Default fallback
    return 1;
}

function set_config_value(string $key, $value): bool {
    global $data_path;

    $filepath = rtrim($data_path, '/') . '/config.json';

    // Create directory if it doesn't exist
    $dir = dirname($filepath);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }

    // Load existing config data
    $config = [];
    if (file_exists($filepath)) {
        $json = file_get_contents($filepath);
        $decoded = json_decode($json, true);
        if (is_array($decoded)) {
            $config = $decoded;
        }
    }

    // Set or update the key
    $config[$key] = $value;

    // Encode to JSON and save
    $json_encoded = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    return file_put_contents($filepath, $json_encoded) !== false;
}

function get_config_value(string $key, $default = null) {
    global $data_path;

    $filepath = rtrim($data_path, '/') . '/config.json';

    // If the config file doesn't exist, return the default value
    if (!file_exists($filepath)) {
        return $default;
    }

    // Read and decode the config file
    $json = file_get_contents($filepath);
    $config = json_decode($json, true);

    // If decoding fails or result is not an array, return default
    if (!is_array($config)) {
        return $default;
    }

    // Return the value if it exists, otherwise the default
    return array_key_exists($key, $config) ? $config[$key] : $default;
}
?>
