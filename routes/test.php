<?php

Route::get('/test-db', function () {
    try {
        DB::connection()->getPdo();
        return '✓ Database connected successfully!';
    } catch (\Exception $e) {
        return '✗ Database error: ' . $e->getMessage();
    }
});
