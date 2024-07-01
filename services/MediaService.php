<?php

class MediaService
{
    private $imageTypes = [
        "image/jpeg",
        "image/png",
        "image/gif",
        "image/webp",
        "image/bmp",
        "image/avif",
    ];

    public function getMediaType($file)
    {
        if (!isset($_FILES[$file]) || $_FILES[$file]["error"] !== UPLOAD_ERR_OK) {
            throw new Exception("Файлът не съществува или е възникнала грешка при качването му.");
        }

        $tmpFilePath = $_FILES[$file]["tmp_name"];

        $fileInfo = new finfo(FILEINFO_MIME_TYPE);
        $mimeType = $fileInfo->file($tmpFilePath);

        return $mimeType;
    }

    public function isImage($file)
    {
        try {
            $mimeType = $this->getMediaType($file);
            return in_array($mimeType, $this->imageTypes);
        } catch (Exception $e) {
            return false;
        }
    }

    public function compressImage($file, $destination, $quality)
    {
        if (!$this->isImage($file)) {
            throw new Exception("Каченият файл не е изображение.");
        }

        $tmpFilePath = $_FILES[$file]["tmp_name"];
        $mimeType = $this->getMediaType($file);

        switch ($mimeType) {
            case "image/jpeg":
                $image = imagecreatefromjpeg($tmpFilePath);
                imagejpeg($image, $destination, $quality);
                break;

            case "image/png":
                $image = imagecreatefrompng($tmpFilePath);
                imagepng($image, $destination, (int) ($quality / 10));
                break;

            case "image/gif":
                $image = imagecreatefromgif($tmpFilePath);
                imagegif($image, $destination);
                break;

            case "image/webp":
                $image = imagecreatefromwebp($tmpFilePath);
                imagewebp($image, $destination, $quality);
                break;

            case "image/avif":
                $image = imagecreatefromavif($tmpFilePath);
                imageavif($image, $destination, $quality);
                break;

            default:
                throw new Exception("Неподдържан формат на изображението.");
        }

        imagedestroy($image);
    }

    public function uploadImage($file, $destinationFolder = "uploads")
    {
        if (!$this->isImage($file)) {
            throw new Exception("Каченият файл не е изображение.");
        }

        // Генериране на текущата дата
        $currentDate = new DateTime();
        $year = $currentDate->format('Y');
        $month = $currentDate->format('m');
        $day = $currentDate->format('d');

        // Създаване на папките за година, месец и ден
        $destinationFolder = rtrim($destinationFolder, "/") . "/$year/$month/$day";
        if (!is_dir($destinationFolder)) {
            if (!mkdir($destinationFolder, 0755, true)) {
                throw new Exception("Неуспешно създаване на целева папка.");
            }
        }

        $fileName = basename($_FILES[$file]["name"]);
        $destinationPath = $destinationFolder . "/" . $fileName;

        $tmpFilePath = $_FILES[$file]["tmp_name"];
        $dimensions = getimagesize($tmpFilePath);

        if (!move_uploaded_file($_FILES[$file]["tmp_name"], $destinationPath)) {
            throw new Exception("Неуспешно преместване на качения файл.");
        }

        $image = self::create([
            "width" => $dimensions[0],
            "height" => $dimensions[1],
            "path" => $destinationPath
        ]);

        return $image["id"];
    }

    public function create($data)
    {
        global $database;

        try {
            $database->insert("media", $data);
            return self::findOne($database->lastInsertedId());
        } catch (Exception $ex) {
            echo "Insert new image error: " . $ex->getMessage();
        }
    }

    public static function findOne($value, $column = "id", $fields = "*")
    {
        global $database;

        $sql = "SELECT $fields FROM media WHERE $column = :$column";
        $params = [":$column" => $value];

        try {
            $image = $database->getOne($sql, $params);
            return $image;
        } catch (Exception $ex) {
            echo "Fetch single image error: " . $ex->getMessage();
        }
    }

    public static function update($id, $data)
    {
        global $database;

        $media = self::findOne($id);

        if (!$media) {
            Response::badRequest(["invalid_id" => "Този файл не съществува"])->send();
        }

        $newMedia = [
            "width" => $data->width,
            "height" => $data->height,
            "title" => $data->title,
            "decoding" => $data->decoding,
        ];

        try {
            $database->update("media", $newMedia, "id = $id");
            return self::findOne($id);
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public static function findAll($offset, $limit)
    {
        global $database;

        $sql = "SELECT * FROM media";

        if ($limit) {
            $sql .= " LIMIT $limit";
        }
        
        if ($offset) {
            $sql .= " OFFSET $offset";
        }

        try {
            $mediaFiles = $database->getAll($sql, []);

            return $mediaFiles;
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
