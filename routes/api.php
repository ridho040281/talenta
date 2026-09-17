<?php

use App\Http\Controllers\BadmintonMatchController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Badminton Real-time High-Speed Stateless API
|--------------------------------------------------------------------------
| No session locking, no cookie overhead, sub-millisecond execution.
*/
Route::prefix('badminton')->name('api.badminton.')->group(function () {
    Route::get('/matches/{id}/state', [BadmintonMatchController::class, 'apiState'])->name('state');
    Route::post('/matches/{id}/score', [BadmintonMatchController::class, 'apiScore'])->name('score');
    Route::get('/active-courts', [BadmintonMatchController::class, 'apiActiveCourts'])->name('active_courts');
});
