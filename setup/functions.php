<?php

function view($templateName, $data = array(), $statusCode = 200)
{
	http_response_code($statusCode);
	extract($data);
	require 'views/' . $templateName . '.php';
}

function redirect($path, $statusCode = 200)
{
	http_response_code($statusCode);
	header("Location: $path");
	exit;
}

function getJSONData()
{
	$inputData = json_decode(file_get_contents("php://input"));
	return $inputData;
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

function generateSecurityCode() {
	$code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= rand(0, 9);
    }
    return $code;
}