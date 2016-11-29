<?php

namespace App\Http\Controllers;

use DB;
use Hash;
use Input;
use DateTime;
use App\User;
use App\Models\PasswordResetLink;
use Illuminate\Http\Request;
use App\Packages\Gougousis\Helpers\Mailer;

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
    public function sendResetLink(Request $request, Mailer $mailer)
    {
        $form = $request->input();

        // Form validation
        $errors = $this->loadValidationErrors('validation.password_reset_request', $form, null, null);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 400);
        }

        // Create and send a reset link
        DB::beginTransaction();
        try {
            $user = User::where('email', $form['email'])->first();
            $resetLink = $this->createResetLinkFor($user->id);

            if ($mailResponse = $mailer->sendResetLink($user, $resetLink) != 'ok') {
                DB::rollBack();
                $this->logEvent("Mail could not be sent! Error message: $mailResponse", 'error');
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
     * Creates and returns a password reset link for a user
     *
     * @param int $userId
     * @return string
     */
    protected function createResetLinkFor($userId)
    {
        // Create a reset link
        $reset_link = new PasswordResetLink();
        $reset_link->uid = $userId;

        // Add a random string to the reset link
        $random = str_random(24);
        $url = secure_url('password_reset/'.$random);
        $reset_link->code = $random;

        // Set expiration date to the reset link
        $date = new DateTime();
        $date->modify("+1 day");
        $valid_until = $date->format("Y-m-d H:i:s");
        $reset_link->valid_until = $valid_until;

        // Save the reset link
        $reset_link->save();

        return $url;
    }

    /**
     * Sets the user password to a new value selected by the user
     *
     * @param string $code
     * @return Response
     */
    public function setPassword($code)
    {
        $linkInfo = PasswordResetLink::where('code', '=', $code)->first();

        // Check for invalid link
        if (empty($linkInfo)||(!$linkInfo->isValid())) {
            $this->logEvent("Invalid reset link.", 'authnetication');
            return response()->json(['errors' => []])->setStatusCode(400, 'This reset link is not valid! Maybe it has expired.');
        }

        // Validate new password
        $form = Input::all();

        $errors = $this->loadValidationErrors('validation.password_reset', $form, null, null);
        if (!empty($errors)) {
            return response()->json(['errors' => $errors], 400);
        }

        // Set the new password
        try {
            $user = User::find($linkInfo->uid);
            $linkInfo->delete();
            $user->password = Hash::make($form['new_password']);
            $user->save();
        } catch (Exception $ex) {
            $this->logEvent("Request for reset link raised an error: ".$ex->getMessage(), 'error');
            return response()->json(['errors' => []])->setStatusCode(500, 'An unexpected error occured while trying to reset your password. Please contact system administrator!');
        }

        return response()->json([])->setStatusCode(200, 'Your password was reset successfully!');
    }
}
