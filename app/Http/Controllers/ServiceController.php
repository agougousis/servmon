<?php

namespace App\Http\Controllers;

use DB;
use App\Models\Service;
use Illuminate\Http\Request;
use App\Http\Controllers\RootController;
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to services
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class ServiceController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('ServiceTransformer');
    }

    /**
     * Adds new services to servers
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        $services = $request->input('services');

        // Validate the data for each node
        $index = 0;
        $createdList = array();
        DB::beginTransaction();
        foreach ($services as $service) {
            $result = $this->createServiceItem($service, $index, $createdList);

            if ($result['status'] != 200) {
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($createdList);
        return response()->json($responseArray)->setStatusCode(200, count($services).' service(s) added.');
    }

    /**
     * Creates a single service
     *
     * @param array $service
     * @param int $index
     * @param array $createdList
     * @return array
     */
    protected function createServiceItem($service, $index, &$createdList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.create_service', $service, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Service validation failed!', 'errors' => []];
            }

            // Access control
            if (!$this->hasPermission('service', $service['server'], 'create', null)) {
                return ['status' => 403, 'message' => 'You are not allowed to create services on this server!', 'errors' => []];
            }

            $serv = new Service();
            $serv->fill($service)->save();
            $createdList[] = $serv;
        } catch (Exception $ex) {
            $this->logEvent('Service creation failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Service creation failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Returns information about a specific service item
     *
     * @param int $serviceId
     * @return Response
     */
    public function read($serviceId)
    {
        // Check if a service with such an ID exists
        $service = Service::find($serviceId);
        if (empty($service)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }

        // Access control
        if (!$this->hasPermission('service', $service->server, 'read', $serviceId)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to read services on this server!');
        }

        $responseArray = $this->transformer->transform($service);

        // Send back the node info
        return response()->json($responseArray, 200);
    }

    /**
     * Updates service items
     *
     * @param Request $request
     * @return Response
     */
    public function update(Request $request)
    {
        $services = $request->input('services');

        // Validate the data for each node
        $index = 0;
        $updatedList = array();
        DB::beginTransaction();
        foreach ($services as $service) {
            $result = $this->updateServiceItem($service, $index, $updatedList);

            if ($result['status'] != 200) {
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($updatedList);
        return response()->json($responseArray)->setStatusCode(200, count($services).' service(s) updated.');
    }

    /**
     * Update a single service
     *
     * @param array $service
     * @param int $index
     * @param array $updatedList
     * @return array
     */
    protected function updateServiceItem($service, $index, &$updatedList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.update_service', $service, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Service validation failed!', 'errors' => []];
            }

            $serv = Service::find($service['id']);

            // Access control
            if (!$this->hasPermission('service', $serv->server, 'create', $service['id'])) {
                return ['status' => 403, 'message' => 'You are not allowed to update services on this server!', 'errors' => []];
            }

            $serv->fill($service)->save();
            $updatedList[] = $serv;
        } catch (Exception $ex) {
            $this->logEvent('Service creation failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Service creation failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Deletes a specific service item
     *
     * @param int $serviceId
     * @return Response
     */
    public function delete($serviceId)
    {
        // Check if a node with ID equal to $nid exists
        $service = Service::find($serviceId);
        if (empty($service)) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }

        // Access control
        if (!$this->hasPermission('service', $service->server, 'delete', $service->id)) {
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to delete services on this server!');
        }

        $service->delete();
        return response()->json([])->setStatusCode(200, 'Service deleted successfully');
    }
}
