<?php

require "vendor/autoload.php";

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelGenerator
{
    public static function generateFromJson($jsonData, $filePath)
    {
        $dataArray = json_decode($jsonData, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new InvalidArgumentException("Invalid JSON data provided");
        }

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $columnIndex = 1;
        foreach (array_keys($dataArray[0]) as $columnName) {
            $cellCoordinate = self::columnLetter($columnIndex) . "1";
            $sheet->setCellValue($cellCoordinate, $columnName);
            $columnIndex++;
        }

        $rowIndex = 2;
        foreach ($dataArray as $row) {
            $columnIndex = 1;
            foreach ($row as $cellValue) {
                $cellCoordinate = self::columnLetter($columnIndex) . $rowIndex;
                $sheet->setCellValue($cellCoordinate, $cellValue);
                $columnIndex++;
            }
            $rowIndex++;
        }

        $writer = new Xlsx($spreadsheet);
        $writer->save($filePath);
    }

    private static function columnLetter($columnIndex)
    {
        $letter = "";
        while ($columnIndex > 0) {
            $remainder = ($columnIndex - 1) % 26;
            $letter = chr(65 + $remainder) . $letter;
            $columnIndex = intval(($columnIndex - 1) / 26);
        }
        return $letter;
    }

    public static function downloadFile($filePath)
    {
        if (file_exists($filePath)) {
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.basename($filePath).'"');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            echo "Файлът не съществува.";
        }
    }
}
