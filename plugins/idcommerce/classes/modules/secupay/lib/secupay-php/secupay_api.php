<?php
/**
 * Helper classes that creates HTTP requests to Secupay API
 *
 */

/**
 * @package Secupay
 * @copyright 2013 Secucard Projekt KG
 */
define('SECUPAY_HOST', apply_filters('id_secupay_host', 'api.secupay.ag'));
//define('SECUPAY_HOST', 'api-dist.secupay-ag.de');
define('SECUPAY_URL', 'https://'.SECUPAY_HOST.'/payment/');
define('SECUPAY_PATH', '/payment/');
define('SECUPAY_PORT', 443);
define('SECUPAY_HOST_PUSH', 'connect.secupay.ag');
//define('SECUPAY_HOST_PUSH', 'dist.secupay-ag.de');
define('SECUPAY_USER_AGENT', 'php API lib test 0.0.2');

if ( !function_exists('seems_utf8')) {
    function seems_utf8($Str) {
        for ($i=0; $i<strlen($Str); $i++) {
            if (ord($Str[$i]) < 0x80) continue; # 0bbbbbbb
            else if ((ord($Str[$i]) & 0xE0) == 0xC0) $n=1; # 110bbbbb
            else if ((ord($Str[$i]) & 0xF0) == 0xE0) $n=2; # 1110bbbb
            else if ((ord($Str[$i]) & 0xF8) == 0xF0) $n=3; # 11110bbb
            else if ((ord($Str[$i]) & 0xFC) == 0xF8) $n=4; # 111110bb
            else if ((ord($Str[$i]) & 0xFE) == 0xFC) $n=5; # 1111110b
            else return false; // Does not match any model

            for ($j=0; $j<$n; $j++) {
                // n bytes matching 10bbbbbb follow ?
                if ((++$i == strlen($Str)) || ((ord($Str[$i]) & 0xC0) != 0x80)) {
                    return false;
                }
            }
        }
        return true;
    }
}

if ( !function_exists('utf8_ensure')){
    function utf8_ensure($data) {
        if (is_string($data)) {
            return seems_utf8($data)? $data: utf8_encode($data);
        } else if (is_array($data)) {
            foreach ($data as $key => $value) {
                $data[$key] = utf8_ensure($value);
            }
            unset($value);
            unset($key);
        } else if (is_object($data)) {
            foreach ($data as $key => $value) {
                $data->$key = utf8_ensure($value);
            }
            unset($value);
            unset($key);
        }
        return $data;
    }
}

if (!class_exists("secupay_log")) {

    /**
     * logging class
     */
    class secupay_log {
        static $logfile = "splog.php";

        /**
         * logging function
         *
         * @param bool log - if false, the log will not be done
         */
        static function log($log) {
            if(!$log){
                return;
            }

            //prevent access to logfile
            if(!file_exists(self::$logfile)) {
                file_put_contents(self::$logfile, "<?php die('Nothing to see here.'); ?>\n", FILE_APPEND);
            }

            $date = date("r");
            $x = 0;
            foreach(func_get_args() as $val){
                $x++;
                if ($x == 1)
                    continue;
                if (is_string($val) || is_numeric($val)) {
                    file_put_contents(self::$logfile, "[{$date}] {$val}\n", FILE_APPEND);
                } else {
                    file_put_contents(self::$logfile, "[{$date}] " . print_r($val, true) . "\n", FILE_APPEND);
                }
            }
        }
    }

}

if (!class_exists("secupay_api")) {

    /**
     * Class that creates request for API
     */
    class secupay_api {

        var $req_format,
            $data,
            $req_function,
            $sent_req,
            $error,
            $sp_log,
            $language;

        /**
         * Contructor
         */

        public function __construct($params, $req_function = 'init', $format = 'application/json', $sp_log = false, $language = 'de_de') {
            $this->req_function = $req_function;
            $this->req_format = $format;
            $this->sp_log = $sp_log;
            $this->language = $language;
            $this->data = array( 'data' => $params);
        }

        /**
         * Public class that returns answer from API
         *
         * @returns object type secupay_api_response
         */
        function request() {
            $rc = null;
            if (function_exists("curl_init")) {
                $rc = $this->request_by_curl();
            } else {
                $rc = $this->request_by_socketstream();
            }

            return $rc;
        }

        private function cleanData() {
            $this->data = utf8_ensure($this->data);
        }

        /**
         * Function that creates Curl request
         */
        function request_by_curl() {
            $this->cleanData();
            $_data = json_encode($this->data);

            $http_header = array(
                    'Accept: ' . $this->req_format,
                    'Content-Type: application/json',
                    'Accept-Language: ' . $this->language,
                    'User-Agent: ' . SECUPAY_USER_AGENT,
                    'Content-Length: ' . strlen($_data)
                    );

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, SECUPAY_URL . $this->req_function);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $http_header);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $_data);

            secupay_log::log($this->sp_log, 'CURL request for ' . SECUPAY_URL . $this->req_function . ' in format : ' . $this->req_format . ' language: ' . $this->language);
            secupay_log::log($this->sp_log, $_data);

            $rcvd = curl_exec($ch);
            secupay_log::log($this->sp_log, 'Response: ' . $rcvd);

            $this->sent_data = $_data;
            $this->recvd_data = $rcvd;

            curl_close($ch);
            return $this->parse_answer($this->recvd_data);
        }

        function parse_answer($ret) {
            switch (strtolower($this->req_format)) {
                case "application/json":
                    $answer = json_decode($ret);
                    break;
                case "text/xml":
                    $answer = simplexml_load_string($ret);
                    break;
            }
            $api_response = new secupay_api_response($answer);
            return $api_response;
        }

        /**
         * Function that request by socketstream (when CURL library is not available)
         */
        function request_by_socketstream() {

            $this->cleanData();
            $_data = json_encode($this->data);
            $rcvd = "";
            $rcv_buffer = "";
            $fp = fsockopen('ssl://' . SECUPAY_HOST, SECUPAY_PORT, $errstr, $errno);

            if (!$fp) {
                $this->error = "can't connect to secupay api";
                return false;
            }
            $req = "POST ".SECUPAY_PATH . $this->req_function." HTTP/1.1\r\n";
            $req.= "Host: ".SECUPAY_HOST."\r\n";
            $req.= "Content-type: application/json; Charset:UTF8\r\n";
            $req.= "Accept: ".$this->req_format."\r\n";
            $req.= "User-Agent: ".SECUPAY_USER_AGENT."\r\n";
            $req.= "Accept-Language: ".$this->language."\r\n";
            $req.= "Content-Length: ". strlen($_data). "\r\n";
            $req.= "Connection: close\r\n\r\n";
            $req.= $_data;

            secupay_log::log($this->sp_log, 'SOCKETSTREAM request for '. SECUPAY_URL . $this->req_function.' in format : '.$this->req_format .' language: '.$this->language);
            secupay_log::log($this->sp_log, $_data);

            fputs($fp, $req);

            while (!feof($fp)) {
                $rcv_buffer = fgets($fp, 128);
                $rcvd .= $rcv_buffer;
            }
            fclose($fp);

            $pos = strpos($rcvd, "\r\n\r\n");
            $rcvd = substr($rcvd, $pos + 4);

            secupay_log::log($this->sp_log, 'Response: ' . $rcvd);

            $this->sent_data = $_data;
            $this->recvd_data = $rcvd;

            return $this->parse_answer($this->recvd_data);
        }

        static function get_api_version() {
            return '2.11';
        }

    }

}

if (!class_exists("secupay_api_response")) {

    /**
     * this class should be a wrapper for secupay response
     */
    class secupay_api_response {

        var $status,
            $data,
            $errors,
            $raw_data;

        /**
         * Contructor
         */
        public function __construct($answer) {

            $this->status = $answer->status;
            $this->errors = $answer->errors;
            $this->data = $answer->data;
            $this->raw_data = $answer;
        }

        function check_response($log_error = false) {

            if (strtolower($this->status) != 'ok') {
                secupay_log::log($log_error, "secupay_api_response status: ", $this->status);
                return false;
            };
            if (count($this->errors) > 0) {
                secupay_log::log($log_error, "secupay_api_response error: ", $this->errors);
                return false;
            }
            if (count($this->data) == 0) {
                secupay_log::log($log_error, "secupay_api_response error: no data in response");
                return false;
            }
            return true;
        }

        function get_hash() {
            if (isset($this->data->hash)) {
                return $this->data->hash;
            }
            return false;
        }

        function get_iframe_url() {
            if (isset($this->data->iframe_url)) {
                return $this->data->iframe_url;
            }
            return false;
        }

        function get_status($log_error = false) {
            secupay_log::log($log_error, "secupay_api_response get_status: " . $this->status);
            if (isset($this->status)) {
                return $this->status;
            }
            return false;
        }

        function get_error_message($log_error = false) {
            if (empty($this->errors)) {
                return false;
            }
            $message = '';
            foreach ($this->errors as $error) {
                $message .= '(' . $error->code . ') ' . $error->message . '<br>';
                if (isset($error->field)) {
                    $message .= $error->field . '<br>';
                }
            }
            secupay_log::log($log_error, "secupay_api_response get_error_message: " . $message);
            return $message;
        }

        function get_error_message_user($log_error = false) {
            if (empty($this->errors)) {
                return false;
            }
            $message = '';
            foreach ($this->errors as $error) {
                $message .= '(' . $error->code . ')';
                if ($this->status == 'failed') {
                    $message .= ' ' . $error->message;
                }
            }
            secupay_log::log($log_error, "secupay_api_response get_error_message_user: " . $message);
            return $message;
        }
    }
}