<?php
// Стартиране на изходния буфер
ob_start();

// Настройка на заглавията за JSON отговор
header("Content-Type: application/json");

// Функция за обработка на грешки
function handleError($errno, $errstr, $errfile, $errline)
{
    // Изчистване на изходния буфер
    ob_end_clean();

    // Задаване на статус код 500
    http_response_code(500);

    // Връщане на JSON отговор с информация за грешката
    echo json_encode([
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ]);
    exit();
}

// Функция за обработка на изключения
function handleException($exception)
{
    // Изчистване на изходния буфер
    ob_end_clean();

    // Задаване на статус код 500
    http_response_code(500);

    // Връщане на JSON отговор с информация за изключението
    echo json_encode([
        'message' => $exception->getMessage(),
        'file' => $exception->getFile(),
        'line' => $exception->getLine()
    ]);
    exit();
}

// Регистриране на функциите за обработка
set_error_handler("handleError");
set_exception_handler("handleException");

// Спиране на изходния буфер и изпращане на изхода
ob_end_flush();