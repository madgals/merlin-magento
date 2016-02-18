<?php

namespace Merlin;

class MerlinCrud extends MerlinEngine {
    private $url;
    private $getParams;

    function __construct($company, $environment, $instance, $token, $user) {
        parent::__construct($company, $environment, $instance, 'upload');
        $this->url = "https://{$this->host}/";
        $this->getParams = http_build_query(array(
            'instance_name'=>$instance,
            'token'=>$token,
            'user'=>$user,
            'env'=>$environment,
            'company_id'=>$company
        ));
    }
    
    public function upload(Crud $crud){
        return $this->rest('POST', $this->url.'add?'.$this->getParams, json_encode($crud->getSubjects()));
    }
    public function update(Crud $crud){
        return $this->rest('POST', $this->url.'update?'.$this->getParams, json_encode($crud->getSubjects()));
    }
    public function delete(Crud $crud){
        return $this->rest('DELETE', $this->url.'delete?'.$this->getParams, json_encode($crud->getSubjects()));
    }
}
