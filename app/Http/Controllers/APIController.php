<?php

namespace App\Http\Controllers;

use App\Models\Email;
use Illuminate\Http\Request;

class APIController extends Controller
{
    public function createEmail(Request $request){
        
        // create the temp email
        if ($request->input('address') == null) {
            if (empty($email)) {
                do {
                    $nickname = Email::random_words(1,rand(6,15)).'@mx.getmailet.com';
                    $emails_same_nickname = Email::where('email',$nickname)->count();
                } while ($emails_same_nickname!=0);
                $email = new Email;
                $email->email = $nickname;
                $email->user_id =\Auth::user()->id;
                $email->save();
            }
        }else {
            if (empty($email)) {
                $nickname = $request->input('address').'@mx.getmailet.com';
                $emails_same_nickname = Email::where('email',$nickname)->first();
                if (empty($emails_same_nickname)) {
                    $email = new Email;
                    $email->email = $nickname;
                    $email->user_id =\Auth::user()->id;
                    $email->save();
                }else {
                    return response()->json(['error'=>'Username already exist.', 'success'=>false]);
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
         // If you want to see the messages in the inbox, you need to set on url post /inbox/?email=your_email
         if (empty($request->email)) {
             $emails = Email::where("user_id", \Auth::user()->id)->select('email')->get();
             foreach ($emails as $e) {
                 Message::saveMessagesReceived($e->email);
                 $messages = Message::where('email', $e->email)->select('message_id', 'from', 'subject', 'date', 'id', 'body')->orderByDesc('created_at')->whereNull('deleted_at')->get();
                 $email_messages[$e->email] = !empty($messages->first()) ? $messages : "No messages.";
             }
 
             $data = [
                 'message' => $email_messages,
                 'success' => true
             ];
         }else {
             Message::saveMessagesReceived($request->email);
             $messages = Message::where('email',$request->email)->select('message_id', 'email', 'from', 'subject', 'date', 'id', 'body')->orderByDesc('created_at')->whereNull('deleted_at')->get();
             
             $data = [
                 'message' => !empty($messages->first()) ? $messages : "No messages." ,
                 'success' => true
             ];
         }
 
         return response()->json($data);
     }
}
