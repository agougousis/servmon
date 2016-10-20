<?php

namespace App\Http\Controllers;

use Auth;
use Config;
use Validator;
use Response;
use App\Models\Server;
use App\Models\Domain;
use App\Models\SystemLog;
use App\Models\ServerDelegation;
use App\Models\DomainDelegation;

/**
 * Implements functionality useful to more than one controlers
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class RootController extends Controller
{

    /**
     * Loads a specific view using the appropriate template
     *
     * @param string $view_name
     * @param string $title
     * @param array $data
     * @return Response
     */
    protected function loadView($view_name, $title, $data=array())
    {
        $content = view($view_name, $data);
        if (Auth::check()) {
            $template = 'in_template';
        } else {
            $template = 'out_template';
        }

        $page = view($template)
                ->with('title',$title)
                ->with('content',$content)
                ->with('isSuperuser',($this->isSuperuser() ? true : false));

        $response = Response::make($page);
        $response->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-stale=0, post-check=0, pre-check=0');
        return $response;
    }

    /*
     * Logs an event
     *
     * @param string $message
     * @param string $category
     */
    protected function logEvent($message, $category)
    {
        // Define the actor
        if (Auth::check()) {
            $user_id = Auth::user()->id;
        } else {
            $user_id = 0;                           // system action
        }

        $action = app('request')->route()->getAction();
        $controller = class_basename($action['controller']);
        list($controller, $action) = explode('@', $controller);

	$log = new SystemLog();
	$log->when 	=   date("Y-m-d H:i:s");
	$log->actor 	=   $user_id;
	$log->controller =  $controller;
	$log->method 	=   $action;
	$log->message 	=   $message;
        $log->category   =   $category;
	$log->save();
    }

    /**
     * Validates a form against a rules set and adds any validation errors it
     * finds to the error list
     *
     * @param string $rulesKey  Array key that is used to retrieved the rules from config/validation.php
     * @param array $form       The form items that are going to be validated
     * @param array $errors     List of errors
     * @param int $index        Used when multiple items are validated by a function.
     * @return array
     */
    protected function loadValidationErrors($rulesKey, $form, $errors=null, $index=null)
    {
        if (empty($errors)) {
            $errors = [];
        }
	$rules = Config::get($rulesKey);
        $validator = Validator::make($form, $rules);
        if ($validator->fails()){
            foreach ($validator->errors()->getMessages() as $key => $errorMessages) {
                foreach ($errorMessages as $msg) {
                    $errorItem = array(
                        'field'     =>  $key,
                        'message'   =>  $msg
                    );
                    if (!is_null($index)) {
                        $errorItem['index'] = $index;
                    }
                    $errors[] = $errorItem;
                }
            }
        }
	return $errors;
    }

    /**
     * Checks if the user has superuser privileges
     *
     * @return boolean
     */
    protected function isSuperuser()
    {
        if (Auth::check()) {
            if (Auth::user()->superuser == 1) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * Checks is a specific user has administration rights on a specific server
     *
     * @param int $userId
     * @param int $serverId
     * @return boolean
     */
    protected function canManageServer($userId, $serverId)
    {
        $server = Server::find($serverId);
        $domainId = $server->domain;

        $server_delegation = ServerDelegation::searchByUserAndServer($serverId, $userId);
        if ((!empty($server_delegation))||($this->canManageDomain($userId, $domainId))) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks is a specific user has administration privileges on a specific domain
     *
     * @param int $userId
     * @param int $domainId
     * @return boolean
     */
    protected function canManageDomain($userId, $domainId)
    {
        $domain = Domain::find($domainId);

        // Find domain's ancestors (including self)
        $ancestorDomains = array_flatten($domain->ancestorsAndSelf()->select('id')->get()->ToArray());
        // If the user has been delegated at least one of domain's ancestors, then he can manage the domain
        $countUserRelationsToAncestors = DomainDelegation::countUserDelegationToDomains($userId, $ancestorDomains);
        if ($countUserRelationsToAncestors == 0) {
           return false;
        } else {
            return true;
        }
    }

    /**
     * Checks is the user has a specific permission on a specific item
     *
     * @param string $itemType      Should take one the following values: domain, server, service, webapp, database
     * @param string $context       If the $itemType is service, webapp or database, then $context should be the server ID.
     *                              If $itemType is server, $context should be the domain ID this server belongs, and if $itemType
     *                              is domain, the $context should be the parent domain ID or (in case of a new top domain) null
     * @param string $actionType    Should take one the following values: create, edit, delete, read
     * @param int $itemId           If the $actionType is 'create', the $itemId shound be null, otherwise is should be the ID of the
     *                              editing/deleting item.
     * @return boolean
     */
    protected function hasPermission($itemType, $context, $actionType, $itemId)
    {
        $user = Auth::user();

        switch ($itemType) {
            case 'service':
            case 'webapp':
            case 'database':
                return $this->hasItemPermission($user, $context);
                break;
            case 'server':
                return $this->hasServerPermission($actionType, $user, $context, $itemId);
                break;
            case 'domain':
                return $this->hasDomainPermission($actionType, $user, $context, $itemId);
                break;
        }
    }

    /**
     * Checks if a specific user has a specific permission on a server item (service,webapp,database)
     *
     * @param User $user
     * @param int $context
     * @return boolean
     */
    private function hasItemPermission($user, $context)
    {
        if ($this->canManageServer($user->id, $context)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Checks if a user has a specific permission on a specific server
     *
     * @param string $actionType
     * @param User $user
     * @param int $context
     * @param int $itemId
     * @return boolean
     * @throws Exception
     */
    private function hasServerPermission($actionType, $user, $context, $itemId)
    {
        switch ($actionType) {
            case 'read':
                if (empty($itemId)) { // we want to read all servers in a domain
                    if (($this->canManageDomain($user->id, $context))||($user->superuser == 1)) {
                        return true;
                    } else {
                        return false;
                    }

                } else { // we want to read info about a specific server
                    if (($this->canManageServer($user->id, $itemId))||($user->superuser == 1)) {
                        return true;
                    } else {
                        return false;
                    }
                }
                break;
            case 'create':
                if ($this->canManageDomain($user->id, $context)) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'update':
            case 'delete':
                if ($this->canManageServer($user->id, $itemId)) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
                throw Exception('Could not resolve domain permission. No valid action type found.');
        }
    }

    /**
     * Checks if a user has a specific permission on a specific domain
     *
     * @param string $actionType
     * @param User $user
     * @param int $context
     * @return boolean
     * @throws Exception
     */
    private function hasDomainPermission($actionType, $user, $context, $itemId)
    {
        switch ($actionType) {
            case 'create':
                if (($context == null)||($this->canManageDomain($user->id, $context))) {
                    return true;
                } else {
                    return false;
                }
                break;
            case 'update':
            case 'delete':
                if ($this->canManageDomain($user->id, $itemId)) {
                    return true;
                } else {
                    return false;
                }
                break;
            default:
            throw Exception('Could not resolve domain permission. No valid action type found.');
        }
    }

}