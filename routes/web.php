<?php

use App\Http\Controllers\GuruController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SiswaController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/news', [NewsController::class, 'index'])->name('news');

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

// Route khusus untuk role "siswa"
Route::group(['middleware' => ['auth', 'role:siswa']], function () {
    Route::get('/siswa/index', [SiswaController::class, 'index'])->name('siswa.index');
    // Tambahkan route lain khusus siswa di sini
});

// Route khusus untuk role "guru"
Route::group(['middleware' => ['auth', 'role:guru']], function () {
    Route::get('/guru/index', [GuruController::class, 'index'])->name('guru.index');
     // Tambahkan route lain khusus guru di sini
});

// SISWA CONTROLLER ---
//Route::get('/siswa/create', [SiswaController::class, 'create'])->name('siswa.create');
//Route::post('/siswa/store', [SiswaController::class, 'store'])->name('siswa.store');
Route::resource('siswa', SiswaController::class);

// GURU CONTROLLER ---
Route::delete('/guru/delete/{Siswa}', [SiswaController::class, 'destroy'])->name('guru.destroy');
Route::patch('/siswa/{id}/complete', [SiswaController::class, 'selesai'])->name('siswa.complete');
Route::get('/siswa', [SiswaController::class, 'index'])->name('siswa.index');
