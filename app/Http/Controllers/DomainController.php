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

/**
 * Implements functionality related to domains
 *
 * @license MIT
 * @author Alexandros Gougousis
 */
class DomainController extends RootController
{

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
        return response()->json($webapps)->setStatusCode(200, 'ok');
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

        $servers = Server::where('domain', $domain->id)->get()->toArray();

        $server_list = array();
        foreach ($servers as $server) {
            $pingResult = Monitor::ping($server['ip']);
            $server['services'] = Service::getServiceTypesOnServer($server['id']);
            $server['status'] = ($pingResult['status']) ? 'on' : 'off';
            $server['response_time'] = $pingResult['time'];
            $server['domain_name'] = $domain->full_name;
            $server_list[] = $server;
        }
        return response()->json($server_list);
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
        $domain_num = count($domains);

        // Validate the data for each node
        $errors = array();
        $created = array();
        $index = 0;
        DB::beginTransaction();
        foreach ($domains as $domain) {
            try {
                // Form validation
                $errors = $this->loadValidationErrors('validation.domain_create', $domain, $errors, $index);
                if (!empty($errors)) {
                    DB::rollBack();
                    return response()->json(['errors' => $errors])->setStatusCode(400, 'Domain validation failed');
                }

                // Locate the parent domain
                $parent = Domain::findByFullname($domain['parent_domain']);

                // Access control
                if (empty($parent)) {
                    $allowed = $this->hasPermission('domain', null, 'create', null);
                } else {
                    $allowed = $this->hasPermission('domain', $parent->id, 'create', null);
                }
                if (!$allowed) {
                    DB::rollBack();
                    return response()->json(['errors' => []])->setStatusCode(403, 'You are not allowed to create subdomains in this domain!');
                }

                // Create the new node
                $newDomain = new Domain();
                $newDomain->parent_id = null;
                $newDomain->node_name = $domain['node_name'];
                $newDomain->full_name = (empty($parent)) ? $domain['node_name'] : $domain['node_name'].".".$domain['parent_domain'];
                $newDomain->fake = (!empty($domain['fake_domain'])) ? 1 : 0 ;
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

                $created[] = $newDomain;
            } catch (Exception $ex) {
                DB::rollBack();
                $errors[] = array(
                    'index'     =>  $index,
                    'field'     =>  $result['error']['field'],
                    'message'   =>  $result['error']['message']
                );
                return response()->json(['errors' => $errors])->setStatusCode(500, 'Domain creation failed');
            }

            $index++;
        }

        DB::commit();
        return response()->json($created)->setStatusCode(200, $domain_num.' domain(s) added.');
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

        $server_list = array();
        foreach ($servers as $server) {
            $pingResult = Monitor::ping($server['ip']);
            $server['services'] = Service::getServiceTypesOnServer($server['id']);
            $server['status'] = ($pingResult['status']) ? 'on' : 'off';
            $server['response_time'] = $pingResult['time'];
            $server['domain_name'] = $server['full_name'];
            $server_list[] = $server;
        }

        return response()->json($server_list)->setStatusCode(200, '');
    }

    /**
     * Returns a list of domains, alongside with any other information is requested through GET parameters
     *
     * @uses $_GET['mode']
     * @return View
     */
    public function search()
    {
        if (!Input::has('mode')) {
            $mode = "normal";
        } else {
            $mode = Input::get('mode');
        }

        switch ($mode) {
            case 'normal':
                $my_domains = DomainDelegation::getUserDelegatedIds(Auth::user()->id);
                $my_domain_ids = array_flatten($my_domains);

                $roots = Domain::roots()->get(); // $roots is a Collection
                $arrayTree = array();

                foreach ($roots as $root) {  // $root is an Sname model
                    $phpRoot = new \stdClass();
                    $phpRoot->id = "treeItem-".$root->full_name;
                    $phpRoot->nid = $root->id;
                    $phpRoot->text = $root->full_name;
                    if (!in_array($root->id, $my_domain_ids)) {
                        $phpRoot->state = (object)['disabled' => true];
                    }
                    $arrayTree[] = $this->addDescendants($phpRoot, $root, $my_domain_ids);
                }

                return response()->json($arrayTree)->setStatusCode(200, '');
                break;
            case 'with_servers':
                $response = array();
                $domains = DB::table('domains')->orderBy('lft', 'ASC')->get();
                foreach ($domains as $domain) {
                    $response[$domain->full_name] = array(
                        'depth'     =>  $domain->depth,
                        'servers'   =>  Server::getBasicInfoByDomain($domain->id)
                    );
                }

                return response()->json($response)->setStatusCode(200, '');
                break;
            default:
                return response()->json(['errors' => array()])->setStatusCode(400, 'Invalid search mode!');
                break;
        }
    }

    /**
     * Builds part of the domains tree into a PHP object, recursively
     *
     * @param stdClass $phpNode
     * @param Domain $baumNode
     * @param array $my_domain_ids
     * @return stdClass
     */
    private function addDescendants($phpNode, $baumNode, $my_domain_ids)
    {
        $children = $baumNode->children()->get();
        if ($children->count() > 0) {
            $childrenArray = array();
            foreach ($children as $child) {
                $countLeaves = $child->leaves()->get()->count();
                $newChild = new \stdClass();
                $newChild->id = "treeItem-".$child->full_name;  // need it for acceptance testing
                $newChild->nid = $child->id;
                $newChild->text = $child->full_name;
                if ($child->fake) {
                    $newChild->icon = "glyphicon glyphicon-cloud";
                }

                $oneOfMyDomainRoots = in_array($child->id, $my_domain_ids);
                $notPartOfMyDomain = (!empty($phpNode->state))&&($phpNode->state->disabled == true);
                if ((!$oneOfMyDomainRoots)&&($notPartOfMyDomain)) {
                    $newChild->state = (object)['disabled' => true];
                }
                if ($child->isLeaf()) {
                    $childrenArray[] = $newChild;
                } else {
                    $childrenArray[] = $this->addDescendants($newChild, $child, $my_domain_ids);
                }
            }
            $phpNode->children = $childrenArray;
        }

        return $phpNode;
    }
}
