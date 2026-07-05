<?php

use App\Http\Controllers\website\AboutController;
use App\Http\Controllers\website\ApplyFormController;
use App\Http\Controllers\website\BlogController;
use App\Http\Controllers\website\ContactController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\website\HomeController;
use App\Http\Controllers\website\JobController;
use App\Http\Controllers\website\OurServiceController;
use App\Http\Controllers\website\SelectController;

Route::group(
    [
        'prefix' => '/hhhh',
        'as' => 'web-'
    ],
    function () {
        Route::get('/verifyDoc', [HomeController::class, 'verifyDoc'])->name('verifyDoc');
        Route::get('/', [HomeController::class, 'index'])->name('index');

        Route::get('/internship', [JobController::class, 'job'])->name('job');
        Route::get('/internship/detail/{id}', [JobController::class, 'jobDetail'])->name('job-detail');

        Route::get('/blog', [BlogController::class, 'blog'])->name('blog');
        Route::get('/blog/detail/{id}', [BlogController::class, 'blogDetail'])->name('blog-detail');
       
        Route::get('/contact', [ContactController::class, 'contact'])->name('contact');
        Route::post('/contact/send', [ContactController::class, 'contactSend'])->name('contact-send');

        Route::get('/our-service', [OurServiceController::class, 'ourService'])->name('our-service');

        Route::get('/about', [AboutController::class, 'about'])->name('about');

        Route::post('/upload', [HomeController::class, 'upload'])->name('upload');

        Route::get('/userIndex', [HomeController::class, 'userIndex'])->name('userIndex');
        Route::get('/userData', [HomeController::class, 'userData'])->name('user-data');


        Route::get('/apply/form/{id?}', [ApplyFormController::class, 'applyForm'])->name('apply-form');
        Route::post('/apply', [ApplyFormController::class, 'apply'])->name('apply');

        //Position
        Route::get('/select/position', [SelectController::class, 'SelectPositionSearch'])->name('select-position');
        //Sector
        Route::get('/select/sector', [SelectController::class, 'SelectSectorSearch'])->name('select-sector');
        Route::get('/select/internship', [SelectController::class, 'SelectJobSearch'])->name('select-job');
        

    }
);