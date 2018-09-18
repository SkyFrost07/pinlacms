<?php

Route::group([
    'prefix' => 'dictionary',
    'as' => 'word.'
], function () {
    Route::get('/', 'DictController@index')->name('index');
    Route::get('/{word}_w{id}.html', 'DictController@viewWord')->name('view_word');
    Route::post('/make-sentence', 'DictController@makeSentence')->name('make_sentence');
    Route::post('/make-word', 'DictController@makeWord')->name('make_word');

    Route::group([
        'middleware' => 'auth'
    ], function () {
        Route::delete('/words/{id}/delete', 'DictController@deleteWord')->name('delete');
        Route::get('/words/{id}/edit', 'DictController@editWord')->name('edit');
        Route::put('/words/{id}/update', 'DictController@updateWord')->name('update');
    });
});

$manage = config('admin.prefix');
Route::group([
    'prefix' => $manage . '/dicts',
    'middleware' => 'auth',
    'namespace' => 'Admin',
    'as' => 'admin.'
], function () {
    Route::get('/', 'DictController@index')->name('index');
    Route::get('/create', 'DictController@create')->name('create');
    Route::post('/store', 'DictController@store')->name('store');
    Route::get('/{id}/edit', 'DictController@edit')->name('edit');
    Route::put('/{id}/update', 'DictController@update')->name('update');
    Route::post('/actions', 'DictController@multiActions')->name('actions');
});

