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
        $services_num = count($services);

        // Validate the data for each node
        $errors = array();
        $index = 0;
        $created = array();
        DB::beginTransaction();
        foreach ($services as $service) {
            try {
                // Form validation
                $errors = $this->loadValidationErrors('validation.create_service', $service, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Service validation failed');
                }

                // Access control
                if (!$this->hasPermission('service', $service['server'], 'create', null)) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to create services on this server!');
                }

                $serv = new Service();
                $serv->fill($service)->save();
                $created[] = $serv;
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent('Service creation failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'Service creation failed. Check system logs.');
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($created);
        return response()->json($responseArray)->setStatusCode(200, $services_num.' service(s) added.');
    }

    /**
     * Returns information about a specific service item
     *
     * @param int $serviceId
     * @return Response
     */
    public function read($serviceId)
    {
        // Check if $serviceId is a positive integer
        if ($serviceId <= 0) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }

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
        return response()->json($responseArray)->setStatusCode(200, '');
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
        $services_num = count($services);

        // Validate the data for each node
        $errors = array();
        $index = 0;
        $updated = array();
        DB::beginTransaction();
        foreach ($services as $service) {
            try {
                // Form validation
                $errors = $this->loadValidationErrors('validation.update_service', $service, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Service validation failed');
                }

                $serv = Service::find($service['id']);

                // Access control
                if (!$this->hasPermission('service', $serv->server, 'create', $service['id'])) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to update services on this server!');
                }

                $serv->fill($service)->save();
                $updated[] = $serv;
            } catch (Exception $ex) {
                DB::rollBack();
                $this->logEvent('Service creation failed! Error: '.$ex->getMessage(), 'error');
                return response()->json(['errors' => []])->setStatusCode(500, 'Service creation failed. Check system logs.');
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($updated);
        return response()->json($responseArray)->setStatusCode(200, $services_num.' service(s) updated.');
    }

    /**
     * Deletes a specific service item
     *
     * @param int $serviceId
     * @return Response
     */
    public function delete($serviceId)
    {
        // Check if $appId is a positive integer
        if ($serviceId <= 0) {
            return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid service ID');
        }

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
