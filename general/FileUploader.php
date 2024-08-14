<?php

class FileUploader
{
    /**
     * Generates a unique filename by combining a unique identifier with the original file's extension.
     * 
     * @param string $originalFilename The original filename to base the new filename on.
     * @return string A unique filename with the same extension as the original file.
     *
     * This method is useful for preventing filename conflicts when uploading files by ensuring 
     * that each file gets a unique name, reducing the risk of overwriting existing files.
     */
    public static function generateUniqueFilename(string $originalFilename): string
    {
        // Получаване на разширението на файла
        $extension = pathinfo($originalFilename, PATHINFO_EXTENSION);

        // Генериране на уникален идентификатор
        $uniqueId = uniqid('', true);

        // Създаване на новото име на файла, комбинирайки уникалния идентификатор и разширението
        $uniqueFilename = $uniqueId . '.' . $extension;

        return $uniqueFilename;
    }

    /**
     * Retrieves detailed information about a file, including its size, MIME type, extension, basename, 
     * directory, creation time, last modification time, and file permissions.
     * 
     * @param string $filePath The path to the file for which information is to be retrieved.
     * @return array An associative array containing detailed file information such as size, MIME type, 
     * extension, basename, directory, creation time, last modification time, and permissions.
     *
     * This method is useful for obtaining comprehensive metadata about a file that has been uploaded or 
     * stored, which can be valuable for processing, validation, or displaying detailed file properties.
     * 
     * @throws InvalidArgumentException if the file does not exist.
     */
    public static function getFileInfo(string $filePath): array
    {
        // Проверка дали файлът съществува
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: $filePath");
        }

        // Получаване на информация за файла
        $fileInfo = [
            'size' => filesize($filePath), // Размер на файла в байтове
            'size_formatted' => self::formatSize(filesize($filePath)), // Форматиран размер на файла
            'mime_type' => mime_content_type($filePath), // MIME тип на файла
            'extension' => pathinfo($filePath, PATHINFO_EXTENSION), // Разширение на файла
            'basename' => basename($filePath), // Име на файла без пътя
            'dirname' => dirname($filePath), // Директория на файла
            'creation_time' => filectime($filePath), // Дата на създаване
            'modification_time' => filemtime($filePath), // Дата на последна модификация
            'permissions' => substr(sprintf('%o', fileperms($filePath)), -4) // Права за достъп
        ];

        return $fileInfo;
    }

    // Помощен метод за форматиране на размера на файла
    public static function formatSize(int $size): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $unitIndex = 0;

        while ($size >= 1024 && $unitIndex < count($units) - 1) {
            $size /= 1024;
            $unitIndex++;
        }

        return round($size, 2) . ' ' . $units[$unitIndex];
    }

    /**
     * Validates the MIME type of a file to ensure it matches one of the allowed types.
     * 
     * @param string $filePath The path to the file to be validated.
     * @param array $allowedTypes An array of allowed MIME types (e.g., ['image/jpeg', 'image/png']).
     * @return bool True if the file's MIME type is in the allowed types, otherwise false.
     *
     * This method is useful for restricting file uploads to specific types by checking 
     * the MIME type of the file against a list of allowed types, helping to ensure 
     * that only files with valid and expected types are accepted.
     * 
     * @throws InvalidArgumentException if the file does not exist.
     */
    public static function validateFileType(string $filePath, array $allowedTypes): bool
    {
        // Проверка дали файлът съществува
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: $filePath");
        }

        // Получаване на MIME типа на файла
        $fileMimeType = mime_content_type($filePath);

        // Проверка дали MIME типа на файла е в списъка на разрешените типове
        return in_array($fileMimeType, $allowedTypes, true);
    }

    /**
     * Validates the size of a file to ensure it does not exceed the maximum allowed size.
     * 
     * @param string $filePath The path to the file to be validated.
     * @param int $maxSize The maximum allowed file size in bytes.
     * @return bool True if the file size is within the allowed limit, otherwise false.
     *
     * This method is useful for enforcing file size limits during file uploads by checking 
     * the size of the file against a specified maximum size, helping to prevent the upload 
     * of excessively large files.
     * 
     * @throws InvalidArgumentException if the file does not exist.
     */
    public static function validateFileSize(string $filePath, int $maxSize): bool
    {
        // Проверка дали файлът съществува
        if (!file_exists($filePath)) {
            throw new InvalidArgumentException("File does not exist: $filePath");
        }

        // Получаване на размера на файла в байтове
        $fileSize = filesize($filePath);

        // Проверка дали размерът на файла не надвишава максималния размер
        return $fileSize <= $maxSize;
    }

    /**
     * Handles the upload of a file, validates its type and size, and stores it in a directory structure 
     * organized by year, month, and day.
     * 
     * @param array $file The file information array from the $_FILES global variable.
     * @param string $uploadDir The base directory where files should be uploaded.
     * @param array $allowedTypes An array of allowed MIME types for the file (e.g., ['image/jpeg', 'image/png']).
     * @param int $maxSize The maximum allowed file size in bytes.
     * @return string The relative path to the uploaded file.
     *
     * This method validates the uploaded file's type and size, then organizes the file storage 
     * by creating a directory structure based on the current date (year/month/day). 
     * It also ensures that each uploaded file has a unique filename to prevent overwriting 
     * existing files. If the upload is successful, it returns the relative path to the uploaded file.
     * 
     * @throws RuntimeException if there is an error during file upload, directory creation, or file moving.
     * @throws InvalidArgumentException if the file type or size is invalid.
     */
    public static function uploadFile(array $file, string $uploadDir, array $allowedTypes, int $maxSize): string
    {
        // Check if file has any upload error
        if ($file['error'] !== UPLOAD_ERR_OK) {
            throw new RuntimeException("File upload error: " . $file['error']);
        }

        // Validate the file type
        if (!self::validateFileType($file['tmp_name'], $allowedTypes)) {
            throw new InvalidArgumentException("Invalid file type.");
        }

        // Validate the file size
        if (!self::validateFileSize($file['tmp_name'], $maxSize)) {
            throw new InvalidArgumentException("File size exceeds the maximum allowed limit.");
        }

        // Generate the directory structure based on the current date (year/month/day)
        $datePath = date('Y/m/d');
        $targetDir = rtrim($uploadDir, '/') . '/' . $datePath;

        // Create the directory if it doesn't exist
        if (!is_dir($targetDir)) {
            if (!mkdir($targetDir, 0755, true)) {
                throw new RuntimeException("Failed to create directories: $targetDir");
            }
        }

        // Generate a unique filename for the uploaded file
        $uniqueFilename = self::generateUniqueFilename($file['name']);

        // Set the full path for the uploaded file
        $targetFilePath = $targetDir . '/' . $uniqueFilename;

        // Move the uploaded file to its final destination
        if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
            throw new RuntimeException("Failed to move uploaded file to $targetFilePath");
        }

        // Return the relative path of the uploaded file
        return $datePath . '/' . $uniqueFilename;
    }
}