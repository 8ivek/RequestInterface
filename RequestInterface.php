<?php

namespace RequestInterface;

class RequestInterface {
    const VERSION = '1.0';
    const DEFAULT_TIMEOUT = 30;

    private $curl;

    private $error = false;
    private $errorCode = 0;
    private $errorMessage = null;

    private $url = null;

    private $attempts = 0;
    private $max_retries = 3;

    private $response;

    private $options = array();

    /**
     * Construct
     *
     * @access public
     * @param  $base_url
     * @throws \ErrorException
     */
    public function __construct($options){
        if (!extension_loaded('curl')) {
            throw new \ErrorException('cURL library is not loaded');
        }

        if(empty($options['CURLOPT_URL'])){
            throw new \ErrorException('CURLOPT_URL should not be empty');
        }
        $this->set_default_values($options);
        $this->setup_curl();
    }

    private function setup_curl(){
        $this->curl = curl_init($this->url);
        $this->set_options();
    }

    private function set_default_values($options){
        $this->options = $options;

        $default_options = [
            'CURLOPT_URL' => '',
            'CURLOPT_PORT' => '80',
            'CURLOPT_HTTPHEADER' => $this->set_curl_headers(),
            'CURLOPT_ENCODING' => 'UTF-8',
            'CURLOPT_AUTOREFERER' => true,
            'CURLOPT_REFERER' => "",
            'CURLOPT_HEADER' => false,
            'CURLOPT_MAXREDIRS' => 10,
            'CURLOPT_CONNECTTIMEOUT' => self::DEFAULT_TIMEOUT,
            'CURLOPT_TIMEOUT' => intval(self::DEFAULT_TIMEOUT + 10),//CURLOPT_TIMEOUT supposed to be greater than
            // CURLOPT_CONNECTTIMEOUT.
            'CURLOPT_USERAGENT' => $this->get_http_user_agent(),
            'CURLOPT_RETURNTRANSFER' => true
        ];

        $this->url = $this->options['CURLOPT_URL'];

        $this->options = array_merge($default_options,$this->options);//merge options

    }

    private function set_curl_headers(){
        $headers = 	array(
            "Content-Type:application/json",
            "Accept:application/json",
        );

        if(isset($this->options['CURLOPT_PORT']) && $this->options['CURLOPT_PORT'] == 7293){
            $headers[] = 'apikey:'.NODE_ACCESS_KEY;
        }
        return $headers;
    }

    private function set_options(){
        foreach($this->options as $key=>$value){
            switch ($key) {
                case "CURLOPT_PORT":
                    $success = curl_setopt($this->curl, constant($key), intval($value));
                    break;
                default:
                    $success = curl_setopt($this->curl, constant($key), $value);
            }
            if(!$success){
                die('Failure with curl_setopt, key: '.$key.' value:'.$value);
            }
        }
    }

    public function get_options(){
        return $this->options;
    }

    public function exec(){
        do {
            $this->attempts++;

            $this->response = curl_exec($this->curl);

            if(curl_errno($this->curl)){
                $request_failed = true;
            }else{
                $request_failed = false;
            }
        } while($request_failed == true && $this->attempts < $this->max_retries);
        return $this->response;
    }

    /**
     * Set Default User Agent
     *
     * @access public
     */
    private function get_http_user_agent()
    {
        $user_agent = 'RequestInterfaceClass/' . self::VERSION . ' (http://rt7.net)';
        $user_agent .= ' PHP/' . PHP_VERSION;
        $curl_version = curl_version();
        $user_agent .= ' curl/' . $curl_version['version'];
        return $user_agent;
    }

    /**
     * Destruct
     *
     * @access public
     */
    public function __destruct()
    {
        $this->close();
    }

    /**
     * Close
     *
     * @access public
     */
    private function close(){
        curl_close($this->curl);
    }

}