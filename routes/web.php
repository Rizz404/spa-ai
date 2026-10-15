<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\HandoverController;
use App\Http\Controllers\Admin\KnowledgeItemController;
use App\Http\Controllers\Admin\ReservationController as AdminReservationController;
use App\Http\Controllers\Admin\TreatmentController;
use App\Http\Controllers\Admin\TreatmentRoomController;
use App\Http\Controllers\Api\ConciergeChatController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\SpaPageController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SpaPageController::class, 'index'])->name('home');

Route::prefix('admin')->name('admin.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [LoginController::class, 'create'])->name('login');
        Route::post('login', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.store');
    });

    Route::middleware('auth')->group(function () {
        Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        Route::resource('treatments', TreatmentController::class)->except('show');
        Route::resource('treatment-rooms', TreatmentRoomController::class)->except('show');
        Route::resource('knowledge-items', KnowledgeItemController::class)->except('show');

        Route::get('reservations', [AdminReservationController::class, 'index'])->name('reservations.index');
        Route::patch('reservations/{reservation}/status', [AdminReservationController::class, 'updateStatus'])->name('reservations.status');

        Route::get('handovers', [HandoverController::class, 'index'])->name('handovers.index');
        Route::get('handovers/{handover}', [HandoverController::class, 'show'])->name('handovers.show');
        Route::post('handovers/{handover}/reply', [HandoverController::class, 'reply'])->name('handovers.reply');
        Route::post('handovers/{handover}/resolve', [HandoverController::class, 'resolve'])->name('handovers.resolve');
    });
});

Route::prefix('{spaSlug}')->middleware('spa')->group(function () {
    Route::get('/', [SpaPageController::class, 'show'])->name('spa.show');
    Route::get('treatments', [SpaPageController::class, 'treatments'])->name('spa.treatments');
    Route::get('treatments/{itemSlug}', [SpaPageController::class, 'treatment'])->name('spa.treatment');
    Route::get('rooms', [SpaPageController::class, 'rooms'])->name('spa.rooms');
    Route::get('rooms/{areaSlug}', [SpaPageController::class, 'treatmentRoom'])->name('spa.treatment-room');
    Route::get('facilities', [SpaPageController::class, 'facilities'])->name('spa.facilities');
    Route::get('facilities/{facilityId}', [SpaPageController::class, 'facility'])->whereNumber('facilityId')->name('spa.facility');
    Route::get('info', [SpaPageController::class, 'info'])->name('spa.info');
    Route::get('staff', [SpaPageController::class, 'staff'])->name('spa.staff');
    Route::get('reservation', [SpaPageController::class, 'reservationScene'])->name('spa.reservation');

    Route::prefix('reservation')->name('reservation.')->middleware('throttle:20,1')->group(function () {
        Route::post('quote', [ReservationController::class, 'quote'])->name('quote');
        Route::post('/', [ReservationController::class, 'store'])->name('store');
    });

    Route::prefix('concierge')->name('concierge.')->group(function () {
        Route::post('start', [ConciergeChatController::class, 'start'])->middleware('throttle:concierge-start')->name('start');
        Route::post('message', [ConciergeChatController::class, 'message'])->middleware('throttle:concierge-message')->name('message');
        Route::get('history', [ConciergeChatController::class, 'history'])->middleware('throttle:concierge-history')->name('history');
        Route::post('speech', [ConciergeChatController::class, 'speech'])->middleware('throttle:concierge-speech')->name('speech');
    });
});
