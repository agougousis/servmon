<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Hash;
use Input;
use App\User;
use App\Models\DomainDelegation;
use App\Models\ServerDelegation;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;

/**
 * Implements functionality related to user management
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class UserController extends RootController
{

    /**
     * Returns information about a specific user
     *
     * @param int $user_id
     * @return Response
     */
    public function read($user_id)
    {
        $user = User::where('id', $user_id)->select('id', 'email', 'firstname', 'lastname', 'activated', 'superuser', 'last_login')->first();
        if (empty($user)) {
            return response()->json(['errors' => []])->setStatusCode(400, 'Invalid user ID.');
        }

        return response()->json($user)->setStatusCode(200, '');
    }

    /**
     * Returns a list of users
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function search()
    {
        $mode = (Input::has('mode'))? Input::get('mode') : 'normal';
        
        switch ($mode) {
            case 'normal':
                $users = User::getList();
                break;
            case 'basic':
                $user_list = User::getBasicInfoList();
                $users = array();
                foreach ($user_list as $user) {
                    $users[$user->email] = $user->firstname." ".$user->lastname;
                }
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        return response()->json($users)->setStatusCode(200, '');
    }

    /*
     * Creates new users
     *
     * @return Response
     */
    public function addUsers(Request $request)
    {
        $users = $request->input('users');
        $users_num = count($users);

        // Validate the data for each node
        $errors = array();
        $created = array();
        $index = 0;
        DB::beginTransaction();
        foreach ($users as $user) {
            try {
                // Form validation
                $errors = $this->loadValidationErrors('validation.add_user', $user, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'User validation failed!');
                }

                // Create the user in the database
                $new_user = new User();
                $new_user->firstname = $user['firstname'];
                $new_user->lastname = $user['lastname'];
                $new_user->email = $user['email'];
                $new_user->password = Hash::make($user['password']);
                $new_user->activated = 0;
                $new_user->save();

                $new_user->password = '';
                $created[] = $new_user;
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent('User creation failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'User creation failed. Check system logs.');
            }

            $index++;
        }

        DB::commit();
        return response()->json($created)->setStatusCode(200, $users_num.' user(s) added.');
    }

    /**
     * Deletes a specific user
     *
     * @param int $user_id
     * @return Response
     */
    public function deleteUser($user_id)
    {
        if (Auth::user()->id == $user_id) {
            return response()->json(['errors' => []])->setStatusCode(400, 'You cannot delete your own account!');
        }

        $user = User::find($user_id);
        if (empty($user)) {
            return response()->json(['errors' => []])->setStatusCode(400, 'Invalid user ID!');
        }

        DB::beginTransaction();
        try {
            // Delete the user delegations
            DomainDelegation::deleteUserDelegations($user_id);
            ServerDelegation::deleteUserDelegations($user_id);
            // Delete the user
            $user->delete();
        } catch (Exception $ex) {
            DB::rollBack();
            $this->logEvent('User deletion failed! ERROR: '.$ex->getMessage(), 'error');
            return response()->json([])->setStatusCode(500, 'User deleation failed! Please, contact the administrator.');
        }
        DB::commit();

        return response()->json([])->setStatusCode(200, 'The user has been deleted!');
    }


    /**
     * Disables a specific user account
     *
     * @param int $user_id
     * @return Response
     */
    public function disableUser($user_id)
    {
        if (Auth::user()->id == $user_id) {
            return response()->json(['errors' => []])->setStatusCode(400, 'A user cannot deactivate himself!');
        }

        $user = User::find($user_id);
        if (empty($user)) {
            return response()->json(['errors' => []])->setStatusCode(400, 'Illegal user ID.');
        }

        $user->activated = 0;
        $user->save();
        $user->password = '';
        return response()->json($user)->setStatusCode(200, 'User deactivated!');
    }

    /**
     * Enables a specific user account
     *
     * @param int $user_id
     * @return Response
     */
    public function enableUser($user_id)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Illegal user ID.');
        }

        $user->activated = 1;
        $user->save();
        $user->password = '';
        return response()->json($user)->setStatusCode(200, 'User activated!');
    }

    /**
     * Grants superuser privileges to a user
     *
     * @param int $user_id
     * @return Response
     */
    public function makeSuperuser($user_id)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Illegal user ID.');
        }

        $user->superuser = 1;
        $user->save();
        $user->password = '';
        return response()->json($user)->setStatusCode(200, 'User activated!');
    }

    /**
     * Revokes superuser privileges from a user
     *
     * @param int $user_id
     * @return Response
     */
    public function unmakeSuperuser($user_id)
    {
        if (Auth::user()->id == $user_id) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'You cannot revoke superuser privilege from yourself!');
        }

        $user = User::find($user_id);
        if (empty($user)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Illegal user ID.');
        }

        $user->superuser = 0;
        $user->save();
        $user->password = '';
        return response()->json($user)->setStatusCode(200, 'User activated!');
    }
}
