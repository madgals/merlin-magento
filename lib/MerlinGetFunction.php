<?php

namespace Merlin;

abstract class MerlinGetFunction {

    protected $uid;
    protected $sid;

    function getUrlString() {
        return $this->__toString();
    }

    function __toString() {
        $r = '';
        foreach ($this as $key => $value) {
            if ($key == 'fieldJoiner') {
                continue;
            }
            if (isset($value)) {
                if ($r != '') {
                    $r .= '&';
                }
                switch ($key) {
                    case 'filter':
                    case 'facet':
                    case 'qc':
                        if (is_array($value)) {
                            foreach ($value as $key2 => $value2) {
                                if ($key2 != 0) {
                                    $r.="&";
                                }
                                $r .= $key . '=' . urlencode($value2);
                            }
                            break;
                        }
                    case 'sort':
                    case 'fields':
                        if (is_array($value)) {
                            $value = implode(',', $value);
                        }
                    default :
                        $r .= $key . '=' . urlencode($value);
                }
            }
        }
        return $r;
    }
    
    function setUid($uid) {
        $this->uid = $uid;
    }

    function setSid($sid) {
        $this->sid = $sid;
    }



}
