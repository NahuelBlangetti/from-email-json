<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
    use HasFactory;
    use SoftDeletes;

    public static function saveMessagesReceived($email){
        $now = \Carbon\Carbon::now();
        $startOfDay = $now->startOfDay()->format('D, j M Y H:i:s O');
        $endOfDay = $now->endOfDay()->format('D, j M Y H:i:s O');
        $mg = \Mailgun\Mailgun::create(env('MAILGUN_API_KEY'));
        $items = \App\Models\Email::get_emails_received_($email,$startOfDay,$endOfDay,$mg);
        foreach ($items as $item) {
            $messages[] = \App\Models\Email::get_email_($item->getMessage()['headers']['message-id'],$mg);
        }
        if (!empty($messages)) {
            foreach ($messages as $message) {
                if (!empty($message['Message-Id'])) {
                    if (!empty($message['body-html'])) {
                        $m = \App\Models\Message::where('message_id',str_replace(array('<','>'),'',$message['Message-Id']))->withTrashed()->first();
                        if (is_null($m)) {
                            $ms = new \App\Models\Message;
                            $ms->email = $email;
                            $ms->from = $message['From'];
                            $ms->subject = $message['Subject'];
                            $ms->date = $message['Date'];
                            $ms->message_id = str_replace(array('<','>'),'',$message['Message-Id']);
                            $ms->body = serialize($message['body-html']);
                            $ms->user_id = \Auth::user()->id;
                            $ms->save();
                        }
                    }
                }
            }
        }
    }
}
