<?php

namespace App\Http\Controllers;

use App\Models\CsvFile;
use Illuminate\Http\Request;
use League\Csv\Writer;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class CSVProcessorController extends Controller
{
    public function showForm()
    {
        $csvFiles = CsvFile::all();
        return inertia("CSV/Upload", [
            'csvFiles' => $csvFiles,
            'success' => session('success'),
            'zipfile' => session('zipfile'),
        ]);
    }



    public function processCSV(Request $request)
    {
        $request->validate([
            'csv_files.*' => 'required|file|mimes:csv,txt',
        ]);

        $files = $request->file('csv_files');
        $uploadDirectory = public_path('uploads');

        Log::info('Starting CSV processing');
        Log::info('Upload directory: ' . $uploadDirectory);

        if (!is_dir($uploadDirectory)) {
            if (mkdir($uploadDirectory, 0755, true)) {
                Log::info("Upload directory created: $uploadDirectory");
            } else {
                Log::error("Failed to create upload directory: $uploadDirectory");
            }
        }

        $csvFilePaths = [];

        foreach ($files as $file) {
            try {
                $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                Log::info("Processing file: $file_name");

                $columnName = 'Phone type 1';
                $columnName2 = 'Phone usage 1';
                $keywords = ['wireless'];

                $reader = Reader::createFromPath($file->getPathname(), 'r');
                $reader->setHeaderOffset(0);
                $header = $reader->getHeader();

                if (!in_array($columnName, $header) || !in_array($columnName2, $header)) {
                    Log::warning("Required columns missing in $file_name: Skipping.");
                    continue;
                }

                $columnIndex = array_search($columnName, $header);
                $records = $reader->getRecords();

                // Wireless filter
                $filteredRows = [];
                foreach ($records as $record) {
                    if (isset($record[$header[$columnIndex]]) && strtolower($record[$columnName2]) !== 'no data available or no usage in the last 2 months') {
                        $cellValue = $record[$header[$columnIndex]];
                        foreach ($keywords as $keyword) {
                            if (stripos($cellValue, $keyword) !== false && !empty($cellValue)) {
                                $filteredRows[] = $record;
                                break;
                            }
                        }
                    }
                }

                $wirelessFilePath = $uploadDirectory . "/{$file_name}_wireless_numbers.csv";
                $writer = Writer::createFromPath($wirelessFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($filteredRows);
                $csvFilePaths[] = $wirelessFilePath;
                Log::info("Wireless CSV saved: $wirelessFilePath");

                // Landline filter
                $keywords = ['landline'];
                $reader = Reader::createFromPath($file->getPathname(), 'r');
                $reader->setHeaderOffset(0);
                $header = $reader->getHeader();
                $columnIndex = array_search($columnName, $header);
                $records = $reader->getRecords();

                $filteredRows = [];
                foreach ($records as $record) {
                    if (isset($record[$header[$columnIndex]])) {
                        $cellValue = $record[$header[$columnIndex]];
                        foreach ($keywords as $keyword) {
                            if (stripos($cellValue, $keyword) !== false) {
                                $filteredRows[] = $record;
                                break;
                            }
                        }
                    }
                }

                $landlineFilePath = $uploadDirectory . "/{$file_name}_landline_only_numbers.csv";
                $writer = Writer::createFromPath($landlineFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($filteredRows);
                $csvFilePaths[] = $landlineFilePath;
                Log::info("Landline CSV saved: $landlineFilePath");

                // No Usage filter
                $columnNameUsage1 = 'Phone usage 1';
                $columnNameUsage2 = 'Phone usage 2';
                $columnName = 'Phone number 1';
                $keywordsUsage = ['no data available or no usage in the last 2 months'];

                $reader = Reader::createFromPath($file->getPathname(), 'r');
                $reader->setHeaderOffset(0);
                $header = $reader->getHeader();
                $columnIndex = array_search($columnName, $header);
                $records = $reader->getRecords();

                $filteredRowsUsage = [];
                foreach ($records as $record) {
                    $cellValue = $record[$header[$columnIndex]] ?? '';
                    if (
                        isset($record[$columnNameUsage1], $record[$columnNameUsage2]) &&
                        (in_array(strtolower($record[$columnNameUsage1]), $keywordsUsage) &&
                            in_array(strtolower($record[$columnNameUsage2]), $keywordsUsage)) ||
                        empty($cellValue)
                    ) {
                        $filteredRowsUsage[] = $record;
                    }
                }

                $noUsageFilePath = $uploadDirectory . "/{$file_name}_no_usage_numbers.csv";
                $writer = Writer::createFromPath($noUsageFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($filteredRowsUsage);
                $csvFilePaths[] = $noUsageFilePath;
                Log::info("No usage CSV saved: $noUsageFilePath");

                // Replace landlines
                $reader = Reader::createFromPath($landlineFilePath, 'r');
                $reader->setHeaderOffset(0);
                $header = $reader->getHeader();
                $records = $reader->getRecords();

                $updatedRows = [];
                foreach ($records as $record) {
                    if (
                        isset($record['Phone number 2']) &&
                        strtolower($record['Phone type 2']) == 'wireless' &&
                        !in_array(strtolower($record['Phone usage 2']), $keywordsUsage)
                    ) {
                        $record['Phone number 1'] = $record['Phone number 2'];
                        $record['Phone type 1'] = $record['Phone type 2'];
                        $record['Phone usage 1'] = $record['Phone usage 2'];
                        $record['Likely owner 1'] = $record['Likely owner 2'];
                        $updatedRows[] = $record;
                    }
                }

                $processedFilePath = $uploadDirectory . "/{$file_name}_landline_processed_numbers.csv";
                $writer = Writer::createFromPath($processedFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($updatedRows);
                $csvFilePaths[] = $processedFilePath;
                Log::info("Processed CSV saved: $processedFilePath");
            } catch (\Exception $e) {
                Log::error("Error processing file {$file->getClientOriginalName()}: " . $e->getMessage());
            }
        }

        // Create ZIP
        $zipFileName = 'processed_csvs_' . time() . '.zip';
        $zipFilePath = $uploadDirectory . '/' . $zipFileName;

        $zip = new ZipArchive;
        if ($zip->open($zipFilePath, ZipArchive::CREATE) === true) {
            foreach ($csvFilePaths as $filePath) {
                $relativeName = basename($filePath);
                $zip->addFile($filePath, $relativeName);
            }
            $zip->close();
            Log::info("ZIP archive created: $zipFilePath");
        } else {
            Log::error("Failed to create ZIP archive: $zipFilePath");
        }

        return redirect()->back()->with([
            'success' => 'Files processed and zipped successfully.',
            'zipfile' => asset('uploads/' . $zipFileName),
        ]);
    }
}
