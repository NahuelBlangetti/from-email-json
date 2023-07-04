<?php

namespace App\Http\Controllers;

use App\Models\Email;
use App\Models\Message;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Mailgun\Mailgun;

class APIController extends Controller
{
    public function createEmail(Request $request)
    {
        // create the temp email
        if ($request->input('address') == null) {
            if (empty($email)) {
                do {
                    $nickname = Email::random_words(1, rand(6, 15)) . '@mx.getmailet.com';
                    $emails_same_nickname = Email::where('email', $nickname)->count();
                } while ($emails_same_nickname != 0);
                $email = new Email;
                $email->email = $nickname;
                $email->user_id = $request->input('user_id');
                $email->save();
            }
        } else {
            if (empty($email)) {
                $nickname = $request->input('address') . '@mx.getmailet.com';
                $emails_same_nickname = Email::where('email', $nickname)->first();
                if (empty($emails_same_nickname)) {
                    $email = new Email;
                    $email->email = $nickname;
                    $email->user_id = $request->input('user_id');
                    $email->save();
                } else {
                    return response()->json(['error' => 'Username already exist.', 'success' => false]);
                }
            }
        }

        $data = [
            'email' => $nickname,
            'success' => true
        ];

        return response()->json($data);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function inbox(Request $request)
    {
        // If you want to see the messages in the inbox, you need to set on url post /inbox/?address=your_email
        if (!empty($request->address)) {

            $this->saveMessagesReceived($request->address);
            $messages = Message::where('email', $request->address)->select('message_id', 'email', 'from', 'subject', 'date', 'body')->orderByDesc('created_at')->get();
            $data = [
                'message' => !empty($messages->first()) ? $messages : "No messages.",
                'success' => true
            ];
        }else

        return response()->json($data);
    }

    public function saveMessagesReceived($email)
    {
        $now = Carbon::now();
        $startOfDay = $now->startOfDay()->format('D, j M Y H:i:s O');
        $endOfDay = $now->endOfDay()->format('D, j M Y H:i:s O');
        $mg = Mailgun::create(env('MAILGUN_API_KEY'));
        $items = Email::get_emails_received_($email, $startOfDay, $endOfDay, $mg);
        
        foreach ($items as $item) {
            $messages[] = Email::get_email_($item->getMessage()['headers']['message-id'], $mg);
        }
        if (!empty($messages)) {
            foreach ($messages as $message) {
                if (!empty($message['Message-Id'])) {
                    if (!empty($message['body-html'])) {
                        $m = Message::where('message_id', str_replace(array('<', '>'), '', $message['Message-Id']))->first();
                        if (is_null($m)) {
                            $ms = new Message ;
                            $ms->email = $email;
                            $ms->from = $message['From'];
                            $ms->subject = $message['Subject'];
                            $ms->date = $message['Date'];
                            $ms->message_id = str_replace(array('<', '>'), '', $message['Message-Id']);
                            $ms->body = serialize($message['body-html']);
                            $ms->save();
                        }
                    }
                }
            }
        }
    }
}
