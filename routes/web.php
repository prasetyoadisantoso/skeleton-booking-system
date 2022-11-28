<?php

use App\Http\Controllers\Authentication\AuthController;
use App\Http\Controllers\Dashboard\GeneralController;
use App\Http\Controllers\Dashboard\MainController;
use App\Http\Controllers\Dashboard\MetaController;
use App\Http\Controllers\Dashboard\PermissionController;
use App\Http\Controllers\Dashboard\RoleController;
use App\Http\Controllers\Dashboard\SocialMediaController;
use App\Http\Controllers\Dashboard\UserController;
use App\Http\Controllers\TestController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
 */

/**
 * Development - Production Mode
 */
Route::group([
    'prefix' => LaravelLocalization::setlocale(),
    'middleware' => ['localeSessionRedirect', 'localizationRedirect'],
], function () {

    // Home Page
    Route::get('/', function () {
        return view('welcome');
    })->name('site.index');

    // Authentication
    Route::prefix('authentication')->group(function () {
        Route::get('login/page', [AuthController::class, 'login_page'])->name('login.page');
        Route::get('register/page', [AuthController::class, 'register_page'])->name('register.page');
        Route::get('forgot_password/page', [AuthController::class, 'forgot_password_page'])->name('forgot.password.page');
        Route::get('reset_password/page/{token}', [AuthController::class, 'reset_password_page'])->name('reset.password.page');
        Route::post('register/{type}', [AuthController::class, 'register_client'])->name('register');
        Route::get('resend_verification/page', [AuthController::class, 'resend_verification_page'])->name('resend.verification.page');
        Route::post('resend_verification', [AuthController::class, 'resend_verification'])->name('resend.verification');
        Route::get('verify/{token}', [AuthController::class, 'verify'])->name('verify');
        Route::post('forgot_password', [AuthController::class, 'forgot_password'])->name('forgot.password');
        Route::post('reset_password', [AuthController::class, 'reset_password'])->name('reset.password');
        Route::post('login', [AuthController::class, 'login'])->name('login');
        Route::get('logout', function () {
            Session::flush();
            Auth::logout();
            return redirect(url('/'));
        })->name('logout');
    });

    // Dashboard
    Route::prefix('dashboard')->group(function () {
        Route::get('main', [MainController::class, 'index'])->name('dashboard.main');

        // User & Permissions
        Route::resource('user', UserController::class);
        Route::get('user_datatable', [UserController::class, 'index_dt'])->name('user.datatable');
        Route::resource('permission', PermissionController::class);
        Route::get('permission_datatable', [PermissionController::class, 'index_dt'])->name('permission.datatable');
        Route::resource('role', RoleController::class);
        Route::get('role_datatable', [RoleController::class, 'index_dt'])->name('role.datatable');

        // Settings
        Route::get('general', [GeneralController::class, 'index'])->name('general.index');
        Route::post('general/description/update', [GeneralController::class, 'update_site_description'])->name('general.update.description');
        Route::post('general/logo_favicon/update', [GeneralController::class, 'update_site_logo_favicon'])->name('general.update.logo.favicon');
        Route::resource('meta', MetaController::class);
        Route::get('meta_datatable', [MetaController::class, 'index_dt'])->name('meta.datatable');
        Route::resource('social_media', SocialMediaController::class);
        Route::get('social_media_dt', [SocialMediaController::class, 'index_dt'])->name('social_media.datatable');
    });

});

/**
 * Testing Mode
 */
Route::group([
    // 'prefix' => LaravelLocalization::setlocale(),
    // 'middleware' => ['localeSessionRedirect', 'localizationRedirect'],
], function () {
    # Place testing code here
    # <code>
    Route::prefix('testing')->group(function () {
        Route::get('create-form', [TestController::class, 'create'])->name('test.create');
        Route::post('create-form/store', [TestController::class, 'store'])->name('test.store');
    });

    # Email template testing
    Route::get('/send-verification', function () {
        return new App\Mail\SendVerification("1111");
    });

});
