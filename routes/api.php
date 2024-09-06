<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

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
        return response('Invalid verification token', 403);
    }
    
    return response($challenge, 200)->header('Content-Type', 'text/plain');
});

Route::post('/webhooks', function (Request $request) {
    $requestData = $request->getContent();
    $filename = 'all_requests.txt';

    if (Storage::exists($filename)) {
        Storage::append($filename, PHP_EOL . $requestData);
    } else {
        Storage::put($filename, $requestData);
    }

    
    $url = 'https://graph.facebook.com/v20.0/407727505748964/messages';
    Http::withHeaders([
        'Authorization' => 'Bearer ' . env('WHATSAPP_TOKEN'),
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ])->post($url, [
        "messaging_product" => "whatsapp",
        "recipient_type" => "individual",
        "to" => "5581999775952",
        "type" => "interactive",
        "interactive" => [
            "type" => "list",
            "header" => [
                "type" => "text",
                "text" => "Agradecemos o contato"
            ],
            "body" => [
                "text" => "Bom dia, agradecemos o contato. Para seguir com o atendimento selecione um serviço."
            ],
            "action" => [
                "sections" => [
                    [
                        "title" => "Serviços",
                        "rows" => [
                            [
                                'id' => 'hello_world',
                                'title' => 'Hello World!'
                            ],
                            [
                                'id' => 'test',
                                'title' => 'This is a test!'
                            ],
                            [
                                'id' => 'ok',
                                'title' => 'Ok?'
                            ],
                        ]
                    ]
                ],
                "button" => "Selecionar um serviço",
            ]
        ]
    ]);

    return response()->noContent(200);
});