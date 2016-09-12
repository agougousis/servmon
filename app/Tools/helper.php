<?php

function startsWithString($mainString,$startString){
    if(substr($mainString, 0, strlen($startString)) === $startString){
        return true;
    }
    return false;
}
