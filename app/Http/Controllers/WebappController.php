<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Webapp;
use App\Models\Server;
use App\Models\Database;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;

/**
 * Implements functionality related to webapps
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class WebappController extends RootController
{

    /**
     * Returns a list of all the webapps that have been defined in the system
     *
     * @return Response
     */
    public function search()
    {
        $webapps = Webapp::getAllAsArray();
        return response()->json($webapps)->setStatusCode(200, '');
    }

    /**
     * Creates new webapp items
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $webapps = $request->input('webapps');
        $webapps_num = count($webapps);

        // Validate the data for each node
        $errors = array();
        $index = 0;
        $created = array();
        DB::beginTransaction();
        foreach ($webapps as $webapp) {
            try {

                // Form validation
                $errors = $this->loadValidationErrors('validation.create_webapp', $webapp, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Webapp validation failed!');
                }

                // Access control
                if (!$this->hasPermission('webapp', $webapp['server'], 'create', null)) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to create webapps on this server!');
                }

                $wp = new Webapp();
                $wp->fill($webapp)->save();
                $created[] = $wp;

            } catch (Exception $ex) {
                DB::rollBack();
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Webapp creation failed');
            }

            $index++;
        }

        DB::commit();
        return response()->json($created)->setStatusCode(200, $webapps_num.' webapps(s) added.');
    }

    /**
     * Returns information about a specific webapp
     *
     * @param int $appId
     * @return Response
     */
    public function read($appId)
    {
        // Check if $appId is a positive integer
        if ($appId <= 0) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid webapp ID');
        }

        // Check if a node with ID equal to $nid exists
        $webapp = Webapp::find($appId);
        if (empty($webapp)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid webapp ID');
        }

        $webapp = Webapp::select('id', 'url', 'server', 'language', 'developer', 'contact', 'watch')->where('id', $appId)->first();

        // Access control
        if (!$this->hasPermission('webapp', $webapp->server, 'read', $appId)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to read webapps on this server!');
        }

        $result = new \stdClass();
        $result->data = $webapp;

        // Send back the node info
        return response()->json($result)->setStatusCode(200, '');
    }

    /**
     * Updates webapp items
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $webapps = $request->input('webapps');
        $webapps_num = count($webapps);

        // Validate the data for each node
        $errors = array();
        $index = 0;
        $updated = array();
        DB::beginTransaction();
        foreach ($webapps as $webapp) {
            try {

                // Form validation
                $errors = $this->loadValidationErrors('validation.update_webapp', $webapp, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Webapp validation failed!');
                }

                $wp = Webapp::find($webapp['id']);

                // Access control
                if (!$this->hasPermission('webapp', $webapp['server'], 'update', $wp->id)) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to update webapps on this server!');
                }

                $wp->fill($webapp)->save();
                $updated[] = $wp;

            } catch (Exception $ex) {
                DB::rollBack();
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(400, 'Webapp update failed');
            }

            $index++;
        }

        DB::commit();
        return response()->json($updated)->setStatusCode(200, $webapps_num.' webapp(s) updated.');
    }

    /**
     * Deletes a specific webapp item
     *
     * @param int $appId
     * @return Response
     */
    public function delete($appId)
    {
        // Check if $appId is a positive integer
        if ($appId <= 0) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid webapp ID');
        }

        // Check if a node with ID equal to $nid exists
        $webapp = Webapp::find($appId);
        if (empty($webapp)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid webapp ID');
        }

        $database = Database::where('related_webapp',$appId)->first();
        if (!empty($database)) {
            $server = Server::find($database->server);
            return response()->json(['errors' => array()])->setStatusCode(403, "There is a database related to this webapp on '".$server->hostname."' server. Please detach the database from this web app first!");
        }

        // Access control
        if (!$this->hasPermission('webapp', $webapp->server, 'delete', $appId)) {
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to delete webapps on this server!');
        }

        $webapp->delete();
        return response()->json([])->setStatusCode(200, 'Webapp deleted successfully');
    }

}