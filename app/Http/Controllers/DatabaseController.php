<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use App\Models\Database;
use App\Models\Webapp;
use App\Http\Controllers\RootController;
use Illuminate\Http\Request;
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to backups
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class DatabaseController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('DatabaseTransformer');
    }

    /**
     * Creates new database items
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $databases = $request->input('databases');

        // Validate the data for each node
        $index = 0;
        $createdList = array();

        DB::beginTransaction();
        foreach ($databases as $database) {
            $result = $this->createDatabaseItem($database, $index, $createdList);

            if ($result['status'] != 200) {
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();

        $responseArray = $this->transformer->transform($createdList);
        return response()->json($responseArray)->setStatusCode(200, count($databases).' database(s) added.');
    }

    /**
     * Creates a single database item
     *
     * @param array $database
     * @param int $index
     * @param array $createdList
     * @return array
     */
    protected function createDatabaseItem($database, $index, &$createdList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.create_database', $database, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Database validation failed', 'errors' => $errors];
            }

            // Access control
            if (!$this->hasPermission('database', $database['server'], 'create', null)) {
                return ['status' => 403, 'message' => 'You are not allowed to create databases on this server!', 'errors' => []];
            }

            // Update the related webapp, if there is one
            if (!empty($database['related_webapp'])) {
                $wp = Webapp::getByUrl($database['related_webapp']);
                $database['related_webapp'] = $wp->id;
                $database['related_webapp_name'] = $wp->url;
            }

            // Save the database info
            $db = new Database();
            $db->owner = Auth::user()->id;
            $db->fill($database)->save();
            $createdList[] = $db;
        } catch (Exception $ex) {
            $this->logEvent('Database creation failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Database creation failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Returns information about a specific database item
     *
     * @param int $databaseId
     * @return Response
     */
    public function read($databaseId)
    {
        // Check if a database with such an ID exists
        $database = Database::find($databaseId);
        if (empty($database)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid database ID');
        }
        if (!empty($database->related_webapp)) {
            $wp = Webapp::find($database->related_webapp);
            $database->related_webapp = $wp->url;
        }

        // Access control
        if (!$this->hasPermission('database', $database->server, 'read', $databaseId)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to read databases on this server!');
        }

        $responseArray = $this->transformer->transform($database);

        // Send back the node info
        return response()->json($responseArray, 200);
    }

    /**
     * Updates database items
     *
     * @param Request $request
     * @param int $databaseId
     * @return type
     */
    public function update(Request $request)
    {
        $databases = $request->input('databases');

        // Validate the data for each node
        $index = 0;
        $updatedList = array();
        DB::beginTransaction();
        foreach ($databases as $database) {
            $result = $this->updateDatabaseItem($database, $index, $updatedList);

            if ($result['status'] != 200) {
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($updatedList);
        return response()->json($responseArray)->setStatusCode(200, count($databases).' database(s) updated.');
    }

    /**
     * Updates a single database item
     *
     * @param array $database
     * @param int $index
     * @param array $createdList
     * @return array
     */
    protected function updateDatabaseItem($database, $index, &$updatedList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.update_database', $database, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Database validation failed.', 'errors' => $errors];
            }

            // Access control
            if (!$this->hasPermission('database', $database['server'], 'update', null)) {
                return ['status' => 403, 'message' => 'You are not allowed to update databases on this server!', 'errors' => []];
            }

            //
            if (empty($database['related_webapp'])) {
                $database['related_webapp'] = null;
            }

            // Update the database item
            $db = Database::find($database['id']);
            $db->fill($database)->save();

            // Update the related webapp, if needed
            if (!empty($db->related_webapp)) {
                $wp = Webapp::find($db->related_webapp);
                $db['related_webapp_name'] = $wp->url;
            }
            $updatedList[] = $db;
        } catch (Exception $ex) {
            $this->logEvent('Database update failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Database update failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Deletes a database item
     *
     * @param int $databaseId
     * @return Response
     */
    public function delete($databaseId)
    {
        // Check if a node with ID equal to $databaseId exists
        $database = Database::find($databaseId);
        if (empty($database)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid database ID');
        }

        // Access control
        if (!$this->hasPermission('database', $database->server, 'delete', null)) {
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to delete databases on this server!');
        }

        $database->delete();
        return response()->json([])->setStatusCode(200, 'Database deleted successfully!');
    }
}
