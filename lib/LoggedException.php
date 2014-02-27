<?php
class LoggedException extends Exception
{
    public function __construct($message, $code = 0, Exception $previous = null) {
        parent::__construct($message, $code, $previous);
        $this->_log();
    }

    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }

    private function _log(){
        error_log(date('d/m/Y G:i:s').' >> '.$this->__toString(), 3, ROOT_DIR.'logs'.DS.'exception.log');
    }
}