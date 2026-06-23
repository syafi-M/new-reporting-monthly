<?php

use App\Http\Controllers\Admin\DataFotoController;
use App\Http\Controllers\API\CalenderApiHandler;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CoverReportControllers;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FixedImageController;
use App\Http\Controllers\FindingController;
use App\Http\Controllers\HandlerCountController;
use App\Http\Controllers\ImageRateController;
use App\Http\Controllers\QrCodeController;
use App\Http\Controllers\ReportLettersControllers;
use App\Http\Controllers\SendImageStatusController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UploadImageController;
use App\Http\Controllers\UploadTambahanCheckController;
use App\Http\Controllers\UploadTambahanController;
use App\Http\Controllers\UserNavigateController;
use App\Http\Controllers\UserSettingsController;
use App\Http\Controllers\Admin\UploadTambahanAdminController;
use App\Http\Controllers\Admin\DownloadRekapController;
use App\Models\Clients;
use App\Models\Kerjasama;
use App\Models\UploadImage;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified', 'theme'])->name('dashboard');
Route::resource('/rating-pekerjaan', ImageRateController::class)
    ->only('create', 'store')
    ->middleware('throttle:20,1');
Route::get('/rating-pekerjaan/terima-kasih', function () {
    return view('pages.user.etc.makasih');
})->name('rating-pekerjaan.makasih');

Route::middleware(['auth', 'theme'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::resource('/upload-img-lap', UploadImageController::class);
    Route::post('/upload-img-lap-draft', [UploadImageController::class, 'draft'])->name('upload-images.draft');
    Route::post('/upload-img-lap/chunk/init', [UploadImageController::class, 'initChunkUpload'])->name('upload-images.chunk.init');
    Route::post('/upload-img-lap/chunk/upload', [UploadImageController::class, 'uploadChunk'])->name('upload-images.chunk.upload');
    Route::post('/upload-img-lap/chunk/finalize', [UploadImageController::class, 'finalizeChunkUpload'])->name('upload-images.chunk.finalize');
    Route::post('/upload-img-lap/chunk/cancel', [UploadImageController::class, 'cancelChunkUpload'])->name('upload-images.chunk.cancel');
    Route::resource('/finding', FindingController::class)->only(['index', 'store']);
    Route::get('/send-img/laporan', [UserNavigateController::class, 'toUploadImgLaporan'])->name('send.img.laporan');
    Route::get('/upload-file-tambahan', [UploadTambahanController::class, 'index'])->name('upload-tambahan.index');
    Route::post('/upload-file-tambahan', [UploadTambahanController::class, 'store'])->name('upload-tambahan.store');
    Route::get('/upload-file-tambahan/detail', [UploadTambahanController::class, 'show'])->name('upload-tambahan.show');
    Route::get('/upload-file-tambahan/detail/show/{id}', [UploadTambahanController::class, 'detail'])->name('upload-tambahan.detail');
    Route::post('/upload-file-tambahan/chunk/init', [UploadTambahanController::class, 'initChunkUpload'])->name('upload-tambahan.chunk.init');
    Route::post('/upload-file-tambahan/chunk/upload', [UploadTambahanController::class, 'uploadChunk'])->name('upload-tambahan.chunk.upload');
    Route::post('/upload-file-tambahan/chunk/finalize', [UploadTambahanController::class, 'finalizeChunkUpload'])->name('upload-tambahan.chunk.finalize');
    Route::post('/upload-file-tambahan/chunk/cancel', [UploadTambahanController::class, 'cancelChunkUpload'])->name('upload-tambahan.chunk.cancel');
    Route::get('/check-upload-tambahan', [UploadTambahanCheckController::class, 'index'])->name('upload-tambahan.check.index');
    Route::get('/api/v1/check-upload-tambahan', [UploadTambahanCheckController::class, 'summary'])->name('api.v1.upload-tambahan.check.summary');
    Route::get('/api/v1/check-upload-tambahan/{userId}', [UploadTambahanCheckController::class, 'detail'])->name('api.v1.upload-tambahan.check.detail');

    Route::resource('/set-image/fixed', FixedImageController::class)->only('index', 'create', 'store', 'destroy');
    Route::post('/set-image/fixed/rate', [FixedImageController::class, 'rate'])->name('fixed.rate');


    // Tools Route
    Route::get('/check-calender', [UserNavigateController::class, 'toCalenderUpload'])->name('check.calender.upload');
    Route::get('/fetch-calender', [CalenderApiHandler::class, 'getCalendarData']);
    Route::get('/settings', [UserNavigateController::class, 'toSettings'])->name('user.settings.index');
    Route::post('/save-settings', [UserSettingsController::class, 'store']);
    // End Tools Route

    Route::get('/count-data/data-verifikasi', [HandlerCountController::class, 'index'])->name('counting.data.upload.spv');
    Route::get('/count-per-user/{id}/{month}/{year}', function () {
        return view('pages.user.counting.show');
    })->name('count.per.user.show');


    //API HANDLE COUNT
    Route::get('/api/v1/count-data-spv', [HandlerCountController::class, 'getCountJatim'])->name('api.v1.count.data.upload.spv');
    Route::get('/api/v1/count-per-user/{id}/{month}/{year}', [HandlerCountController::class, 'show'])->name('api.v1.count.per.user');
    Route::get('/api/v1/count-data', [UploadImageController::class, 'countData'])->name('v1.count.data');
    Route::get('/api/v1/count-fixed-image', [FixedImageController::class, 'getCountFixed'])->name('v1.count.fixed.image');
    Route::get('/api/v1/calender-show', [CalenderApiHandler::class, 'modalShow'])->name('api.v1.calender.show');
    // END HANDLE
});

Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::resource('/admin-covers', CoverReportControllers::class);
    Route::post('admin-covers/store-pdf', [CoverReportControllers::class, 'store_pdf']);
    Route::resource('/admin-latters', ReportLettersControllers::class);
    Route::get('/admin-check-status', [SendImageStatusController::class, 'index'])->name('check.upload');
    Route::get('/admin-check-status/{admin_check_status}/{client}/{month}/{year}', [SendImageStatusController::class, 'show'])->name('check.upload.show');

    Route::get('admin/upload/get-pdf-data', [UploadImageController::class, 'getPdfData'])->name('admin.upload.get-pdf-data');
    Route::post('admin/upload/store-pdf', [UploadImageController::class, 'storePdf'])->name('admin.upload.store-pdf');
    Route::get('/admin-settings', [SettingsController::class, 'index'])->name('admin.settings');
    Route::post('/admin-settings', [SettingsController::class, 'store'])->name('admin.set.settings');

    Route::get('/admin-foto-progress', [DataFotoController::class, 'index'])->name('admin.upload.index');
    Route::get('/admin-foto-progress/{upload_image}', [DataFotoController::class, 'show'])->name('admin.upload.show');
    Route::put('/admin-foto-progress-update/{upload_image}', [DataFotoController::class, 'update'])->name('admin.upload.update');
    Route::delete('/admin-foto-progress-delete/{upload_image}', [DataFotoController::class, 'destroy'])->name('admin.upload.destroy');
    Route::post('/admin-foto-progress-mass-delete', [DataFotoController::class, 'massDelete'])->name('admin.upload.mass-delete');
    Route::get('/admin/upload/get-users', [DataFotoController::class, 'getUsers'])->name('admin.upload.get-users');

    Route::resource('/admin-qrcode', QrCodeController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    Route::get('/admin-finding', [FindingController::class, 'adminIndex'])->name('admin.finding.index');
    Route::delete('/admin-finding/{finding}', [FindingController::class, 'adminDestroy'])->name('admin.finding.destroy');

    Route::resource('/admin-rating-image', ImageRateController::class)->only(['index', 'edit', 'update', 'destroy']);
    Route::get('/admin-upload-tambahan', [UploadTambahanAdminController::class, 'index'])->name('admin.upload-tambahan.index');
    Route::get('/admin-upload-tambahan/{id}', [UploadTambahanAdminController::class, 'show'])->name('admin.upload-tambahan.show');
    Route::get('/admin-download-rekap', [DownloadRekapController::class, 'index'])->name('admin.download-rekap.index');
    Route::post('/admin-download-rekap/generate', [DownloadRekapController::class, 'generate'])->name('admin.download-rekap.generate');

    // Route Handle AJAX API GET
    Route::get('/api/v1/admin/admin-check-status/detail/{user_id}/{month}/{year}', [SendImageStatusController::class, 'getDetailFixed'])->name('admin.api.v1.check.detail');
});

Route::view('/testPage', 'pages.admin.tesPages');


require __DIR__ . '/auth.php';
