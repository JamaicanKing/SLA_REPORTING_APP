<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CcCalls;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Common;

class ReportController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function store(Request $request)
    {
        // Get file from form request
        $file = $request->file('file');
        
        // Get specific reader object for the file type being processed
        $reader = IOFactory::createReader('Xlsx');

        // Generate the worksheet object using the uploadeded file
        $spreadsheet = $reader->load($file->path());

        // Get all rows in the worksheet as an array
        $rows = $spreadsheet->getActiveSheet()->toArray();

        $success = 0;
        $failed = 0;

        // Loop through worksheet rows and save the data to the database
        foreach($rows as $rowNumber => $rowData){
            
            // Skip the first row since it contains headers
            if($rowNumber == 0){
                continue;
            }

            Log::info('Row: ' . $rowNumber + 1);

            $fallbackDate = $spreadsheet->getActiveSheet()->getCell('J' . $rowNumber + 1)->getFormattedValue();
            // Get the correctly formatted date based on dates received in different formats
            $startDate = Common::getFormattedDate($spreadsheet->getActiveSheet()->getCell('L' . $rowNumber + 1)->getFormattedValue(), $fallbackDate);
            $finishDate = Common::getFormattedDate($spreadsheet->getActiveSheet()->getCell('M' . $rowNumber + 1)->getFormattedValue(), $fallbackDate);

           try
           {
               // Attempt to save the row to the database
               $created = CcCalls::create([
                    'account_number' => $spreadsheet->getActiveSheet()->getCell('A' . $rowNumber + 1)->getValue(),
                    'customer_name' => $spreadsheet->getActiveSheet()->getCell('B' . $rowNumber + 1)->getValue(),
                    'job_number' => $spreadsheet->getActiveSheet()->getCell('D' . $rowNumber + 1)->getValue(),
                    'date_started' => $startDate,
                    'date_finished' => $finishDate,
                    'engineer' => $spreadsheet->getActiveSheet()->getCell('P' . $rowNumber + 1)->getValue(),
                    'created_by' => $spreadsheet->getActiveSheet()->getCell('T' . $rowNumber + 1)->getValue(),
                ]);
            }
            catch(Exception $e){

                // Log an error received when trying to save the row to the database
                Log::error($e->getMessage());
                $created = false;

            }

            // Increase the count of success or failed based on the result of the attempt to save to the database
            if($created){
                $success++;
            } else {
                $failed++;
            }

            // Limit the amount of rows processed for testing purposes
            // This should be removed when testing is finished
            if($rowNumber > 15){
                break;
            }
        }

        // Return the status of the operation in a JSON format
        return response()->json([
            'total_records' => count($rows) - 1,
            'total_success' => $success,
            'total_failed' => $failed
        ]);
    }

    public function write(Request $request)
    {
        $persons = CcCalls::all();
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Account Number');
        $sheet->setCellValue('B1', 'Job Number');
        $sheet->setCellValue('C1', 'Date Finished');
        $sheet->setCellValue('D1', 'Engineer');

        $dataRow = 2;
        foreach($persons as $person){
            $sheet->setCellValue('A' . $dataRow, $person->account_number);
            $sheet->setCellValue('B' . $dataRow, $person->job_number);
            $sheet->setCellValue('C' . $dataRow, $person->date_finished);
            $sheet->setCellValue('D' . $dataRow, $person->engineer);

            $dataRow++;
        }

        $filePath = storage_path() . '\hello world.xlsx';

        $writer = new Xlsx($spreadsheet);
        $saved = $writer->save($filePath);

        //return response()->json(['status' => true, 'path' => $filePath]);

        //dd($writer);

        $date = date('Ymd_His');
        $filename = "report_$date.xlsx";

        // Download file with custom headers
        return response()->download($filePath, $filename, [
            'Content-Type' => 'application/vnd.ms-excel',
            'Content-Disposition' => 'inline; filename="' . $filename . '"'
        ]);
    }
}
