<?php

namespace Merlin;

abstract class Validation {
    static function checkBoolean($v, $name){
        assert(is_bool($v), "The variable '$name' must be a boolean!");
    }
    
    static function checkFieldName($v){
        assert(
                preg_match('/^[a-z][a-z0-9_]*$/', $v),
                "'$v' is not a valid field name!\nField names must only contain 'a-z0-9_' and must start with a-z!"
                );
    }
}


