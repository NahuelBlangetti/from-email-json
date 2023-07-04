<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Email extends Model
{
    use HasFactory;
    protected $fillable = [
        'email',
        'user_id',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public static function random_words($words = 1, $length = 6)
    {
        $string = '';
        for ($o = 1; $o <= $words; $o++) {
            $vowels = array("a", "e", "i", "o", "u");
            $consonants = array(
                'b', 'c', 'd', 'f', 'g', 'h', 'j', 'k', 'l', 'm',
                'n', 'p', 'r', 's', 't', 'v', 'w', 'x', 'y', 'z'
            );

            $word = '';
            for ($i = 1; $i <= $length; $i++) {
                $word .= $consonants[rand(0, 19)];
                $word .= $vowels[rand(0, 4)];
            }
            $string .= mb_substr($word, 0, $length);
            $string .= "-";
        }
        return mb_substr($string, 0, -1);
    }

    public static function get_emails_received_($email, $startOfDay, $endOfDay, $mg)
    {
        $res = $mg->events()->get('mx.getmailet.com', array('recipient' => $email, 'begin' => $startOfDay, 'end' => $endOfDay));
        return $res->getItems();
    }

    public static function get_email_($email_id, $mg)
    {
        $res =  $mg->events()->get('mx.getmailet.com', array('message-id' => $email_id));
        $jo = null;
        if (isset($res->getItems()[0])) {
            $url = $res->getItems()[0]->getStorage()['url'];
            $key = $res->getItems()[0]->getStorage()['key'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_USERPWD, "api:" . env('MAILGUN_API_KEY'));
            curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            $output = curl_exec($ch);
            $jo = json_decode($output, true);
            curl_close($ch);
        }
        return $jo;
    }
}
