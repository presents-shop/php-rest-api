<?php

class Sanitizer
{
    /**
     * Sanitizes a given string by removing unnecessary whitespace, stripping HTML tags, 
     * converting special characters to HTML entities, and removing potentially unsafe characters.
     * 
     * @param string $value The string value to be sanitized.
     * @return string The sanitized string.
     *
     * This method is useful for cleaning user input before storing it in a database or displaying it
     * on a webpage, helping to prevent XSS (Cross-Site Scripting) attacks and other potential security issues.
     */
    public static function sanitizeString(string $value): string
    {
        // Removes extra spaces at the beginning and end of the string
        $value = trim($value);

        // Removes all HTML tags
        $value = strip_tags($value);

        // Converts special characters to HTML entities
        $value = htmlspecialchars($value, ENT_QUOTES, 'UTF-8');

        // Removes possible dangerous characters such as NULL bytes
        $value = filter_var($value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH);

        return $value;
    }

    /**
     * Sanitizes a given email address by removing unnecessary whitespace and 
     * filtering out invalid characters.
     * 
     * @param string $value The email address to be sanitized.
     * @return string The sanitized email address.
     *
     * This method is useful for cleaning user-provided email input to ensure 
     * it is in a valid and safe format before storing or using it, thereby 
     * reducing the risk of invalid data entry and enhancing security.
     */
    public static function sanitizeEmail(string $value): string
    {
        // Removes extra spaces at the beginning and end of the string
        $value = trim($value);

        // Removes all unwanted characters from the email
        $value = filter_var($value, FILTER_SANITIZE_EMAIL);

        return $value;
    }

    /**
     * Sanitizes a given URL by removing unnecessary whitespace and filtering out
     * invalid characters to ensure the URL is in a safe and valid format.
     * 
     * @param string $value The URL to be sanitized.
     * @return string The sanitized URL.
     *
     * This method is useful for cleaning user-provided URLs to ensure they are 
     * properly formatted and secure before storing or using them, reducing the risk 
     * of security vulnerabilities such as injection attacks.
     */
    public static function sanitizeUrl(string $value): string
    {
        // Removes extra spaces at the beginning and end of the string
        $value = trim($value);

        // Strips all unwanted characters from the URL
        $value = filter_var($value, FILTER_SANITIZE_URL);

        return $value;
    }

    /**
     * Sanitizes a given value by removing all non-numeric characters and converting 
     * it to an integer.
     * 
     * @param string $value The value to be sanitized.
     * @return int The sanitized integer.
     *
     * This method is useful for cleaning user-provided input that is expected to be an 
     * integer, ensuring that only valid numeric data is processed and stored.
     */
    public static function sanitizeInt(string $value): int
    {
        // Removes all unwanted characters and converts the value to an integer
        return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
    }

    /**
     * Sanitizes a given value by removing all non-numeric characters except for the 
     * decimal point, and converts it to a float.
     * 
     * @param string $value The value to be sanitized.
     * @return float The sanitized float.
     *
     * This method is useful for cleaning user-provided input that is expected to be a 
     * floating-point number, ensuring that only valid numeric data is processed and stored.
     */
    public static function sanitizeFloat(string $value): float
    {
        // Strips all unwanted characters and converts the value to a floating point number
        return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
    }

    /**
     * Sanitizes a given string by converting special characters to their HTML entities,
     * thereby preventing the injection of malicious HTML or JavaScript code.
     * 
     * @param string $value The string to be sanitized.
     * @return string The sanitized string.
     *
     * This method is useful for cleaning user input that will be displayed in a web 
     * context, helping to prevent XSS (Cross-Site Scripting) attacks by escaping HTML.
     */
    public static function sanitizeHtml(string $value): string
    {
        // Converts special characters to HTML entities
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Sanitizes a given string to be safely used in an SQL query by escaping special 
     * characters that could be used in SQL injection attacks.
     * 
     * @param string $value The string to be sanitized.
     * @param object $connection The database connection used for escaping the string.
     * @return string The sanitized string.
     *
     * This method is useful for cleaning user input before including it in SQL queries,
     * helping to prevent SQL injection by properly escaping dangerous characters.
     */
    public static function sanitizeSql(string $value, $connection): string
    {
        // Escapes special characters for SQL queries using a database-specific connector
        return mysqli_real_escape_string($connection, $value);
    }

    /**
     * Sanitizes a given filename by removing any characters that are not alphanumeric, 
     * hyphens, underscores, or periods.
     * 
     * @param string $filename The filename to be sanitized.
     * @return string The sanitized filename.
     *
     * This method is useful for ensuring that filenames are valid and safe for storage 
     * and retrieval, preventing issues related to invalid characters or potential 
     * security vulnerabilities.
     */
    public static function sanitizeFilename(string $filename): string
    {
        // Removes all dangerous characters from the filename
        return preg_replace('/[^a-zA-Z0-9-_\.]/', '', $filename);
    }
}