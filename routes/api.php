<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\FbrController;


// Route::get('/save-pdf', [PrintController::class, 'printQT'])->middleware('api');

Route::post('/save-pdf', [PrintController::class, 'printQT']);
Route::post('/send-to-fbr', [FbrController::class, 'receiveInvoiceWebhook']);