<?php

namespace App\Packages\Gougousis\Transformers;

use Auth;
use App\Models\Domain;
use App\Models\DomainDelegation;
use League\Fractal;

class DomainTreeItemTransformer extends Fractal\TransformerAbstract
{
    public function transform($domain)
    {
        $my_domains = DomainDelegation::getUserDelegatedIds(Auth::user()->id);
        $my_domain_ids = array_flatten($my_domains);

        $item = [
            'id'    =>  "treeItem-".$domain->full_name,
            'nid'   =>  $domain->id,
            'text'  =>  $domain->full_name,
            'fake'  =>  $domain->fake
        ];

        if (!in_array($domain->id, $my_domain_ids)) {
            $item['state'] = (object)['disabled' => true];
        }
        $item = $this->addDescendants($item, $domain, $my_domain_ids);

        return $item;
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
                $newChild = new \stdClass();
                $newChild->id = "treeItem-".$child->full_name;  // need it for acceptance testing
                $newChild->nid = $child->id;
                $newChild->text = $child->full_name;
                if ($child->fake) {
                    $newChild->icon = "glyphicon glyphicon-cloud";
                }

                $oneOfMyDomainRoots = in_array($child->id, $my_domain_ids);
                $notPartOfMyDomain = (!empty($phpNode['state']))&&($phpNode['state']->disabled == true);
                if ((!$oneOfMyDomainRoots)&&($notPartOfMyDomain)) {
                    $newChild->state = (object)['disabled' => true];
                }
                if ($child->isLeaf()) {
                    $childrenArray[] = $newChild;
                } else {
                    $childrenArray[] = $this->addDescendants($newChild, $child, $my_domain_ids);
                }
            }
            $phpNode['children'] = $childrenArray;
        }

        return $phpNode;
    }
}
