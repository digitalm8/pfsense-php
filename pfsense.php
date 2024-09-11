<?php

class PfSenseAPI {
    protected $host;
    protected $username;
    protected $password;

    protected $debug        = 0;

    protected $def_proto    = 'https://';
    protected $def_endpoint = '/api/v2/';

    public function PfSenseAPI($host, $username, $password, $debug = 0) {
        $this->host     = $host;
        $this->username = $username;
        $this->password = $password;
        $this->debug    = $debug;
    }

    private function http_request($type, $mcc, $data) {
        $url = $this->def_proto . $this->host . $this->def_endpoint . $mcc;
        if ($this->debug)
            print("URL: {$url}\n");
        $ch = curl_init( $url );
        curl_setopt($ch, CURLOPT_USERPWD, $this->username . ':' . $this->password);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $jdata = json_encode($data, JSON_UNESCAPED_UNICODE|JSON_FORCE_OBJECT);
        if ($this->debug)
            print("{$type} REQUEST: {$jdata}\n");
        switch ($type) {
            case 'GET':
                curl_setopt($ch, CURLOPT_POST, false);
                if (count($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: multipart/form-data', 'Accept: application/json']);
                }
                break;
            case 'POST':
                curl_setopt($ch, CURLOPT_POST, true);
                if (count($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jdata);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
                }
                break;
            case 'DELETE':
            case 'PATCH':
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
                if (count($data)) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $jdata);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Accept: application/json']);
                }
                break;
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        $html = curl_exec($ch);
        if ($html === false) {
            $errno = curl_errno($ch);
            $message = curl_strerror($errno);
            throw new Exception( "cURL error ({$errno}): {$message}" );
        }
        if ($this->debug)
            print_r(json_decode($html, true));
        $info = curl_getinfo($ch);
        if ($info['http_code'] != 200)
            throw new Exception( "pfSense API HTTP error: {$info['http_code']} - {$url}" );
        curl_close($ch);
        return json_decode($html, true);
    }

    public function post($mcc, $data = array()) {
        return $this->http_request('POST', $mcc, $data);
    }

    public function get($mcc, $data = array()) {
        return $this->http_request('GET', $mcc, $data);
    }

    public function delete($mcc, $data = array()) {
        return $this->http_request('DELETE', $mcc, $data);
    }

    public function patch($mcc, $data = array()) {
        return $this->http_request('PATCH', $mcc, $data);
    }
}
