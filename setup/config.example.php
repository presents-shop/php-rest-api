<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

date_default_timezone_set('Europe/Sofia');

// general settings
define("WEBSITE_LINK", "");
define("WEBSITE_CHARSET", "UTF-8");

// mail configuration
define("MAIL_HOST", "mail.example.com");
define("MAIL_USERNAME", "office@example.com");
define("MAIL_PASSWORD", "office@example.com");
define("MAIL_PORT", 587);
define("MAIL_SENDER", "office@example.com");

// e-mail settings
define("WEBSITE_EMAIL", "office@example.com");
define("WEBSITE_EMAIL_PASSWORD", "your_mail_password");
define("WEBSITE_EMAIL_HOST", "mail@example.com");

// JWT settings
define("JSON_WEB_TOKEN_EXPIRLY_IN_SECONDS", 3600); // 1 hour
define("JSON_WEB_TOKEN_SECRET_KEY", "...");
define("JSON_WEB_TOKEN_SALT", "...");

// database settings
define("HOST", "localhost");
define("DATABASE_USER", "root");
define("DATABASE_PASSWORD", "");
define("DATABASE_NAME", "");
define("CHARSET", "utf8");

$database = new Database(HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME, CHARSET);