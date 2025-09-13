<?php

namespace App\Http\Controllers;

use App\Events\CsvProcessingProgress;
use App\Jobs\ImportContactsJob;
use App\Models\ContactImport;
use App\Models\ContactImportProgress;
use App\Models\CsvFile;
use App\Models\Folder;
use App\Models\Workflow;
use GrahamCampbell\ResultType\Success;
use Illuminate\Contracts\Auth\Middleware\AuthenticatesRequests;
use Illuminate\Http\Request;
use League\Csv\Writer;
use League\Csv\Reader;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

use function Laravel\Prompts\error;

class CSVProcessorController extends Controller
{
    public function showForm()
    {
        $workflows = Workflow::where('organisation_id', auth()->user()->organisation_id)
            ->get();
        return inertia("CSV/Upload", [
            'workflows' => $workflows,
            'success' => session('success'),
            'zipfile' => session('zipfile'),
            'error' => session('error')
        ]);
    }

    public function index()
    {
        $latestProgress = ContactImportProgress::where('user_id', auth()->user()->id)
            ->latest()
            ->first();
        $currentImportProgress = $latestProgress ? min(round(($latestProgress->processed_contacts / $latestProgress->total_contacts) * 100), 100) : null;
        $workflows = Workflow::where('organisation_id', auth()->user()->organisation_id)
            ->get();
        return inertia("CSV/ContactImport", [
            'currentImportProgress' => $currentImportProgress,
            'Success' => session('success'),
            'error' => session('error'),
            'workflows' => $workflows,
        ]);
    }
    public function import(Request $request)
    {

        try {
            $validated = $request->validate([
                'mappings' => 'required|array',
                'data' => 'required|array',
                'selected_workflow_id' => 'required|exists:workflows,id',
                'filename' => 'required|string',
            ]);
            $contact_import_progress = ContactImportProgress::create([
                'user_id' => auth()->user()->id,
                'processed_contacts' => 0,
                'imported_contacts' => 0,
                'failed_contacts' => 0,
                'total_contacts' => (int) count($validated['data']),
            ]);
            $filePath = 'imports/import_' . uniqid() . '.json';
            Storage::put($filePath, json_encode($validated['data']));
            $import = ContactImport::create([
                'user_id' => auth()->user()->id,
                'mappings' => json_encode($validated['mappings']),
                'data_file' => $filePath, // reference only
                'progress_id' => $contact_import_progress->id,
                'workflow_id' => $validated['selected_workflow_id'],
                'filename' => $validated['filename'],
            ]);
            ImportContactsJob::dispatch($import->id);
            return redirect()->back()->with('success', 'Contacts import started successfully');
        } catch (\Exception $e) {
            Log::error('Import failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Import failed: ' . $e->getMessage());
        }
    }
    public function processCSV(Request $request)
    {
        $request->validate([
            'csv_files.*' => 'required|file|mimes:csv,txt',
            'sms_workflow_id' => 'nullable',
            'calls_workflow_id' => 'nullable',
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
                return redirect()->back()->with([
                    'error' => "Failed to create upload directory.",
                ]);
            }
        }

        $csvFilePaths = [];

        foreach ($files as $file) {
            try {
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                // Sanitize the filename
                $file_name = preg_replace('/[^A-Za-z0-9_\-]/', '_', $originalName);
                $file_name = strtolower(trim($file_name, '_'));
                Log::info("Processing file: $file_name");
                $folder = Folder::create([
                    'name' => $file_name,
                    'organisation_id' => auth()->user()->organisation_id,
                    'user_id' => auth()->user()->id,
                ]);
                $folder_id = $folder->id;
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
                Log::info("No of filtered wireless rows: " . count($filteredRows));
                if (!empty($filteredRows) && $request->sms_workflow_id) {
                    dispatch(new \App\Jobs\ProcessCsvFile($wirelessFilePath, $request->sms_workflow_id, $folder_id, auth()->user()));
                }
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
                    if (isset($record[$header[$columnIndex]]) && strtolower($record[$columnName2]) !== 'no data available or no usage in the last 2 months') {
                        $cellValue = $record[$header[$columnIndex]];
                        foreach ($keywords as $keyword) {
                            if (stripos($cellValue, $keyword) !== false) {
                                $filteredRows[] = $record;
                                break;
                            }
                        }
                    }
                }

                $landlineFilePath = $uploadDirectory . "/{$file_name}_landline_numbers.csv";
                $writer = Writer::createFromPath($landlineFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($filteredRows);
                $csvFilePaths[] = $landlineFilePath;
                if (!empty($filteredRows) && $request->calls_workflow_id) {
                    dispatch(new \App\Jobs\ProcessCsvFile($landlineFilePath, $request->calls_workflow_id, $folder_id, auth()->user()));
                }

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

                // This is for wireless processed numbers
                $reader = Reader::createFromPath($file->getPathname(), 'r');
                $reader->setHeaderOffset(0);
                $header = $reader->getHeader();
                $records = $reader->getRecords();

                $updatedRows = [];
                foreach ($records as $record) {
                    if (
                        isset($record['Phone number 2']) &&
                        strtolower($record['Phone type 2']) == 'wireless' &&
                        //strtolower($record['Phone type 1']) == 'wireless' &&
                        in_array(strtolower($record['Phone usage 1']), $keywordsUsage) &&
                        !in_array(strtolower($record['Phone usage 2']), $keywordsUsage)
                    ) {
                        $record['Phone number 1'] = $record['Phone number 2'];
                        $record['Phone type 1'] = $record['Phone type 2'];
                        $record['Phone usage 1'] = $record['Phone usage 2'];
                        $record['Likely owner 1'] = $record['Likely owner 2'];
                        $updatedRows[] = $record;
                    }
                }

                $wirelessProcessedFilePath = $uploadDirectory . "/{$file_name}_wireless_processed_numbers.csv";
                $writer = Writer::createFromPath($wirelessProcessedFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($updatedRows);
                $csvFilePaths[] =  $wirelessProcessedFilePath;
                if (!empty($filteredRows) && $request->sms_workflow_id) {
                    dispatch(new \App\Jobs\ProcessCsvFile($wirelessProcessedFilePath, $request->sms_workflow_id, $folder_id, auth()->user()));
                }
                Log::info("Processed CSV saved:  $wirelessProcessedFilePath");
                // This is for landline processed numbers
                $reader = Reader::createFromPath($file->getPathname(), 'r');
                $reader->setHeaderOffset(0);
                $header = $reader->getHeader();
                $records = $reader->getRecords();

                $updatedRows = [];
                foreach ($records as $record) {
                    if (
                        isset($record['Phone number 2']) &&
                        strtolower($record['Phone type 2']) == 'landline' &&
                        (strtolower($record['Phone type 1']) == 'landline' ||
                            strtolower($record['Phone type 1']) == 'wireless') &&
                        in_array(strtolower($record['Phone usage 1']), $keywordsUsage) &&
                        !in_array(strtolower($record['Phone usage 2']), $keywordsUsage)
                    ) {
                        $record['Phone number 1'] = $record['Phone number 2'];
                        $record['Phone type 1'] = $record['Phone type 2'];
                        $record['Phone usage 1'] = $record['Phone usage 2'];
                        $record['Likely owner 1'] = $record['Likely owner 2'];
                        $updatedRows[] = $record;
                    }
                }

                $landlineProcessedFilePath = $uploadDirectory . "/{$file_name}_landline_processed_numbers.csv";
                $writer = Writer::createFromPath($landlineProcessedFilePath, 'w+');
                $writer->insertOne($header);
                $writer->insertAll($updatedRows);
                $csvFilePaths[] =  $landlineProcessedFilePath;
                if (!empty($filteredRows) && $request->calls_workflow_id) {
                    dispatch(new \App\Jobs\ProcessCsvFile($landlineProcessedFilePath, $request->calls_workflow_id, $folder_id, auth()->user()));
                }
                Log::info("Processed CSV saved:  $landlineProcessedFilePath");
            } catch (\Exception $e) {
                Log::error("Error processing file {$file->getClientOriginalName()}: " . $e->getMessage());
                return redirect()->back()->with([
                    'error' => "Error processing file {$file->getClientOriginalName()}: " . $e->getMessage(),
                ]);
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
        $workflows_message = $request->sms_workflow_id || $request->calls_workflow_id ? "Workflows were also created" : "No workflows were created";
        return redirect()->back()->with([
            'success' => 'Files processed and zipped successfully. ' . $workflows_message,
            'zipfile' => asset('uploads/' . $zipFileName),
        ]);
    }
}
