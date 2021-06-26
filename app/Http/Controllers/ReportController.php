<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Models\CcCalls;
use App\Models\DigiplusTickets;
use Illuminate\Support\Facades\Log;
use Exception;
use App\Models\Common;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use DateTime;
use PhpParser\Node\Stmt\Foreach_;

class ReportController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function store(Request $request)
    {
        set_time_limit(900);

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

            Log::info('Row: ' . ($rowNumber + 1));

            /*$fallbackDate = $spreadsheet->getActiveSheet()->getCell('J' . $rowNumber + 1)->getFormattedValue();*/
            // Get the correctly formatted date based on dates received in different formats
            $createdOn = Common::getFormattedDate($spreadsheet->getActiveSheet()->getCell('B' . ($rowNumber + 1))->getFormattedValue());
            $mondifiedOn = Common::getFormattedDate($spreadsheet->getActiveSheet()->getCell('O' . ($rowNumber + 1))->getFormattedValue());
            //$pivotDate = date('M d', strtotime($createdOn));
            $pivotDate = new DateTime($createdOn);
            $createdBy = $spreadsheet->getActiveSheet()->getCell('M' .($rowNumber + 1))->getValue();
            $modifiedBy = $spreadsheet->getActiveSheet()->getCell('N' . ($rowNumber + 1))->getValue();
            $status = $spreadsheet->getActiveSheet()->getCell('G' . ($rowNumber + 1))->getValue();

            $tierFilter = Common::getTierFilter($createdBy, $modifiedBy, $status);
            $resolutionTime = Common::getResolutionTime($request->input('date',date('Y-m-d H:i:s')), $tierFilter, $createdOn, $mondifiedOn);
            $sla = Common::getSla($resolutionTime);

            try
            {
               // Attempt to save the row to the database
               $created = DigiplusTickets::create([

                'case_number' => $spreadsheet->getActiveSheet()->getCell('A' . ($rowNumber + 1))->getValue(),
                'created_on' => $createdOn,
                'case_type'=> $spreadsheet->getActiveSheet()->getCell('C' . ($rowNumber + 1))->getValue(),
                'case_category' => $spreadsheet->getActiveSheet()->getCell('D' . ($rowNumber + 1))->getValue(),
                'case_sub_category' => $spreadsheet->getActiveSheet()->getCell('E' . ($rowNumber + 1))->getValue(),
                'description' => $spreadsheet->getActiveSheet()->getCell('F' . ($rowNumber + 1))->getValue(),
                'status' => $spreadsheet->getActiveSheet()->getCell('G' . ($rowNumber + 1))->getValue(),
                'account_number' => $spreadsheet->getActiveSheet()->getCell('H' . ($rowNumber + 1))->getValue(),
                'customer_type' => $spreadsheet->getActiveSheet()->getCell('I' . ($rowNumber + 1))->getValue(),
                'customer_name' => $spreadsheet->getActiveSheet()->getCell('J' .($rowNumber + 1))->getValue(),
                'primary_mobile_number' => $spreadsheet->getActiveSheet()->getCell('K' . ($rowNumber + 1))->getValue(),
                'city' => $spreadsheet->getActiveSheet()->getCell('L' . ($rowNumber + 1))->getValue(),
                'created_by' => $spreadsheet->getActiveSheet()->getCell('M' . ($rowNumber + 1))->getValue(),
                'modified_by' => $spreadsheet->getActiveSheet()->getCell('N' . ($rowNumber + 1))->getValue(),
                'modified_on' => $mondifiedOn,
                'case_title' => $spreadsheet->getActiveSheet()->getCell('P' . ($rowNumber + 1))->getValue(),
                'pivot_date_created' => $pivotDate->format('M d'),
                'tier_filter' => $tierFilter,
                'resolution_time' => $resolutionTime,
                'sla' => $sla,
                'week' => $pivotDate->format('W'),
                'escalation_team' => $spreadsheet->getActiveSheet()->getCell('V' . ($rowNumber + 1))->getValue(),
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
               // break;
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
        $createdOn1 = $request->input('createdOn1');
        $createdOn2 = $request->input('createdOn2');
        $columnIndex = 2;
        
        $reportDetails = DigiplusTickets::getReportDetails($createdOn1,$createdOn2);
        
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'SLA');
        $sheet->setCellValue('B1', 'Created On');
        $sheet->setCellValue('C1', 'Case Count');

        

        $data = [];
        
        foreach($reportDetails as $reportDetail){
          
            $data[$reportDetail->created_on][$reportDetail->sla] = $reportDetail->case_count;
            
        }

        $slaExcelData = [];
        $maxRowNum = 1;

        foreach ($data as $date => $slaList) {

            $rowNum = 2;
            $column = Coordinate::stringFromColumnIndex($columnIndex);
            $cell = $column . '1';
            $sheet->setCellValue($cell, $date);

            foreach ($slaList as $sla => $caseCount) {

                if(isset($slaExcelData[$sla]) === false ){

                    if(count($slaExcelData) > 0){
                        foreach($slaExcelData as $excelData){
                            $maxRowNum = ($excelData['row'] > $maxRowNum) ? $excelData['row'] : $maxRowNum;
                        }
                    }
                    $maxRowNum = $maxRowNum + 1;

                    $slaExcelData[$sla] = [
                        'row' => $maxRowNum,
                        'cell' =>"A" . $maxRowNum,
                    ];

                }
                
                $dataCell = $column . "" . $slaExcelData[$sla]['row'];
                $sheet->setCellValue($slaExcelData[$sla]['cell'],$sla);
                $sheet->setCellValue("$dataCell",$caseCount);

                
                $rowNum++;
                
            }

            $columnIndex++;
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

    public function show(){
        
        return view('sla');
    }
}
