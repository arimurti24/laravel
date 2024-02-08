<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\TransactionController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::resource('employee',EmployeeController::class);
Route::resource('transaction',TransactionController::class);
Route::resource('product',ProductController::class);
Route::get('productCategory', [ProductController::class, 'getCategory']);
Route::post('/create-transaction', [TransactionController::class, 'createTransaction']);
Route::post('/get-payment', [TransactionController::class, 'getPayment']);
Route::get('/get-transaction/{id}', [TransactionController::class, 'getTransaction']);
Route::get('/get-detail-transaction/{id}', [TransactionController::class, 'getDetailTransaction']);