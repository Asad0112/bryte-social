<?php
function loadEnv($path)
{
    if (file_exists($path)) {
        $variables = parse_ini_file($path);
        foreach ($variables as $key => $value) {
            putenv("$key=$value");
        }
    } else {
        echo "Error: .env file not found at $path";
    }
}
?>
