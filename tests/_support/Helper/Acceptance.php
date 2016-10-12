<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Acceptance extends \Codeception\Module
{

    function seeEqualValues($value1,$value2){
        $this->assertEquals($value1,$value2);
    }
    
    function grabRowIdWithText($rowTextList,$rowIdList,$targetText){
        
        $locatedAt = -1;
        
        foreach($rowTextList as $key => $rowText){
            codecept_debug('key = '.$key.' , text = '.$rowText);
            if( strpos($rowText,$targetText) !== false ){
                $locatedAt = $key;
                break;
            }
        }
        codecept_debug('locatedAt = '.$locatedAt);
        if($locatedAt >= 0){            
            $rowId = $rowIdList[$locatedAt];
            return $rowId;
        }
        
        return '';
    }
    
}
