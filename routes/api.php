<?php

use App\Http\Controllers\Auth\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('/auth')->group(function () {
  Route::post('/login', [AuthController::class, 'login'])->name('auth.login');
  Route::post('/register', [AuthController::class, 'register'])->name('auth.register');
  Route::post('/logout', [AuthController::class, 'logout'])->name('auth.logout')->middleware('auth:sanctum');
});

// Route::middleware('auth:sanctum')->group(function () {
//     Route::prefix('/tools')->group(function () {
//         Route::post('/', [ToolController::class, 'store'])->name('tools.store');
//         Route::get('/', [ToolController::class, 'index'])->name('tools.index');
//         Route::get('/{tool}', [ToolController::class, 'show'])->name('tools.show');
//         Route::delete('/{tool}', [ToolController::class, 'destroy'])->name('tools.destroy');
//         Route::put('/{tool}', [ToolController::class, 'update'])->name('tools.update');
//     });
// });