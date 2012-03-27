<?php
namespace lean\form;

interface Validator {

    /**
     * @abstract
     * @param mixed  $value
     * @param array  $messages
     * @internal param string $message
     * @return boolean
     */
    public function isValid($value, &$messages = array());
}

abstract class Validator_Abstract implements Validator {
    private $messages;
    public function __construct($messages = array()) {
        $this->messages = $messages;
    }

    public function getErrorMessage($error) {
        if(!array_key_exists($error, $this->messages))
            throw new \lean\Exception("Error message not found for key '$error'");
        return $this->messages[$error];
    }
}

class Validator_Mandatory extends Validator_Abstract {

    const ERR_NO_VALUE = 'no_value_set';

    public function __construct($msg = '') {
        parent::__construct(array(self::ERR_NO_VALUE => $msg));
    }

    /**
     * @param mixed  $value
     * @param string $message
     * @return boolean
     */
    public function isValid($value, &$messages = array()) {
        if($value === null || is_string($value) && !strlen($value)) {
            $messages[self::ERR_NO_VALUE] = $this->getErrorMessage(self::ERR_NO_VALUE);
            return false;
        }

        return true;
    }
}

class Validator_Equal extends Validator_Abstract {

    const ERR_NOT_EQUAL = 'not_equal';

    /**
     * @var Element
     */
    private $compare;

    /**
     * @param array $messages
     * @param \lean\form\Element $compareElement
     */
    public function __construct($msg, Element $compareElement) {
        parent::__construct(array(self::ERR_NOT_EQUAL => $msg));

        $this->compare = $compareElement;
    }

    /**
     * @param mixed  $value
     * @param string $message
     * @return boolean
     */
    public function isValid($value, &$messages = array()) {
        if($value != $this->compare->getValue()) {
            $messages[self::ERR_NOT_EQUAL] = $this->getErrorMessage(self::ERR_NOT_EQUAL);
            return false;
        }

        return true;
    }
}

class Validator_Custom extends Validator_Abstract {

    const ERR_NOT_VALID = 'not_valid';

    private $callback;

    public function __construct($msg, $callable) {
        parent::__construct(array(self::ERR_NOT_VALID => $msg));
        $this->callback = $callable;
        if(!is_callable($callable))
            throw new \lean\Exception('Second argument has to be a valid callback!');
    }

    /**
     * @param mixed  $value
     * @param array  $messages
     * @internal param string $message
     * @return boolean
     */
    public function isValid($value, &$messages = array()) {
        if(!$this->callback($value)) {
            $messages[self::ERR_NOT_VALID] = $this->getErrorMessage(self::ERR_NOT_VALID);
            return false;
        }

        return true;
    }
}