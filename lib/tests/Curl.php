<?php
/**
* Arquivo com as configurações usadas no webservice
* @author Carlos W. Gama
* @package Library
* @package Curl
* @since 1.0.0
**/

class Curl {
    
    /**
    * @access public
    * @param $url string
    * @param $dados array
    * @param $tipo (""|"POST"|"PUT"|"DELETE")
    * @param $meta (""|CURLOPT_POST|CURLOPT_CUSTOMREQUEST)
    * @param $GET bool
    * @return json
    */
    protected function openCurl($url, $dados="",$tipo="",$meta="",$GET=true) {

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        /* PARA METODOS POST, PUT e DELETE */
        if (!$GET) {
          curl_setopt($curl, $meta, $tipo);  
          curl_setopt($curl, CURLOPT_SAFE_UPLOAD, false);
          curl_setopt($curl, CURLOPT_POSTFIELDS, $dados);
        }      

        //curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json', 'Content-Type: application/json'));
        $curl_response = curl_exec($curl);
        $response = curl_getinfo($curl);
        $response['response'] = $curl_response;
        curl_close($curl);

        return $response;

    }

    /**
    * @access public
    * @param $url string
    * @return json
    */
    public function get($url) {
        return $this->openCurl($url);
    }

    /**
    * @access public
    * @param $url string
    * @param $dados array()
    * @return json
    */
    public function post($url, $dados="") {
        return $this->openCurl($url, $dados, "POST",CURLOPT_POST, false);
    }

    /**
    * @access public
    * @param $url string
    * @param $dados array()
    * @return json
    */
    public function put($url, $dados="") {
        return $this->openCurl($url, $dados, "PUT",CURLOPT_CUSTOMREQUEST, false);
    }

    /**
    * @access public
    * @param $url string
    * @param $dados array()
    * @return json
    */
    public function delete($url, $dados="") {
        return $this->openCurl($url, $dados, "DELETE",CURLOPT_CUSTOMREQUEST, false);
    }
}

?>