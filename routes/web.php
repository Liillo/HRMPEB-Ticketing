<?php

use App\Http\Controllers\TicketController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\EventController;
use Illuminate\Support\Facades\Route;

// Public Routes - Events and Booking
Route::get('/', [TicketController::class, 'index'])->name('home');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::get('/event/{event}/booking-type', [TicketController::class, 'bookingType'])->name('booking.type');
Route::get('/event/{event}/individual-booking', [TicketController::class, 'individualBooking'])->name('booking.individual');
Route::get('/event/{event}/corporate-booking', [TicketController::class, 'corporateBooking'])->name('booking.corporate');
Route::post('/individual-booking', [TicketController::class, 'storeIndividual'])->name('booking.individual.store');
Route::post('/corporate-booking', [TicketController::class, 'storeCorporate'])->name('booking.corporate.store');

// Payment Routes
Route::get('/payment', [TicketController::class, 'createPayment'])->name('payment.create');
Route::get('/payment/pending', [TicketController::class, 'pendingPaymentForm'])->name('payment.pending.form');
Route::post('/payment/pending', [TicketController::class, 'pendingPayment'])->name('payment.pending');
Route::get('/payment/{ticket}', [TicketController::class, 'payment'])->name('payment');
Route::post('/payment/{ticket}/initiate', [TicketController::class, 'initiatePayment'])->name('payment.initiate');
Route::get('/waiting/{ticket}', [TicketController::class, 'waiting'])->name('payment.waiting');
Route::get('/check-payment/{ticket}', [TicketController::class, 'checkPaymentStatus'])->name('payment.check');
Route::get('/success/{ticket}', [TicketController::class, 'success'])->name('payment.success');

// Ticket Routes
Route::get('/ticket/retrieve', [TicketController::class, 'retrieveForm'])->name('ticket.retrieve.form');
Route::post('/ticket/retrieve', [TicketController::class, 'retrieveTicket'])->name('ticket.retrieve');
Route::get('/ticket/{uuid}', [TicketController::class, 'show'])->name('ticket.show');
Route::get('/ticket/{uuid}/download', [TicketController::class, 'download'])->name('ticket.download');
Route::get('/ticket/{uuid}/validate', [TicketController::class, 'viewValidation'])->name('ticket.validate');

// Admin Routes
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AdminController::class, 'showLogin'])->name('login');
    Route::post('/login', [AdminController::class, 'login'])->name('login.post');
    
    Route::middleware(['auth', 'admin'])->group(function () {
        Route::post('/logout', [AdminController::class, 'logout'])->name('logout');
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
        Route::get('/search', [AdminController::class, 'search'])->name('search');
        Route::get('/tickets/{id}', [AdminController::class, 'ticketDetail'])->name('ticket.detail');
        Route::get('/tickets/{id}/download', [AdminController::class, 'downloadTicket'])->name('ticket.download');
        Route::post('/tickets/{id}/resend', [AdminController::class, 'resendTicket'])->name('ticket.resend');
        Route::get('/validation', [AdminController::class, 'validation'])->name('validation');
        Route::post('/scan', [AdminController::class, 'scanTicket'])->name('scan');
        
        Route::resource('events', EventController::class)->except(['index', 'show']);
        Route::get('/events', [EventController::class, 'index'])->name('events.index');
        Route::get('/events/{event}', [EventController::class, 'show'])->name('events.show');
        Route::post('/events/{id}/toggle-status', [EventController::class, 'toggleStatus'])->name('events.toggle');
    });
});
