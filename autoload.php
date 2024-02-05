<?php

spl_autoload_register(function ($className) {
    // Define your base namespace and path
    $baseNamespace = 'NetBhaari\\NetLib\\';
    $basePath = 'src/';

    // Check if the class belongs to the specified namespace
    if (strpos($className, $baseNamespace) === 0) {
        // Remove the base namespace from the class name
        $relativeClass = substr($className, strlen($baseNamespace));

        // Convert namespace separators to directory separators
        $filePath = $basePath . str_replace('\\', '/', $relativeClass) . '.php';

        // Include the file if it exists
        if (file_exists($filePath)) {
            require $filePath;
        }
    }
});