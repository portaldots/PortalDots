<?php

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
        Route::get('/mail/test', 'Install\Mail\TestAction')->name('mail.test');
        Route::post('/mail/test', 'Install\Mail\PostTestAction');
    });
