<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Webapp;
use App\Models\Server;
use App\Models\Database;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to webapps
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class WebappController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('WebappTransformer');
    }

    /**
     * Returns a list of all the webapps that have been defined in the system
     *
     * @return Response
     */
    public function search()
    {
        $responseArray = $this->transformer->transform(Webapp::all());
        return response()->json($responseArray)->setStatusCode(200, '');
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

        // Validate the data for each node
        $index = 0;
        $createdList = array();
        DB::beginTransaction();
        foreach ($webapps as $webapp) {
            $result = $this->createWebappItem($webapp, $index, $createdList);

            if($result['status'] != 200){
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($createdList);
        return response()->json($responseArray)->setStatusCode(200, count($webapps).' webapps(s) added.');
    }

    /**
     * Creates a single webapp
     *
     * @param array $webapp
     * @param int $index
     * @param array $createdList
     * @return array
     */
    protected function createWebappItem($webapp, $index, &$createdList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.create_webapp', $webapp, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Delegation request validation failed!', 'errors' => $errors];
            }

            // Access control
            if (!$this->hasPermission('webapp', $webapp['server'], 'create', null)) {
                return ['status' => 403, 'message' => 'You are not allowed to create webapps on this server!', 'errors' => $errors];
            }

            $wp = new Webapp();
            $wp->fill($webapp)->save();
            $createdList[] = $wp;
        } catch (Exception $ex) {
            $this->logEvent('Webapp creation failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Webapp creation failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Returns information about a specific webapp
     *
     * @param int $appId
     * @return Response
     */
    public function read($appId)
    {
        // Check if a node with ID equal to $nid exists
        $webapp = Webapp::find($appId);
        if (empty($webapp)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid webapp ID');
        }

        // Access control
        if (!$this->hasPermission('webapp', $webapp->server, 'read', $appId)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to read webapps on this server!');
        }

        // Transform the response data
        $responseArray = $this->transformer->transform($webapp);
        return response()->json($responseArray)->setStatusCode(200, '');
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

        // Validate the data for each node
        $index = 0;
        $updatedList = array();
        DB::beginTransaction();
        foreach ($webapps as $webapp) {
            $result = $this->updateWebappItem($webapp, $index, $updatedList);

            if($result['status'] != 200){
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($updatedList);
        return response()->json($responseArray)->setStatusCode(200, count($webapps).' webapp(s) updated.');
    }

    /**
     * Updates a single webapp item
     *
     * @param array $webapp
     * @param int $index
     * @param array $updatedList
     * @return array
     */
    protected function updateWebappItem($webapp, $index, &$updatedList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.update_webapp', $webapp, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Webapp validation failed!', 'errors' => []];
            }

            $wp = Webapp::find($webapp['id']);

            // Access control
            if (!$this->hasPermission('webapp', $webapp['server'], 'update', $wp->id)) {
                return ['status' => 403, 'message' => 'You are not allowed to update webapps on this server!', 'errors' => []];
            }

            $wp->fill($webapp)->save();
            $updatedList[] = $wp;
        } catch (Exception $ex) {
            $this->logEvent('Webapp update failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Webapp update failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Deletes a specific webapp item
     *
     * @param int $appId
     * @return Response
     */
    public function delete($appId)
    {
        // Check if a node with ID equal to $nid exists
        $webapp = Webapp::find($appId);
        if (empty($webapp)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid webapp ID');
        }

        $database = Database::where('related_webapp', $appId)->first();
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
