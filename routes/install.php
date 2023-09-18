<?php

use Illuminate\Support\Facades\Route;

Route::prefix('/install')
    ->name('install.')
    ->group(function () {
        Route::get('/', 'Install\HomeAction')->name('index');
        Route::get('/portal', 'Install\Portal\EditAction')->name('portal.edit');
        Route::patch('/portal', 'Install\Portal\UpdateAction')->name('portal.update');
        Route::get('/database', 'Install\Database\EditAction')->name('database.edit');
        Route::patch('/database', 'Install\Database\UpdateAction')->name('database.update');
        Route::get('/mail', 'Install\Mail\EditAction')->name('mail.edit');
        Route::patch('/mail', 'Install\Mail\UpdateAction')->name('mail.update');
        Route::post('/mail/send_test', 'Install\Mail\SendTestAction')->name('mail.send_test');
        Route::get('/admin', 'Install\Admin\CreateAction')->name('admin.create');
        Route::post('/admin', 'Install\Admin\StoreAction')->name('admin.store');
    });
