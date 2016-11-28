<?php

namespace App\Packages\Gougousis\Helpers;

use App\User;
use Illuminate\Support\Facades\Mail;

/**
 * A class that handles the email-ing
 *
 * @author Alexandros Gougousis
 */
class Mailer {

    /**
     * Sends a password reset link to the specifid user
     *
     * @param type $user
     * @param type $resetUrl
     * @return string
     */
    public function sendResetLink(User $user, $resetUrl)
    {
        $data['link'] = $resetUrl;
        try {
            Mail::send('emails.password_reset_link', $data, function ($message) use ($user) {
                $message->to($user->email)->subject('ServMon: Password reset request');
            });

            return 'ok';
        } catch (Exception $ex) {
            return $ex->getMessage();
        }
    }

}

