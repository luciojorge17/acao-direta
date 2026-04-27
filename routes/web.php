<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth'])->group(function () {
    Route::get('/usuarios', function () { return view('users.index'); })->name('users.index');
    Route::get('/colaboradores', function () { return view('colaboradores.index'); })->name('colaboradores.index');
    Route::get('/registro-ponto', function () { return view('pontos.index'); })->name('pontos.index');
    Route::get('/relatorios', function () { return view('relatorios.index'); })->name('relatorios.index');
    Route::get('/relatorios/exportar', [\App\Http\Controllers\RelatorioController::class, 'export'])->name('relatorios.exportar');
});
