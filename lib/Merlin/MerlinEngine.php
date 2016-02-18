<?php

namespace Merlin;

abstract class MerlinEngine {

    protected $host;
    protected $company;
    protected $environment;
    protected $instance;
    protected $curl;
    static $HOSTS = array(
        "prod" => "-prod.search.blackbird.am",
        "stage" => "-staging.search.blackbird.am",
        "dev" => "-dev.search.blackbird.am");

    function __construct($company, $environment, $instance, $function) {
        $this->company = $company;
        $this->environment = $environment;
        $this->instance = $instance;
        $this->curl = curl_init();

        if (isset($this::$HOSTS[$environment])) {
            $this->host = $function . $this::$HOSTS[$environment];
        } else {
            $this->host = $environment;
        }
    }

    function rest($method, $url, $data = false, $ip = '') {
        $checkssl = true;
        if ($this->environment == 'dev' || $this->environment == 'stage') {
            $checkssl = false;
        }

        $curl = $this->curl;
        switch ($method) {
            case "POST":
            case "PUT":
                curl_setopt($curl, constant("CURLOPT_$method"), 1);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            case "DELETE":
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
                curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
                if ($data) {
                    curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
                }
                break;
            default:
                if ($data) {
                    $url = sprintf("%s?%s", $url, $data);
                }
        }

        curl_setopt($curl, CURLOPT_HTTPHEADER, array("X-Forwarded-For:$ip", "X-Real-IP:$ip"));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, $checkssl);

        $result = curl_exec($curl);
        if ($result === false) {
            throw new MerlinException("Curl error:\n" . curl_error($curl));
        }

        $dec = json_decode($result);

        return $dec;
    }

    function getHost() {
        return $this->host;
    }
    
    function close(){
        curl_close($this->curl);
    }

}
