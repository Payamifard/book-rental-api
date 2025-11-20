<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\RentalController;

Route::middleware('auth:sanctum')->group(function () {
    // books
    Route::get('books', [BookController::class, 'index']);
    Route::get('books/{book}', [BookController::class, 'show']);

    // rentals
    Route::post('rentals', [RentalController::class, 'store']); // create rental
    Route::get('rentals', [RentalController::class, 'index']);  // user's rentals
    Route::get('rentals/{rental}', [RentalController::class, 'show']);
    Route::post('rentals/{rental}/return', [RentalController::class, 'return']); // return books
});
