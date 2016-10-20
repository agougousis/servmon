<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Mail;
use Input;
use DateTime;
use App\User;
use App\Models\PasswordResetLink;
use Illuminate\Http\Request;

/**
 * Implements functionality related to password reminding
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class PasswordController extends RootController
{

    /**
     * Sends to the user's email a password reset link
     *
     * @param Request $request
     * @return Response
     */
    public function sendResetLink(Request $request)
    {
        $form = $request->input();

        // Form validation
        $errors = $this->loadValidationErrors('validation.password_reset_request', $form, null, null);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors])->setStatusCode(400, '');
        }

        DB::beginTransaction();
        try {
            $user = User::where('email',$form['email'])->first();
            $uid = $user->id;

             // Create and send a reset link
            $reset_link = new PasswordResetLink();
            $reset_link->uid = $uid;
            $random = str_random(24);
            $url = secure_url('password_reset/'.$random);
            $reset_link->code = $random;
            $date = new DateTime();
            $date->modify("+1 day");
            $valid_until = $date->format("Y-m-d H:i:s");
            $reset_link->valid_until = $valid_until;
            $reset_link->save();

            // Notify the user about the reset link
            $data['link'] = $url;
            try {
                Mail::send('emails.password_reset_link', $data, function($message) use ($user)
                {
                  $message->to($user->email)->subject('ServMon: Password reset request');
                });
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent("Mail could not be sent! Error message: ".$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'Something went wrong! Please contact system administrator!');
            }

            DB::commit();
            return response()->json([])->setStatusCode(200, 'A reset link was sent!');

        } catch (Exception $ex) {
            DB::rollBack();
            $this->logEvent("Request for reset link raised an error: ".$ex->getMessage(), 'error');
            return response()->json(['errors' => []])->setStatusCode(500, 'Something went wrong! Please contact system administrator!');
        }
    }

    /**
     * Sets the user password to a new value selected by the user
     *
     * @param string $code
     * @return Response
     */
    public function setPassword($code)
    {
        $linkInfo = PasswordResetLink::where('code','=',$code)->first();

        // Check for invalid link
        if (empty($linkInfo)) {
            $this->logEvent("Illegal reset link.", 'authentication');
            return view('errors.illegal');
        }

        // Check for expired link
        $now = new DateTime();
        $valid_until = new DateTime($linkInfo->valid_until);
        if ($now > $valid_until) {
            $this->logEvent("Expired reset link.", 'authnetication');
            return response()->json(['errors' => []])->setStatusCode(400, 'Your reset link has expired!');
        }

        // Validate new password
        $form = Input::all();

        $errors = $this->loadValidationErrors('validation.password_reset', $form, null, null);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors])->setStatusCode(400,'');
        }

        DB::beginTransaction();
        try {
            $user = User::find($linkInfo->uid);
            $linkInfo->delete();
            $user->password = Hash::make($form['new_password']);
            $user->save();
        } catch (Exception $ex) {
            DB::rollBack();
            $this->logEvent("Request for reset link raised an error: ".$ex->getMessage(), 'error');
            return response()->json(['errors' => []])->setStatusCode(500, 'An unexpected error occured while trying to reset your password. Please contact system administrator!');
        }
        DB::commit();
        return response()->json([])->setStatusCode(200, 'Your password was reset successfully!');
    }

}