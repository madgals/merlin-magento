<?php

namespace Merlin;

class Feedback extends MerlinGetFunction {
    protected $qid;
    protected $docids;
    protected $uid;
    protected $sid;
    
    function __construct($qid, $docids, $uid=null, $sid=null) {
        $this->qid = $qid;
        $this->docids = $docids;
        $this->uid = $uid;
        $this->sid = $sid;
    }

}
