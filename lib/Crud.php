<?php

namespace Merlin;

class Crud {
    protected $subjects;
    
    function __construct() {
        $this->subjects = array();
    }
    
    function addSubject($s){
        $this->subjects[] = $s;
    }
    
    function getSubjects(){
        return array('subjects'=>$this->subjects);
    }

}
