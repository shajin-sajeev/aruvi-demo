<?php

use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PolicyPageController;
use App\Http\Controllers\Admin\ResourceController;
use App\Http\Controllers\Admin\RolePermissionController;
use App\Http\Controllers\Admin\ThemeCustomizationController;
use App\Http\Controllers\Admin\UserApprovalController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\FrontendController;
use Illuminate\Support\Facades\Route;

/* ── Frontend ─────────────────────────────────────────────── */
Route::get('/', [FrontendController::class, 'home'])->name('home');
Route::get('/login', fn () => redirect()->route('admin.login'))->name('login');
Route::get('/about-us', fn () => redirect()->to('/#about'))->name('about');
Route::get('/resort-overview-amenities', fn () => redirect()->to('/#amenities'))->name('amenities');
Route::get('/rooms-suites', [FrontendController::class, 'rooms'])->name('rooms.index');
Route::get('/rooms-suites/{room:slug}', [FrontendController::class, 'room'])->name('rooms.show');
Route::get('/restaurant-menu', fn () => redirect()->to('/#dining'))->name('restaurant');
Route::get('/gallery', fn () => redirect()->to('/#gallery'))->name('gallery');
Route::get('/location-nearby-attractions', fn () => redirect()->to('/#contact'))->name('location');
Route::get('/reviews-feedback', fn () => redirect()->to('/#reviews'))->name('reviews');
Route::post('/reviews-feedback', [FrontendController::class, 'storeReview'])->name('reviews.store');
Route::get('/contact-us', fn () => redirect()->to('/#contact'))->name('contact');
Route::post('/contact-us', [FrontendController::class, 'storeContact'])->name('contact.store');
Route::get('/faq', fn () => redirect()->to('/#faq'))->name('faq');
Route::post('/newsletter', [FrontendController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/policies/{slug}', [FrontendController::class, 'policy'])->name('policies.show');

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');

/* ── Auth (guest only) ────────────────────────────────────── */
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login',    [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login',   [AuthController::class, 'login'])->name('login.store')->middleware('throttle:5,1');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register',[AuthController::class, 'register'])->name('register.store')->middleware('throttle:3,1');
});

/* ── Admin panel ──────────────────────────────────────────── */
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {

    /* Dashboard */
    Route::get('/', DashboardController::class)->name('dashboard');

    /* Profile */
    Route::get('/profile',          [ProfileController::class, 'show'])->name('profile');
    Route::patch('/profile',        [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password',[ProfileController::class, 'updatePassword'])->name('profile.password');

    /* Theme customization — super-admin or permission */
    Route::get('/theme-customization',  [ThemeCustomizationController::class, 'show'])->name('theme.customization');
    Route::post('/theme-customization', [ThemeCustomizationController::class, 'store'])->name('theme.customization.store');

    /* Policies */
    Route::get('/policies',                   [PolicyPageController::class, 'index'])->name('policies.index');
    Route::get('/policies/create/{slug}',     [PolicyPageController::class, 'create'])->name('policies.create');
    Route::post('/policies',                  [PolicyPageController::class, 'store'])->name('policies.store');
    Route::get('/policies/{page}/edit',       [PolicyPageController::class, 'edit'])->name('policies.edit');
    Route::patch('/policies/{page}',          [PolicyPageController::class, 'update'])->name('policies.update');
    Route::delete('/policies/{page}',         [PolicyPageController::class, 'destroy'])->name('policies.destroy');

    /* Messages */
    Route::get('/messages',                          [\App\Http\Controllers\Admin\ContactMessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/{message}',                [\App\Http\Controllers\Admin\ContactMessageController::class, 'show'])->name('messages.show');
    Route::patch('/messages/{message}/read',         [\App\Http\Controllers\Admin\ContactMessageController::class, 'markAsRead'])->name('messages.read');
    Route::delete('/messages/{message}',             [\App\Http\Controllers\Admin\ContactMessageController::class, 'destroy'])->name('messages.destroy');

    /* ── Super-admin only: user approvals ─────────────────── */
    Route::get('/approvals',                         [UserApprovalController::class, 'index'])->name('approvals.index');
    Route::patch('/approvals/{user}/approve',        [UserApprovalController::class, 'approve'])->name('approvals.approve');
    Route::delete('/approvals/{user}/reject',        [UserApprovalController::class, 'reject'])->name('approvals.reject');
    Route::patch('/approvals/{user}/suspend',        [UserApprovalController::class, 'suspend'])->name('approvals.suspend');
    Route::patch('/approvals/{user}/reactivate',     [UserApprovalController::class, 'reactivate'])->name('approvals.reactivate');

    /* ── Super-admin only: role & permission management ────── */
    Route::get('/role-permissions',                  [RolePermissionController::class, 'index'])->name('role-permissions.index');
    Route::post('/role-permissions/roles',           [RolePermissionController::class, 'store'])->name('role-permissions.store');
    Route::patch('/role-permissions/roles/{role}',   [RolePermissionController::class, 'update'])->name('role-permissions.update');
    Route::delete('/role-permissions/roles/{role}',  [RolePermissionController::class, 'destroy'])->name('role-permissions.destroy');

    /* ── Generic resource CRUD ────────────────────────────── */
    Route::get('/{resource}',           [ResourceController::class, 'index'])->name('resources.index');
    Route::get('/{resource}/create',    [ResourceController::class, 'create'])->name('resources.create');
    Route::post('/{resource}',          [ResourceController::class, 'store'])->name('resources.store');
    Route::get('/{resource}/{id}/edit', [ResourceController::class, 'edit'])->name('resources.edit');
    Route::patch('/{resource}/{id}',    [ResourceController::class, 'update'])->name('resources.update');
    Route::delete('/{resource}/{id}',   [ResourceController::class, 'destroy'])->name('resources.destroy');
});
