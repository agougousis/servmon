<?php

namespace App\Http\Controllers;

use Auth;
use DateTime;
use Redirect;
use App\User;
use App\Models\PasswordResetLink;

/**
 * Implements functionality related to backups
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class WebController extends RootController
{

    /**
     * Displays the installation page
     *
     * @return View
     */
    public function installationPage()
    {
        if (config('app.installation') == 'done') {
            return Redirect::to('/');
        }
        return $this->loadView('installation', 'Installation Page');
    }

    /**
     * Displays the Landing Page
     *
     * @return View
     */
    public function landingPage()
    {
        if (config('app.installation') != 'done') {
            return Redirect::to('/installation_page');
        }

        if (Auth::check()) {
            return Redirect::to('/home');
        }

        return $this->loadView('landing', 'Home Page');
    }

    /**
     * Displays the Home Page
     *
     * @return View
     */
    public function index()
    {
        return $this->loadView('index', "Home Page");
    }

    /**
     * Displays user profile page
     *
     * @return View
     */
    public function profile()
    {
        return $this->loadView('profile', 'My Profile');
    }

    /*
     * Displays the user management page.
     *
     * @return View
     */
    public function userManagement()
    {
        $title = 'User Management';
        return $this->loadView('admin.user_management', $title);
    }

    /**
     * Display a user's profile
     *
     * @param int $user_id
     * @return Response
     */
    public function userProfileManagement($user_id)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            return $this->custom_error_message("User not found!");
        }

        $data['user_id'] = $user_id;
        return $this->loadView('admin.user_profile_manage', 'User Profile', $data);
    }

    /**
     * Displays the backup administration page
     *
     * @return View
     */
    public function backupPage()
    {
        return $this->loadView('backup', "Backup");
    }

    /**
     * Displays the monitoring configuration page
     *
     * @return View
     */
    public function configure()
    {
        return $this->loadView('configure', "Configuration Page");
    }

    /**
     * Displays the delegations management page
     *
     * @return View
     */
    public function delegationsPage()
    {
        return $this->loadView('delegations', 'Administration Delegation');
    }

    /**
     * Displays password reset request form
     *
     * @return View
     */
    public function passwordResetRequest()
    {
        return $this->loadView('password_reset_request', 'Password reset request');
    }

    /**
     * Displays a message about the password reset link sent to the user
     *
     * @return View
     */
    public function resetLinkSent()
    {
        return $this->loadView('reset_link_sent', 'Password reset requested');
    }

    /**
     * Displays a form to set a new password
     *
     * @param string $code
     * @return View
     */
    public function setPasswordPage($code)
    {
        $linkInfo = PasswordResetLink::where('code', '=', $code)->first();

        // Check for invalid link
        if (empty($linkInfo)) {
            $this->logEvent("Illegal reset link.", 'authentication');
            return $this->loadView('errors.illegal', '');
        }

        // Check for expired link
        $now = new DateTime();
        $valid_until = new DateTime($linkInfo->valid_until);
        if ($now > $valid_until) {
            $this->logEvent("Expired reset link.", 'authnetication');
            return $this->loadView('errors.expired_link', 'Invalid link');
        }

        // Load the password setting page
        $data = ['code' => $code];
        return $this->loadView('set_password_page', 'Password reset page', $data);
    }
}
