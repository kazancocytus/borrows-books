<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BooksController;
use App\Http\Controllers\MembersController;
use App\Http\Controllers\BorrowController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::get('check-members', [MembersController::class, 'check'])->name('check.members');

Route::get('check-books', [BooksController::class, 'check'])->name('check.books');
Route::post('/borrow', [BorrowController::class, 'borrow'])->name('borrow');
Route::post('returns', [BorrowController::class, 'returns'])->name('returns');