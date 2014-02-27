<?php
final class SirportlyAPI {

    private $_token, $_secret, $_logging;
    private $_api_basepath = 'http://api.sirportly.com';

    public function __construct($token, $secret, $logging = false){
        $this->_token = $token;
        $this->_secret = $secret;
        $this->_logging = $logging;
    }

    final private function _sendRequest($path, $params = array()){
        $return = false;

        try{
            $path = $this->_api_basepath.$path;

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$path);

            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                "X-Auth-Token:{$this->_token}",
                "X-Auth-Secret:{$this->_secret}",
            ));

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, API_TIMEOUT);

            if(!empty($params)){
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
            }

            $return = curl_exec($ch);

            if($this->_logging){
                error_log(date('d/m/Y G:i:s').PHP_EOL.'path: '.$path.PHP_EOL.'params: '.$params.PHP_EOL.'return: '.$return.PHP_EOL.'*******************'.PHP_EOL, 3, ROOT_DIR.'logs'.DS.'sirportly.log');
            }

            if(!$return){
                throw new LoggedException('Sirportly API unresponsive');
            } else {
                $return = json_decode($return);

                if(isset($return->error)){
                    throw new LoggedException("Sirportly API returned an error: {$return->error}");
                }
            }

            curl_close($ch);

        } catch (LoggedException $e) {
            return false;
        }

        return $return;
    }

    final public function execSPQL($query){
        $path = '/api/v2/tickets/spql';
        $params = array(
            'spql' => $query,
        );

        return $this->_sendRequest($path, $params);
    }
}