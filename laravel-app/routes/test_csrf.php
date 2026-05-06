<?php
Route::get('/test-csrf', function (\Illuminate\Http\Request $request) {
    return [
        'csrf_token' => csrf_token(),
        'session_id' => session()->getId(),
        'auth_check' => auth('pp_admin')->check()
    ];
});
