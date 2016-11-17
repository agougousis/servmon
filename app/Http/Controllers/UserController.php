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
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to user management
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class UserController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('UserTransformer');
    }

    /**
     * Returns information about a specific user
     *
     * @param int $user_id
     * @return Response
     */
    public function read($user_id)
    {
        $user = User::find($user_id);
        if (empty($user)) {
            return response()->json(['errors' => []])->setStatusCode(400, 'Invalid user ID.');
        }

        $responseArray = $this->transformer->transform($user);
        return response()->json($responseArray)->setStatusCode(200, '');
    }

    /**
     * Returns a list of users
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function search()
    {
        $mode = Input::get('mode') ?: 'normal';

        switch ($mode) {
            case 'normal':
                $users = User::allOrderedByLastname();
                $responseArray = $this->transformer->transform($users);
                break;
            case 'basic':
                $user_list = User::allOrderedByLastname();
                $responseArray = $this->transformer->transform($user_list, 'UserBasicTransformer');
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        return response()->json($responseArray)->setStatusCode(200, '');
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
                $created[] = $this->saveUser($user);
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent('User creation failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'User creation failed. Check system logs.');
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($created);
        return response()->json($responseArray)->setStatusCode(200, $users_num.' user(s) added.');
    }

    /**
     * Saves a new user in the database
     *
     * @param array $user
     * @return User
     */
    private function saveUser($user)
    {
        $new_user = new User();
        $new_user->firstname = $user['firstname'];
        $new_user->lastname = $user['lastname'];
        $new_user->email = $user['email'];
        $new_user->password = Hash::make($user['password']);
        $new_user->activated = 0;
        $new_user->save();

        $new_user->password = '';
        return $new_user;
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
        $responseArray = $this->transformer->transform($user);

        return response()->json($responseArray)->setStatusCode(200, 'User deactivated!');
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
        $responseArray = $this->transformer->transform($user);

        return response()->json($responseArray)->setStatusCode(200, 'User activated!');
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
        $responseArray = $this->transformer->transform($user);

        return response()->json($responseArray)->setStatusCode(200, 'User activated!');
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
        $responseArray = $this->transformer->transform($user);

        return response()->json($responseArray)->setStatusCode(200, 'User activated!');
    }
}
