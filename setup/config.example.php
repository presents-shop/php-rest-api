<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// general settings
setlocale(LC_MONETARY,"bg");
define("WEBSITE_LANG", "bg");
define("WEBSITE_LINK", "http://presents-shop.test");
define("WEBSITE_LANGUAGE_EXTENSION", "bg");
define("WEBSITE_CHARSET", "UTF-8");

// mail configuration
define("MAIL_HOST", "mail.example.com");
define("MAIL_USERNAME", "office@example.com");
define("MAIL_PASSWORD", "example-password");
define("MAIL_PORT", 587);
define("MAIL_SENDER", "office@example.com");

// e-mail settings
define("WEBSITE_EMAIL", "office@example.com");
define("WEBSITE_EMAIL_PASSWORD", "example-password");
define("WEBSITE_EMAIL_HOST", "mail.example.com");

// JWT settings
define("JSON_WEB_TOKEN_EXPIRLY_IN_SECONDS", 3600);
define("JWT_ALGORITHM", "");
define("JWT_SECRET_KEY", "");

// database settings
define("HOST", "localhost");
define("DATABASE_USER", "root");
define("DATABASE_PASSWORD", "");
define("DATABASE_NAME", "presents-shop");
define("CHARSET", "utf8");

$database = new Database(HOST, DATABASE_USER, DATABASE_PASSWORD, DATABASE_NAME, CHARSET);

// openssl genpkey -algorithm RSA -out private.key -pkeyopt rsa_keygen_bits:2048
// openssl rsa -pubout -in private.key -out public.key
