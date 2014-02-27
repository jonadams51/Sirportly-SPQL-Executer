<?php
final Class System {

    private $_expected_constants = array('DS', 'ROOT_DIR', 'API_TIMEOUT');

    final public function addToExpectedConsts($data){
        if(!empty($data)){
            if(is_array($data)){
                array_merge($this->_expected_constants, $data);
            } else {
                $this->_expected_constants[] = $data;
            }
        }
    }

    final public function configErrorCheck(){
        $message = false;

        try{
            if(!empty($this->_expected_constants) && is_array($this->_expected_constants)){
                foreach($this->_expected_constants as $name){
                    if(!defined(strtoupper($name))){
                        $message .= "Missing config option: $name";
                        throw new LoggedException($message);
                    }
                }
            }
        } catch(Exception $e) {
            return $message;
        }

        return $message;
    }
}