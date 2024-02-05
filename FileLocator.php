<?php
// Directory ka path
$directory = __DIR__ . '/src/config/';

// Directory ke andar sabhi PHP files ko select karein
$files = glob($directory . '*.php');

// Check karein ki koi file hai ya nahi
if (!empty($files)) {
    // Sabhi files ko include karein
    foreach ($files as $file) {
        if (file_exists($file)) {
            include_once $file;
        }
    }
}