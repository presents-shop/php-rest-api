<?php

function view($templateName, $data = array(), $statusCode = 200)
{
	http_response_code($statusCode);
	extract($data);
	require 'views/' . $templateName . '.php';
	exit;
}

function redirect($path, $statusCode = 200)
{
	http_response_code($statusCode);
	header("Location: $path");
	exit;
}

function generateRandomToken($length = 64)
{
	$randomBytes = random_bytes($length);
	$token = bin2hex($randomBytes);
	return $token;
}

set_exception_handler(function($exception) {
    echo "Uncaught exception: " , $exception->getMessage(), "\n";
});