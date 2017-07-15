<?php

$admin = [
    'prefix' => 'admin',
    'namespace' => 'QuetzalArc\Admin\Tag',
];

Route::group($admin, function() {
    Route::get('/tags', 'TagController@index');
    Route::post('/tags', 'TagController@store');
    Route::get('/tags/{id}/edit', 'TagController@edit');
    Route::patch('/tags/{id}', 'TagController@update');
    Route::delete('/tags/{id}', 'TagController@delete');
});