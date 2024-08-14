<?php

class ValidationResult
{
    public bool $success;
    public string $message;

    public function __construct(bool $success, string $message)
    {
        $this->success = $success;
        $this->message = $message;
    }
}

class Validator
{
    /**
     * Checks if a field is empty.
     * 
     * @param string $field The name of the field to validate.
     * @param mixed $value The value of the field being validated.
     * @return ValidationResult An object with the result of the validation: whether it was successful and an error message, if any.
     */
    public static function validateRequired(string $field, mixed $value): ValidationResult
    {
        if (empty($value) && $value !== "0") {
            return new ValidationResult(false, "This field {$field} is required.");
        }

        return new ValidationResult(true, "");
    }

    /**
     * Checks if a field contains a valid email address.
     * 
     * @param string $field The name of the field to validate.
     * @param mixed $value The value of the field being validated.
     * @return ValidationResult An object with the result of the validation: whether it was successful and an error message, if any.
     */
    public static function validateEmail(string $field, mixed $value): ValidationResult
    {
        if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
            return new ValidationResult(false, "The {$field} field must contain a valid email address.");
        }

        return new ValidationResult(true, "");
    }

    /**
     * Проверява дали стойността на дадено поле е в зададен диапазон.
     * 
     * @param string $field The name of the field to validate.
     * @param mixed $value The value of the field being validated.
     * @param int $min Minimum range value.
     * @param int $max Maximum range value.
     * @return ValidationResult An object with the result of the validation: whether it was successful and an error message, if any.
     */
    public static function validateRange(string $field, mixed $value, int $min, int $max): ValidationResult
    {
        // Check if the value is a number and is in the specified range.
        if (!is_numeric($value) || $value < $min || $value > $max) {
            return new ValidationResult(
                false,
                "The value of the {$field} field must be between {$min} and {$max}."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Checks whether the length of a field's value is within a specified range.
     * 
     * @param string $field The name of the field to validate.
     * @param mixed $value The value of the field being validated. Must be a string.
     * @param int $minLength Minimum value length.
     * @param int $maxLength Maximum length of the value.
     * @return ValidationResult An object with the result of the validation: whether it was successful and an error message, if any.
     */
    public static function validateLength(string $field, mixed $value, int $minLength, int $maxLength): ValidationResult
    {
        // Checks if the value is a string and if its length is in the specified range.
        if (!is_string($value) || strlen($value) < $minLength || strlen($value) > $maxLength) {
            return new ValidationResult(
                false,
                "The length of the {$field} field must be between {$minLength} and {$maxLength} characters."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field matches the specified regular expression pattern.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated. It should be a string.
     * @param string $pattern The regular expression pattern that the value should match.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validatePattern(string $field, mixed $value, string $pattern): ValidationResult
    {
        // Проверка дали стойността е стринг и дали съвпада с зададения регулярен израз.
        if (!is_string($value) || !preg_match($pattern, $value)) {
            return new ValidationResult(
                false,
                "The value of the {$field} field does not match the expected format."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is numeric.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated. It should be a number or a numeric string.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateNumeric(string $field, mixed $value): ValidationResult
    {
        if (!is_numeric($value)) {
            return new ValidationResult(
                false,
                "The value of the field {$field} must be numeric."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is a valid date.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated. It should be a string representing a date.
     * @param string $format The expected date format (e.g., 'Y-m-d').
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateDate(string $field, mixed $value, string $format): ValidationResult
    {
        $date = \DateTime::createFromFormat($format, $value);
        if (!$date || $date->format($format) !== $value) {
            return new ValidationResult(
                false,
                "The value of the field {$field} is not a valid date in the format {$format}."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of one field matches the value of another field.
     * 
     * @param string $field1 The name of the first field being validated.
     * @param mixed $value1 The value of the first field being validated.
     * @param string $field2 The name of the second field being validated.
     * @param mixed $value2 The value of the second field being validated.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateMatch(string $field1, mixed $value1, string $field2, mixed $value2): ValidationResult
    {
        if ($value1 !== $value2) {
            return new ValidationResult(
                false,
                "The values of the fields {$field1} and {$field2} do not match."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the given password meets specific complexity requirements.
     * 
     * @param string $field The name of the field being validated.
     * @param string $password The password value to be validated.
     * @param int $minLength Minimum required length of the password.
     * @param bool $requireUppercase Whether the password must contain at least one uppercase letter.
     * @param bool $requireLowercase Whether the password must contain at least one lowercase letter.
     * @param bool $requireDigit Whether the password must contain at least one digit.
     * @param bool $requireSpecial Whether the password must contain at least one special character (e.g., !@#$%^&*).
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validatePassword(
        string $field,
        string $password,
        int $minLength = 8,
        bool $requireUppercase = true,
        bool $requireLowercase = true,
        bool $requireDigit = true,
        bool $requireSpecial = true
    ): ValidationResult {
        // Check minimum length
        if (strlen($password) < $minLength) {
            return new ValidationResult(
                false,
                "The {$field} must be at least {$minLength} characters long."
            );
        }

        // Check for uppercase letter
        if ($requireUppercase && !preg_match('/[A-Z]/', $password)) {
            return new ValidationResult(
                false,
                "The {$field} must contain at least one uppercase letter."
            );
        }

        // Check for lowercase letter
        if ($requireLowercase && !preg_match('/[a-z]/', $password)) {
            return new ValidationResult(
                false,
                "The {$field} must contain at least one lowercase letter."
            );
        }

        // Check for digit
        if ($requireDigit && !preg_match('/\d/', $password)) {
            return new ValidationResult(
                false,
                "The {$field} must contain at least one digit."
            );
        }

        // Check for special character
        if ($requireSpecial && !preg_match('/[\W_]/', $password)) {
            return new ValidationResult(
                false,
                "The {$field} must contain at least one special character (e.g., !@#$%^&*)."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is a valid JSON string.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateJson(string $field, mixed $value): ValidationResult
    {
        json_decode($value);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return new ValidationResult(
                false,
                "The value of the field {$field} must be a valid JSON string."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the uploaded file has one of the allowed extensions.
     * 
     * @param string $field The name of the field being validated.
     * @param string $fileName The name of the file being validated.
     * @param array $allowedExtensions An array of allowed file extensions (e.g., ['jpg', 'png', 'pdf']).
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateFileExtension(string $field, string $fileName, array $allowedExtensions): ValidationResult
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!in_array(strtolower($extension), $allowedExtensions)) {
            return new ValidationResult(
                false,
                "The file in the field {$field} must have one of the following extensions: " . implode(", ", $allowedExtensions)
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether a given date is before another specified date.
     * 
     * @param string $field The name of the field being validated.
     * @param string $value The date value being validated.
     * @param string $comparisonDate The date to compare against.
     * @param string $format The date format (e.g., 'Y-m-d').
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateDateBefore(string $field, string $value, string $comparisonDate, string $format = 'Y-m-d'): ValidationResult
    {
        $date = \DateTime::createFromFormat($format, $value);
        $compareDate = \DateTime::createFromFormat($format, $comparisonDate);

        if ($date >= $compareDate) {
            return new ValidationResult(
                false,
                "The date in the field {$field} must be before {$comparisonDate}."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is a boolean.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateBoolean(string $field, mixed $value): ValidationResult
    {
        if (!is_bool($value)) {
            return new ValidationResult(
                false,
                "The value of the field {$field} must be a boolean."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is a valid URL.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateUrl(string $field, mixed $value): ValidationResult
    {
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            return new ValidationResult(
                false,
                "The value of the field {$field} must be a valid URL."
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is one of the allowed values in an array.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @param array $allowedValues An array of allowed values.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateInArray(string $field, mixed $value, array $allowedValues): ValidationResult
    {
        if (!in_array($value, $allowedValues, true)) {
            return new ValidationResult(
                false,
                "The value of the field {$field} must be one of the following: " . implode(", ", $allowedValues)
            );
        }

        return new ValidationResult(true, "");
    }

    /**
     * Validates whether the value of a given field is a valid IP address.
     * 
     * @param string $field The name of the field being validated.
     * @param mixed $value The value of the field being validated.
     * @return ValidationResult An object containing the validation result: whether it was successful and an error message if applicable.
     */
    public static function validateIpAddress(string $field, mixed $value): ValidationResult
    {
        if (!filter_var($value, FILTER_VALIDATE_IP)) {
            return new ValidationResult(
                false,
                "The value of the field {$field} must be a valid IP address."
            );
        }

        return new ValidationResult(true, "");
    }
}