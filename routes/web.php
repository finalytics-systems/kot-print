<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/save-pdf', function () {
    $data = ['title' => 'Welcome to My Site', 'content' => 'This is a sample PDF.'];

    // Initialize DOMPDF
    $pdf = PDF::loadView('qt', $data)->setOption(['dpi' => 203, 'defaultFont' => 'sans-serif']);
    Storage::put('invoice/myPDFFile1.pdf', $pdf->output());

    return "PDF has been saved with custom dimensions and DPI";
});

Route::get('/execute-script', function () {
    $scriptPath = storage_path('app/invoice/print_invoices_by_kitchen.ps1');

    // Command to execute the PowerShell script
    $command = "powershell -ExecutionPolicy Bypass -File \"$scriptPath\"";

    // Execute the command
    $output = shell_exec($command);
});