<?php

function startsWithString($mainString, $startString)
{
    if (substr($mainString, 0, strlen($startString)) === $startString) {
        return true;
    }
    return false;
}

/**
 * Turns a collection structure to an assosiative array where each collection
 * item has been stored with a key equal to the the value of the relevant
 * property of this item. We assume that all key values are unique.
 *
 * @param string $keyProperty
 * @param array|object $collection
 * @return array
 */
function turnToAssocUnique($keyProperty,$collection){
    $assocArray = [];

    if(is_array($collection)){
        foreach($collection as $item){
            $assocArray[$item[$keyProperty]] = $item;
        }
        return $assocArray;
    }

    if(is_object($collection)){
        foreach($collection as $item){
            $assocArray[$item->{$keyProperty}] = $item;
        }
        return $assocArray;
    }
}

/**
 * Turns a collection structure to an assosiative array where each collection
 * item has been stored with a key equal to the the value of the relevant
 * property of this item. We assume that key values are not necesserily unique
 * and each unique key points (has as value) to an array containing all items
 * with that key.
 *
 * @param string $keyProperty
 * @param array|object $collection
 * @return array
 */
function turnToAssoc($keyProperty,$collection){
    $assocArray = [];

    if(is_array($collection)){
        foreach($collection as $item){
            $assocArray[$item[$keyProperty]][] = $item;
        }
        return $assocArray;
    }

    if(is_object($collection)){
        foreach($collection as $item){
            $assocArray[$item->{$keyProperty}][] = $item;
        }
        return $assocArray;
    }
}