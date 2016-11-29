<?php

namespace App\Http\Controllers;

use DB;
use Auth;
use Input;
use Monitor;
use App\Models\Domain;
use App\Models\Server;
use App\Models\Service;
use App\Models\Webapp;
use Illuminate\Http\Request;
use App\Models\DomainDelegation;
use App\Http\Controllers\RootController;
use App\Packages\Gougousis\Transformers\Transformer;

/**
 * Implements functionality related to domains
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class DomainController extends RootController
{
    protected $transformer;

    public function __construct()
    {
        $this->transformer = new Transformer('DomainTransformer');
    }

    /**
     * Returns a list of web apps that are hosted to servers of a specific domain.
     *
     * @param String $full_domain_name
     * @return Response
     */
    public function webappList($full_domain_name)
    {
        $domain = Domain::findByFullname($full_domain_name);
        if (empty($domain)) {
            return response()->json(['errors' => []])->setStatusCode(404, 'The domain was not found!');
        }

        $webapps = Webapp::getAllUnderDomain($full_domain_name);
        $responseArray = $this->transformer->transform($webapps, 'WebappTransformer');
        return response()->json($responseArray, 200);
    }

    /**
     * Returns a list of servers that belong to a specific domain. alongside with their status
     *
     * @param String $full_domain_name
     * @return Response
     */
    public function serverList($full_domain_name)
    {
        $domain = Domain::findByFullname($full_domain_name);

        if (empty($domain)) {
            return response()->json(['errors' => []])->setStatusCode(404, 'The domain was not found!');
        }

        if (!$domain->isDelegatedTo(Auth::user()->id)) {
            return response()->json(['errors' => []])->setStatusCode(401, 'Unauthorized Access');
        }

        $servers = DB::table('servers')->where('domain', $domain->id)->get();
        // Add status and domain information to the servers
        $server_list = $this->enrichServerInfoWithDomain($servers, $domain);

        $responseArray = $this->transformer->transform($server_list, 'ServerTransformer');
        return response()->json($responseArray);
    }

    /**
     * Adds new domains in the domains tree.
     *
     * This method does not allow creating root domains yet.
     *
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        // Retrieve nodes array from JSON
        $domains = $request->input('domains');

        // Validate the data for each node
        $createdList = array();
        $index = 0;
        DB::beginTransaction();
        foreach ($domains as $domainData) {
            $result = $this->createDomainItem($domainData, $index, $createdList);

            if ($result['status'] != 200) {
                DB::rollBack();
                return response()->json(['errors' => $result['errors']])->setStatusCode($result['status'], $result['message']);
            }

            $index++;
        }

        DB::commit();
        $responseArray = $this->transformer->transform($createdList);
        return response()->json($responseArray)->setStatusCode(200, count($domains).' domain(s) added.');
    }

    /**
     * Adds a single domain in the domains tree
     *
     * @param array $domainData
     * @param int $index
     * @param array $createdList
     * @return array
     */
    protected function createDomainItem($domainData, $index, &$createdList)
    {
        try {
            // Form validation
            $errors = $this->loadValidationErrors('validation.domain_create', $domainData, [], $index);
            if (!empty($errors)) {
                return ['status' => 400, 'message' => 'Domain validation failed.', 'errors' => []];
            }

            // Locate the parent domain
            $parent = Domain::findByFullname($domainData['parent_domain']);

            // Access control
            if (empty($parent)) {
                $allowed = $this->hasPermission('domain', null, 'create', null);
            } else {
                $allowed = $this->hasPermission('domain', $parent->id, 'create', null);
            }
            if (!$allowed) {
                return ['status' => 403, 'message' => 'You are not allowed to create subdomains in this domain!', 'errors' => []];
            }

            // Save the new domain
            $createdList[] = $this->saveNewDomain($domainData, $parent);
        } catch (Exception $ex) {
            $this->logEvent('Domain creation failed! Error: '.$ex->getMessage(), 'error');
            return ['status' => 500, 'message' => 'Domain creation failed. Check system logs.', 'errors' => []];
        }

        return ['status' => 200, 'message' => '', 'errors' => []];
    }

    /**
     * Saves a new domain in the database.
     *
     * @param array $domainData
     * @param Domain $parent
     * @return Domain
     */
    protected function saveNewDomain($domainData, $parent)
    {
        // Create the new node
        $newDomain = new Domain();
        $newDomain->parent_id = null;
        $newDomain->node_name = $domainData['node_name'];
        $newDomain->full_name = (empty($parent)) ? $domainData['node_name'] : $domainData['node_name'].".".$domainData['parent_domain'];
        $newDomain->fake = (!empty($domainData['fake_domain'])) ? 1 : 0 ;
        $newDomain->save();

        if (!empty($parent)) {
            $newDomain->makeChildOf($parent);
        } else {
            // In case we create a root domain, we should be able to manage it
            $newDelegation = new DomainDelegation();
            $newDelegation->user_id = Auth::user()->id;
            $newDelegation->domain_id = $newDomain->id;
            $newDelegation->save();
        }

        return $newDomain;
    }

    /**
     * Deletes a specified domain from the domains tree
     *
     * All delegations to that domain are being revoked before the deletion.
     * If the domain has subdomains, it cannot be deleted.
     *
     * @param String $domname
     * @return Response
     */
    public function delete($domname)
    {
        $domain = Domain::findByFullname($domname);

        // Check if domain exists
        if (empty($domain)) {
            return response()->json(['errors'=>[]])->setStatusCode(404, 'The specified domain was not found!');
        }

        // Access control
        if (!$this->hasPermission('domain', $domain->parent_id, 'delete', $domain->id)) {
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to delete this domain!');
        }

        // Check if domain contains servers
        $count_servers = Server::where('domain', $domain->id)->get()->count();
        if ($count_servers > 0) {
            return response()->json(['errors'=>[]])->setStatusCode(409, 'This domain cannot be deleted before it contains servers!');
        }

        // Check if domain contains other domains
        $count_subdomains = Domain::countByParentId($domain->id);
        if ($count_subdomains > 0) {
            return response()->json(['errors'=>[]])->setStatusCode(409, 'This domain cannot be deleted before it contains subdomains!');
        }

        // Remove any delegations
        DomainDelegation::deleteByDomainId($domain->id);
        Domain::deleteByFullname($domname);
        return response()->json([])->setStatusCode(200, 'Domain deleted successfully!');
    }

    /**
     * Returns a list of servers that belong to a specific domain or any subdomain of this.
     *
     * @param String $domain_name
     * @return Response
     */
    public function serversUnderDomain($domain_name)
    {
        $domain = Domain::findByFullname($domain_name);

        if (empty($domain)) {
            return response()->json(['errors' => []])->setStatusCode(404, 'The domain was not found!');
        }

        // Access control
        if (!$this->hasPermission('server', $domain->id, 'read', null)) {
            DB::rollBack();
            return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to access information about this server!');
        }

        $servers = Server::getAllUnderDomain($domain_name);
        // Add status information to the servers
        $server_list = array_map(array($this, 'enrichServerInfo'), $servers);

        $responseArray = $this->transformer->transform($server_list, 'ServerTransformer');
        return response()->json($responseArray, 200);
    }

    /**
     * Add information on server items about their status and domain.
     *
     * @param array $servers
     * @param array $domain
     * @return array
     */
    protected function enrichServerInfoWithDomain($servers, $domain)
    {
        $tempList = array_map(array($this, 'enrichServerInfo'), $servers);

        $newServerList = array_map(function ($server) use ($domain) {
            $server->domain_name = $domain['full_name'];
            return $server;
        }, $tempList);

        return $newServerList;
    }

    /**
     * Add information on a server item, especially about its current status.
     *
     * @param array $server
     * @return array
     */
    protected function enrichServerInfo($server)
    {
        $pingResult = Monitor::ping($server->ip);
        $server->service_types = Service::getServiceTypesOnServer($server->id);
        $server->status = ($pingResult['status']) ? 'on' : 'off';
        $server->response_time = $pingResult['time'];
        return $server;
    }

    /**
     * Returns a list of domains, alongside with any other information is requested through GET parameters
     *
     * @uses $_GET['mode']
     * @return View
     */
    public function search()
    {
        $mode = Input::get('mode') ?: 'normal';

        switch ($mode) {
            case 'normal':
                $my_domains = DomainDelegation::getUserDelegatedIds(Auth::user()->id);
                $my_domain_ids = array_flatten($my_domains);

                $roots = Domain::roots()->get(); // $roots is a Collection
                $responseArray = $this->transformer->transform($roots, 'DomainTreeItemTransformer');

                return response()->json($responseArray, 200);
            case 'with_servers':
                $domainsPreOrder = Domain::domainsListPreOrder();
                $responseArray = $this->transformer->transform($domainsPreOrder, 'DomainListTransformer');
                return response()->json($responseArray, 200);
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
        }
    }
}
