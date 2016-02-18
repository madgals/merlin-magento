<?php

namespace Merlin;

abstract class MultiFieldParameter {
    protected $fieldJoiner = '/';


    public function __toString() {
        $r = '';
        foreach ($this as $key => $value) {
            if($key == 'fieldJoiner'){
                continue;
            }
            if (isset($value)) {
                if ($r != '') {
                    $r .= $this->fieldJoiner;
                }
                if(is_array($value)){
                    $r.= $key . '=' . implode(',', $value);
                }else{
                    $r.= $key . '=' . $value;
                }
            }
        }
        return $r;
    }
}
