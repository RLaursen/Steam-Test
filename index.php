<?php

/**
 * Fluent API for testing APIs
 * 
 * Largely based on pest/jest/mocha and supertest
 */

const REQUIRED_VERSION = '8.0.0';
if (version_compare(phpversion() , REQUIRED_VERSION, '<')) {
    warning('PHP Version must be at least ' . REQUIRED_VERSION . ' . Current version is: ' . phpversion());
    exit(1);
}

require_once(__DIR__ . '/lib/request.php');
require_once(__DIR__ . '/lib/test.php');
require_once(__DIR__ . '/lib/expect.php');

?>
