<?php

namespace App\Http\Controllers;

use App\Models\CsvFile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use League\Csv\Writer;
use League\Csv\Reader;

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

    // public function processCSV(Request $request)
    // {
    //     $request->validate([
    //         'csv_file' => 'required|file|mimes:csv,txt',
    //     

    //     $fil = $request->file('csv_file');
    //     $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
    //     $uploadDirectory = '/home/customer/www/internaltools.godspeedoffers.com/public_html/uploads/';
    //     $columnName = 'Phone type 1';
    //     $keywords

    //     // Step 1: Read and filter the CSV for wireless numbers
    //     $reader = Reader::createFromPath($file->getPathname(), 'r');
    //     $reader->setHeaderOffset(0); // Assuming the first row is the header

    //     $header = $reader->getHeader();
    //     if (!in_array($columnName, $header)) {
    //         return response()->json(['error' => 'Invalid column name provided.'], 400);
    //     }

    //     $columnIndex = array_search($columnName, $header);
    //     $records = $reader->getRecords();
    //     $filteredRows = [];

    //     foreach ($records as $record) {
    //         if (isset($record[$header[$columnIndex]])) {
    //             $cellValue = $record[$header[$columnIndex]];
    //             foreach ($keywords as $keyword) {
    //                 if (stripos($cellValue, $keyword) !== false && !empty($cellValue)) {
    //                     $filteredRows[] = $record;
    //                     break;
    //                 }
    //             }
    //         }
    //     }

    //     // Step 2: Save the wireless numbers CSV
    //     $wirelessOutputPath = $uploadDirectory . $file_name . '_wireless_numbers.csv';
    //     $writer = Writer::createFromPath($wirelessOutputPath, 'w+');
    //     $writer->insertOne($header);
    //     $writer->insertAll($filteredRows);

    //     // Repeat similar steps for landline numbers, no usage records, and processed landline numbers
    //     $keywords = ['landline'];
    //     $filteredRows = [];
    //     foreach ($records as $record) {
    //         if (isset($record[$header[$columnIndex]])) {
    //             $cellValue = $record[$header[$columnIndex]];
    //             foreach ($keywords as $keyword) {
    //                 if (stripos($cellValue, $keyword) !== false || empty($cellValue)) {
    //                     $filteredRows[] = $record;
    //                     break;
    //                 }
    //             }
    //         }
    //     }

    //     $landlineOutputPath = $uploadDirectory . $file_name . '_landline_only_numbers.csv';
    //     $writer = Writer::createFromPath($landlineOutputPath, 'w+');
    //     $writer->insertOne($header);
    //     $writer->insertAll($filteredRows);

    //     // Process other steps as in the original function
    //     $nousageOutputPath = $uploadDirectory . $file_name . '_no_usage_numbers.csv';
    //     $processedOutputPath = $uploadDirectory . $file_name . '_processed_landline_numbers.csv';

    //     // Save each processed file to the `/uploads` directory
    //     $writer = Writer::createFromPath($nousageOutputPath, 'w+');
    //     $writer->insertOne($header);
    //     $writer->insertAll($filteredRowsUsage);

    //     $writer = Writer::createFromPath($processedOutputPath, 'w+');
    //     $writer->insertOne($header);
    //     $writer->insertAll($updatedRows);
    //     CsvFile::create([
    //         'original_filename'=>$file_name,
    //         'wireless_path'=>$wirelessOutputPath,
    //         'landline_only_path'=>$landlineOutputPath,
    //         'processed_landline_path'=>$processedOutputPath,
    //         'no_usage_path'=>$nousageOutputPath
    //     ]);
    //     return redirect()->back()->with('success', 'CSV Processed successfully.');
    // }
    public function processCSV(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt',
        ]);

        $file = $request->file('csv_file');
        $file_name = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $columnName = 'Phone type 1';
        $columnName2 = 'Phone usage 1';
        $keywords = array('wireless');
        $uploadDirectory = '/home/support/web/internaltools.godspeedoffers.com/public_html/uploads'; // Use public directory for easier file access
        if (!is_dir($uploadDirectory)) {
            mkdir($uploadDirectory, 0755, true);
        }
        // Step 1: Read and filter the CSV for landline-only records
        $reader = Reader::createFromPath($file->getPathname(), 'r');
        $reader->setHeaderOffset(0); // Assuming the first row is the header

        $header = $reader->getHeader(); // Get CSV headers
        if (!in_array($columnName, $header)) {
            return response()->json(['error' => 'Invalid column name provided.'], 400);
        }
        if (!in_array($columnName2, $header)) {
            return response()->json(['error' => 'Invalid column name provided.'], 400);
        }

        $columnIndex = array_search($columnName, $header); // Map column name to index
        $records = $reader->getRecords();

        $filteredRows = [];
        foreach ($records as $record) {
            if (isset($record[$header[$columnIndex]])&&strtolower($record[$columnName2]) !== 'no data available or no usage in the last 2 months') {
                $cellValue = $record[$header[$columnIndex]];
                foreach ($keywords as $keyword) {
                    // Check for keyword match or if cell is not empty
                    
                    if (stripos($cellValue, $keyword) !== false && !empty($cellValue) ) {
                        $filteredRows[] = $record;
                        break;
                    }
                }
            }
        }

        // Step 2: Create the first CSV file (landline_only_numbers.csv)
        $wirelessFilePath = $uploadDirectory . "/{$file_name}_wireless_numbers.csv";
        $writer = Writer::createFromPath($wirelessFilePath, 'w+');
        $writer->insertOne($header); // Insert header
        $writer->insertAll($filteredRows); // Insert filtered rows

        $columnName = 'Phone type 1';
        $keywords = array('landline'); // 'landline' and '' to include empty cells

        // Step 1: Read and filter the CSV for landline-only records
        $reader = Reader::createFromPath($file->getPathname(), 'r');
        $reader->setHeaderOffset(0); // Assuming the first row is the header

        $header = $reader->getHeader(); // Get CSV headers
        if (!in_array($columnName, $header)) {
            return response()->json(['error' => 'Invalid column name provided.'], 400);
        }

        $columnIndex = array_search($columnName, $header); // Map column name to index
        $records = $reader->getRecords();

        $filteredRows = [];
        foreach ($records as $record) {
            if (isset($record[$header[$columnIndex]])) {
                $cellValue = $record[$header[$columnIndex]];
                foreach ($keywords as $keyword) {
                    // Check for keyword match or if cell is empty
                    if (stripos($cellValue, $keyword) !== false) {
                        $filteredRows[] = $record;
                        break;
                    }
                }
            }
        }

        // Step 2: Create the first CSV file (landline_only_numbers.csv)
        $landlineFilePath = $uploadDirectory . "/{$file_name}_landline_only_numbers.csv";

        $writer = Writer::createFromPath($landlineFilePath, 'w+');
        $writer->insertOne($header); // Insert header
        $writer->insertAll($filteredRows); // Insert filtered rows

        // Step 3: Filter for records with no usage in the last 2 months (Phone usage 1 and Phone usage 2)
        $columnNameUsage1 = 'Phone usage 1';
        $columnNameUsage2 = 'Phone usage 2';
        $columnName="Phone number 1";
        $keywordsUsage = array('no data available or no usage in the last 2 months');

        // Read the original CSV again for filtering based on Phone usage 1 and Phone usage 2
        $reader = Reader::createFromPath($file->getPathname(), 'r');
        $reader->setHeaderOffset(0); // Assuming the first row is the header

        $header = $reader->getHeader(); // Get CSV headers
        $columnIndex = array_search($columnName, $header); // Map column name to index

        if (!in_array($columnNameUsage1, $header) || !in_array($columnNameUsage2, $header)) {
            return response()->json(['error' => 'Invalid column names provided.'], 400);
        }

        $columnIndexUsage1 = array_search($columnNameUsage1, $header); // Map column name to index
        $columnIndexUsage2 = array_search($columnNameUsage2, $header); // Map column name to index
        $records = $reader->getRecords();

        $filteredRowsUsage = [];
        foreach ($records as $record) {
            $cellValue = $record[$header[$columnIndex]];
            if (isset($record[$header[$columnIndexUsage1]]) && isset($record[$header[$columnIndexUsage2]])) {
                $usage1 = $record[$header[$columnIndexUsage1]];
                $usage2 = $record[$header[$columnIndexUsage2]];

                // Check if both Phone usage 1 and Phone usage 2 have the value "No data available or no usage in the last 2 months"
                if ((in_array(strtolower($usage1), $keywordsUsage) && in_array(strtolower($usage2), $keywordsUsage))||empty($cellValue)
                ) {
                    $filteredRowsUsage[] = $record;
                }
            }
        }

        // Step 4: Create the second CSV file (no_usage_numbers.csv)
        $nousageFilePath = $uploadDirectory . "/{$file_name}_no_usage_numbers.csv";

        $writer = Writer::createFromPath($nousageFilePath, 'w+');
        $writer->insertOne($header); // Insert header
        $writer->insertAll($filteredRowsUsage); // Insert filtered rows

        // Step 5: Now process the landline_only_numbers.csv to copy Phone number 2 to Phone number 1
        $landlineFile = $landlineFilePath;
        $reader = Reader::createFromPath($landlineFile, 'r');
        $reader->setHeaderOffset(0); // Assuming the first row is the header

        $header = $reader->getHeader(); // Get CSV headers
        $columnNamePhone1 = 'Phone number 1';
        $columnNamePhone2 = 'Phone number 2';
        $columnNamePhoneType2 = 'Phone type 2';
        $columnNamePhoneType1 = 'Phone type 1';
        $columnNameUsage2 = 'Phone usage 2';
        $columnNameUsage1 = 'Phone usage 1';
        $columnLikelyowner2='Likely owner 2';
        $columnLikelyowner1='Likely owner 1';
        $keywordsUsage2 = array('no data available or no usage in the last 2 months');

        $records = $reader->getRecords();
        $updatedRows = [];

        foreach ($records as $record) {
            // Check if Phone type 2 is 'wireless' and Phone usage 2 does not have the 'No data available' value
            if (
                isset($record[$columnNamePhone2]) && strtolower($record[$columnNamePhoneType2]) == 'wireless' &&
                isset($record[$columnNameUsage2]) && !in_array(strtolower($record[$columnNameUsage2]), $keywordsUsage2)
            ) {

                // Copy Phone number 2 value to Phone number 1
                $record[$columnNamePhone1] = $record[$columnNamePhone2];
                $record[$columnNamePhoneType1] = $record[$columnNamePhoneType2];
                $record[$columnNameUsage1] = $record[$columnNameUsage2];
                $record[$columnLikelyowner1] = $record[$columnLikelyowner2];
                $updatedRows[] = $record;
            }
        }

        // Step 6: Create the final processed CSV with updated phone numbers
        $processedFilePath = $uploadDirectory . "/{$file_name}_landline_processed_numbers.csv";
        $writer = Writer::createFromPath($processedFilePath, 'w+');
        $writer->insertOne($header); // Insert header
        $writer->insertAll($updatedRows); // Insert updated rows

        //processed wireless
        // Step 5: Now process the CSV to copy Phone number 2 to Phone number 1 for wireless no data available
        $reader = Reader::createFromPath($file->getPathname(), 'r');
        $reader->setHeaderOffset(0); // Assuming the first row is the header

        $header = $reader->getHeader(); // Get CSV headers
        $columnNamePhone1 = 'Phone number 1';
        $columnNamePhone2 = 'Phone number 2';
        $columnNamePhoneType2 = 'Phone type 2';
        $columnNamePhoneType1 = 'Phone type 1';
        $columnNameUsage2 = 'Phone usage 2';
        $columnNameUsage1 = 'Phone usage 1';
        $columnLikelyowner2='Likely owner 2';
        $columnLikelyowner1='Likely owner 1';
        $keywordsUsage2 = array('no data available or no usage in the last 2 months');

        $records = $reader->getRecords();
        $updatedRows = [];

        foreach ($records as $record) {
            // Check if Phone type 2 is 'wireless' and Phone usage 2 does not have the 'No data available' value
            if (
                isset($record[$columnNamePhone2]) && strtolower($record[$columnNamePhoneType2]) == 'wireless' &&
                isset($record[$columnNamePhone1]) && strtolower($record[$columnNamePhoneType1]) == 'wireless' &&
                isset($record[$columnNameUsage1]) && in_array(strtolower($record[$columnNameUsage1]), $keywordsUsage2)&&
                isset($record[$columnNameUsage2]) && !in_array(strtolower($record[$columnNameUsage2]), $keywordsUsage2)
            ) {

                // Copy Phone number 2 value to Phone number 1
                $record[$columnNamePhone1] = $record[$columnNamePhone2];
                $record[$columnNamePhoneType1] = $record[$columnNamePhoneType2];
                $record[$columnNameUsage1] = $record[$columnNameUsage2];
                $record[$columnLikelyowner1] = $record[$columnLikelyowner2];
                $updatedRows[] = $record;
            }
        }

        // Step 6: Create the final processed CSV with updated phone numbers
        $wirelessprocessedFilePath = $uploadDirectory . "/{$file_name}_wireless_processed_numbers.csv";
        $writer = Writer::createFromPath($wirelessprocessedFilePath, 'w+');
        $writer->insertOne($header); // Insert header
        $writer->insertAll($updatedRows); // Insert updated rows
        // Create a zip file
        $zipFilePath = $uploadDirectory . "/{$file_name}_processed_files.zip";
        $zip = new \ZipArchive;
        if ($zip->open($zipFilePath, \ZipArchive::CREATE) === true) {
            $zip->addFile($wirelessFilePath, basename($wirelessFilePath));
            $zip->addFile($landlineFilePath, basename($landlineFilePath));
            $zip->addFile($processedFilePath, basename($processedFilePath));
            $zip->addFile($nousageFilePath, basename($nousageFilePath));
            $zip->addFile($wirelessprocessedFilePath, basename($wirelessprocessedFilePath));
            $zip->close();
        }
    
        // Return the public URL of the zip file
        if (file_exists($zipFilePath)) {
            $zipFileUrl = asset('uploads/' . basename($zipFilePath));
            return redirect()->back()->with([
                'zipfile' => $zipFileUrl,
                'success' => 'CSV files processed and zip file created successfully!',
            ]);
                    } else {
            return response()->json(['error' => 'Failed to create the zip file.'], 500);
        }
    }
}
