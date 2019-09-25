<?php

namespace BionConnection\MovistarM2Mglobal;

use InvalidArgumentException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Config;

class MovistarM2Mglobal {

    const STATUS_ACTIVATION_PENDANT = 'ACTIVATION_PENDANT';
    const STATUS_ACTIVATION_READY = 'ACTIVATION_READY';
    const STATUS_INACTIVE_NEW = 'INACTIVE_NEW';
    const STATUS_DEACTIVATED = "DEACTIVATED";
    const ACTIVE = "ACTIVE";
    const STATUS_RETIRED = "RETIRED";
    const TIMEOUT = 20;

    private $uri_key_pem = '';
    private $uri_ca_pem = '';
    private $api_endpoint = '';
    private $request_successful = false;
    private $last_error = '';
    private $last_response = array();
    private $last_request = array();
    private $response;

    public function __construct() {

        $this->uri_key_pem = config('MovistarM2Mglobal.uri_key_pem');
        $this->uri_ca_pem = config('MovistarM2Mglobal.uri_ca_pem');
        $this->response = null;
        $this->api_endpoint = config('MovistarM2Mglobal.url');
        $this->client = new Client(['http_errors' => false]);
    }

    public function getSims($icc = null, $inactive_new = null, $maxreg = null) {
        $param = array();
        if (isset($icc)) {
            $param["icc"] = $icc;
        }
        if (isset($inactive_new)) {
            $param["lifeCycleState"] = self::STATUS_INACTIVE_NEW;
        }
        if (isset($maxreg)) {
            $param["maxBatchSize"] = $maxreg;
        }

        return $this->makeRequest("get", "Inventory/v6/r12/sim", $param);
    }

    private function changeSimStatus($icc, $status) {
        $param = array("lifeCycleStatus" => $status);
        $this->makeRequest("put", "Inventory/v6/r12/sim/icc:" . $icc, $param);

        return $this->request_successful;
    }

    public function changeSimCommercialPlan($icc, $id_commercial_plan) {

        $param = array("commercialGroup" => $id_commercial_plan);

        $this->makeRequest("put", "Inventory/v6/r12/sim/icc:" . $icc, $param);
        return $this->request_successful;
    }

    private function changeExpenseLimit($icc, array $expense) {
        $expenseMovistar = array();


        foreach ($expense as $key => $value) {
            switch ($key) {
                case "LIMITDATA":
                    $limites["dataEnabled"] = "true";
                    $limites["dataLimit"] = $value * 1048576;
                    break;
                case "LIMITVOICEOUT":
                    $limites["voiceEnabled"] = "false";
                    break;
                case "LIMITSMSOUT":
                    $limites["smsEnabled"] = "true";
                    $limites["smsLimit"] = "$value";
                    break;
            }
        }
        $expenseMovistar["monthlyConsumptionThreshold"] = $limites;


        $this->makeRequest("put", "Inventory/v6/r12/sim/icc:" . $icc, $expenseMovistar);


        return $this->request_successful;
    }

    public function activateSim($icc, $id_commercial_plan = null, array $expense = null) {
        $sim = $this->getSims($icc);

        $respuesta = true;

        if (isset($sim)) {
            if (!$this->changeSimCommercialPlan($icc, $id_commercial_plan)) {
   
                return false;
            }; // cambio el plan comercial
            if (!$this->changeExpenseLimit($icc, $expense)) {

           
                return false;
            }; // cambio los limites
            if (!$this->changeSimStatus($icc, self::STATUS_ACTIVATION_READY)) {

                return false;
            }
        }
        return $respuesta;
    }

    public function suspendSim($icc) {

        return $this->changeSimStatus($icc, self::STATUS_DEACTIVATED); // Cambio el sim suspendida
    }

    public function reactivateSim($icc) {

        return $this->changeSimStatus($icc, self::ACTIVE); // Cambio el sim suspendida
    }

    public function terminateSim($icc) {
        return $this->changeSimStatus($icc, self::STATUS_DEACTIVATED); // Cambio el sim RETIRED
    }

    public function getLocationSim($icc) {

        return $this->makeRequest("get", "Inventory/v6/r12/sim/icc:" . $icc . "/location");
    }

    public function getSimStatusGSM($icc) {

        return $this->makeRequest("get", "Inventory/v6/r12/sim/icc:" . $icc . "/syncDiagnostic/gsm");
    }

    private function makeRequest($http_verb, $method, $args = array(), $timeout = self::TIMEOUT) {
        unset($this->response);
        $this->request_successful = false;
        $url = $this->api_endpoint . $method;

        switch ($http_verb) {
            case 'post':
                break;
            case 'get':
                $this->response = $this->client->get($url, ['cert' => $this->uri_ca_pem, 'ssl_key' => $this->uri_key_pem, 'query' => $args, 'timeout' => $timeout]);
                break;
            case 'delete':

                break;
            case 'patch':

                break;
            case 'put':

                $this->response = $this->client->put($url, ['cert' => $this->uri_ca_pem, 'ssl_key' => $this->uri_key_pem, 'body' => json_encode($args), 'timeout' => $timeout]);

                break;
        }

        switch ($this->response->getStatusCode()) {
            case 200:
                $this->request_successful = true;
                return json_decode($this->response->getBody());
                break;
            case 401;

                break;
            case 204: //borrado correctamente
                $this->request_successful = true;
                break;
            case 409:
                break;
            case 400:
                $this->last_error = $this->response;
             
                break;
            default :

                return json_decode($this->response->getBody());
                break;
        }
    }

}
