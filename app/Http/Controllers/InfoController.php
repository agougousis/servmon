<?php

namespace App\Http\Controllers;

use Input;
use Auth;
use App\User;
use App\Models\Setting;
use App\Models\ServiceType;
use App\Models\WebappType;
use App\Models\DatabaseType;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Service;
use App\Models\Webapp;
use App\Models\Database;

/**
 * Implements generic functionality
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class InfoController extends RootController
{

    /**
     * Returns information about the user itself
     *
     * @return Response
     */
    public function myprofile()
    {
        $user = User::find(Auth::user()->id);
        return response()->json($user)->setStatusCode(200, '');
    }

    /**
     * Returns a list of all items that are included in a backup
     *
     * @return Response
     */
    public function backupItems()
    {
        $data['domains'] = Domain::all()->count();
        $data['servers'] = Server::all()->count();
        $data['services'] = Service::all()->count();
        $data['webapps'] = Webapp::all()->count();
        $data['databases'] = Database::all()->count();
        return response()->json($data)->setStatusCode(200, '');
    }

    /**
     * Returns system settings from database
     *
     * @return Response
     */
    public function settings()
    {
        $settings = Setting::get();
        $setting_list = array();
        foreach ($settings as $setting) {
            $setting_list[$setting->sname] = $setting->value;
        }
        return response()->json($setting_list)->setStatusCode(200, '');
    }

    /**
     * Returns a list with service, webapp or/and datbase supported types
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function supportedTypesList()
    {
        if (!Input::has('mode')) {
            $mode = "all";
        } else {
            $mode = Input::get('mode');
        }

        switch ($mode) {
            case 'all':
                $types = array(
                    'service'   =>  ServiceType::all()->toArray(),
                    'webapp'    =>  WebappType::all()->toArray(),
                    'database'  =>  DatabaseType::all()->toArray()
                );
                break;
            case 'services':
                $types = array(
                    'service'   =>  ServiceType::all()->toArray(),
                );
                break;
            case 'webapps':
                $types = array(
                    'webapp'    =>  WebappType::all()->toArray()
                );
                break;
            case 'databases':
                $types = array(
                    'database'  =>  DatabaseType::all()->toArray()
                );
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        return response()->json($types)->setStatusCode(200, '');
    }
}
