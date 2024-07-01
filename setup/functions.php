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
	Response::badRequest($exception->getMessage(), $exception->getTrace())->send();
});

function generateSecurityCode() {
	$code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= rand(0, 9);
    }
    return $code;
}
