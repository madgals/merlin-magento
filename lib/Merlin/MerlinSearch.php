<?php

namespace Merlin;

class MerlinSearch extends MerlinEngine{
    private $url;

    function __construct($company, $environment, $instance) {
        parent::__construct($company, $environment, $instance, 'search');
        $this->url = "https://{$this->host}/$company.$environment.$instance/";
    }
    
    public function search(Search $search){
        return $this->rest('GET', $this->url.'search', $search);
    }
    
    public function multiSearch(MultiSearch $search){
        return $this->rest('GET', $this->url.'msearch', $search);
    }
    
    public function vrec(Vrec $vrec){
        return $this->rest('GET', $this->url.'vrec', $vrec);
    }
    
    public function typeahead(Typeahead $typeahead, $ip=''){
        return $this->rest('GET', $this->url.'typeahead', $typeahead, $ip);
    }
    
    public function feedbackClick(Feedback $f){
        return $this->rest('GET', $this->url.'feedback/click', $f);
    }
    
    public function feedbackCartAdd(Feedback $f){
        return $this->rest('GET', $this->url.'feedback/cart_add', $f);
    }
    
    public function feedbackPurchase(Feedback $f){
        return $this->rest('GET', $this->url.'feedback/purchase', $f);
    }
    
}

