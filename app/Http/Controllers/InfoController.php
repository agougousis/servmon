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
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements generic functionality
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class InfoController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('InfoTransformer');
    }

    /**
     * Returns information about the user itself
     *
     * @return Response
     */
    public function myprofile()
    {
        $user = User::find(Auth::user()->id);
        return response()->json($user, 200);
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
        return response()->json($data, 200);
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
        return response()->json(['data' => $setting_list], 200);
    }

    /**
     * Returns a list with service, webapp or/and datbase supported types
     *
     * @uses $_GET['mode']
     * @return Response
     */
    public function supportedTypesList()
    {
        $mode = Input::get('mode') ?: 'all';

        $types = new \stdClass();
        switch ($mode) {
            case 'all':
                $types->service = ServiceType::all();
                $types->webapp = WebappType::all();
                $types->database = DatabaseType::all();
                break;
            case 'services':
                $types->service = ServiceType::all();
                break;
            case 'webapps':
                $types->webapp = WebappType::all();
                break;
            case 'databases':
                $types->database = DatabaseType::all();
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }

        $responseArray = $this->transformer->transform($types);
        return response()->json($responseArray, 200);
    }
}
