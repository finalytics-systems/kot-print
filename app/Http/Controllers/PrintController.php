<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use PDF;
use Carbon\Carbon;

class PrintController extends Controller
{
    public function printQT(Request $request)
    {
        $this->moveAndRenameFiles();

        $this->ensureDirectoryExists();

        $orderId = $request->input('order_details.order_id', null);
        $tableNo = $request->input('order_details.table_no', null);
        $orderDateTime = $request->input('order_details.order_datetime', null);
        $covers = $request->input('order_details.covers', null);
        $orderTaker = $request->input('order_details.orderTaker', null);
		$reprinted = $request->input('order_details.reprinted', null);
        $data = $request['sections'];
        
        foreach($data as $section => $items) {
            if (!empty($items)) {
                $fileName = $section . '_qt.pdf';
                $storagePath = storage_path('app/qts/' . $fileName);
                $pdf = PDF::loadView('qt', compact('items', 'section', 'orderId', 'tableNo', 'orderDateTime', 'covers', 'orderTaker', 'reprinted'))
                    ->setOption(['dpi' => 203, 'defaultFont' => 'sans-serif']);
                $pdf->save($storagePath);
            }
        }

        $scriptPath = storage_path('app/print_invoices_by_kitchen.ps1');
        $command = "powershell -ExecutionPolicy Bypass -File \"$scriptPath\"";
        $output = shell_exec($command);
        
        return response()->json([
            'message' => "All good!"
        ]);
    }

    public function ensureDirectoryExists()
    {
        $directory = 'qts';  // The directory inside storage/app
     
        if (!Storage::exists($directory)) {
            Storage::makeDirectory($directory);
        }
    }

    public function moveAndRenameFiles()
    {
        $originalDirectory = 'qts';
        $targetDirectory = 'qts-printed';

        if (!Storage::exists($targetDirectory)) {
            Storage::makeDirectory($targetDirectory);
        }

        $files = Storage::files($originalDirectory);

        foreach ($files as $file) {
            $newFileName = basename($file) . '_' . Carbon::now()->format('Y-m-d_His') . '.pdf';
            
            Storage::move($file, $targetDirectory . '/' . $newFileName);
        }
    }
}
