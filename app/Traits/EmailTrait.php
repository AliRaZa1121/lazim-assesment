<?php

namespace App\Traits;

use Illuminate\Support\Facades\Mail;

trait EmailTrait
{
    public function sendMail($data, $view)
    {
        try {
            Mail::send($view, $data, function ($message) use ($data) {
                $message->subject($data['subject']);
                $message->from(env('MAIL_FROM_ADDRESS', 'notifications@lazim.ae'), env('MAIL_FROM_NAME', 'Lazim Application'));
                $message->to($data['email']);
            });
            return true;
        } catch (\Throwable $th) {
            return false;
        }
    }
}
