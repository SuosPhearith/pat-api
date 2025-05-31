<?php

namespace App\Http\Controllers\Testing\ExternalService;

// ============================================================================>> Core Library
use Illuminate\Http\Request; // For Getting requested Payload from Client
use Illuminate\Http\Response; // For Responsing data back to Client
use Illuminate\Support\Facades\Http; // For Calling External Service


class TelegramController
{

    public function sendMessage(Request $req){

        // ===>> Get Credentail from ENV Variable
        $botToken  = env('TELEGRAM_BOT_TOKEN');
        $chatID    = env('TELEGRAM_CHAT_ID');

        // ===>> Send Request to Telegram
        $res = Http::get("https://api.telegram.org/$botToken/sendMessage", [
            'chat_id' => $chatID,
            'text' => $req->text
        ]);

        // ===>> Success Response Back to Client
        return response()->json($res, Response::HTTP_OK);

    }

    public function sendPhoto(Request $req){

        // Check Validation
        $req->validate([
            'photo' => 'required|file|max:51200', //50MB
        ]);



        if($req->has('photo')){

            // ===>> Get Credentail from ENV Variable
            $botToken  = env('TELEGRAM_BOT_TOKEN');
            $chatID    = 229388689;

            // ===>> Send Request to Telegram
            $res = Http::asForm()->post("https://api.telegram.org/$botToken/sendPhoto", [
                'chat_id' => $chatID,
                'photo' => $req->photo
            ])
            ;

            // ===>> Success Response Back to Client
            return response()->json($res, Response::HTTP_OK);

        }else{
            return $req;
        }





    }

    public function sendLocation(Request $req){

         // ===>> Get Credentail from ENV Variable
         $botToken  = env('TELEGRAM_BOT_TOKEN');
         $chatID    = env('TELEGRAM_CHAT_ID');

         // ===>> Send Request to Telegram
         $res = Http::get("https://api.telegram.org/$botToken/sendLocation", [
             'chat_id'      => $chatID,
             'latitude'     => $req->latitude,
             'longitude'    => $req->longitude
         ]);

         // ===>> Success Response Back to Client
         return response()->json($res, Response::HTTP_OK);
    }




}


