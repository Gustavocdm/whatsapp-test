<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/test', function (Request $request) {
    return $request->all();
});

Route::get('/webhooks', function (Request $request) {
    $challenge = $request->get('hub_challenge');
    $tokenEnviado = $request->get('hub_verify_token');

    $token = env('WHATSAPP_WEBHOOK_VERIFY_TOKEN');

    if ($tokenEnviado == null || $challenge == null || $token != $tokenEnviado) {
        return response([
            "error" => "Token doens't match."
        ], 400);
    }
    
    return response([
        "hub.challenge" => $challenge
    ]);
});
