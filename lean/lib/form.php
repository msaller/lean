<?php
namespace lean;

/**
 * HTML form abstraction class
 *
 * getErrors and isValid will trigger a validation once.
 * If different data is populated after, revalidate needs to be called to get correct results
 */
class Form {

    /**
     * Request method get
     */
    const METHOD_GET = 'get';

    /**
     * Request method post
     */
    const METHOD_POST = 'post';

    /**
     * @var string name of the form
     */
    private $name;

    /**
     * @var array instances of form\Element
     */
    private $elements = array();

    /**
     * Form action attribute
     *
     * @var string
     */
    private $action = '';

    /**
     * Form method attribute
     *
     * @var string
     */
    private $method = self::METHOD_POST;

    /**
     * @var null|bool
     */
    private $isValid = null;

    /**
     * Validation error messages
     *
     * @var array
     */
    private $errors = null;

    /**+
     * @param $name string
     */
    public function __construct($name) {
        $this->name = $name;
    }

    /**
     * Set the request method
     *
     * @param string $method
     *
     * @return Form
     */
    public function setMethod($method) {
        $this->method = $method;
        return $this;
    }

    /**
     * @return string
     */
    public function getMethod() {
        return $this->method;
    }

    /**
     * @param string $action
     * @return \lean\Form
     */
    public function setAction($action) {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getAction() {
        return $this->action;
    }

    /**
     * Add an element to the form
     *
     * @param form\Element $element
     */
    public function addElement(form\Element $element) {
        $this->elements[$element->getName()] = $element;
        $element->setId($this->name . '-' . $element->getName());
        return $element;
    }

    /**
     * @return array
     */
    public function getElements() {
        return $this->elements;
    }

    /**
     * Get an element or null if not existent
     *
     * @param $name
     *
     * @return form\Element|null
     */
    public function getElement($name) {
        return array_key_exists($name, $this->elements)
            ? $this->elements[$name]
            : null;
    }

    /**
     * Magic form element getter
     *
     * @magic
     * @param $name
     * @return form\Element|null
     */
    public function __get($name) {
        return $this->getElement($name);
    }

    /**
     * Populate an array of data to the elements
     *
     * @param array $data
     * @param bool  $names
     */
    public function populate(array $data, $names = false) {
        foreach ($this->elements as $element) {
            if($names) {
                // keys by name
                if (array_key_exists($element->getName(), $data)) {
                    $element->setValue($data[$element->getName()]);
                } else {
                    $element->setValue(null);
                }
            }
            else {
                // keys by id
                if (array_key_exists($element->getId(), $data)) {
                    $element->setValue($data[$element->getId()]);
                } else {
                    $element->setValue(null);
                }
            }
        }
    }

    public function open() {
        printf('<form action="%s" method="%s"/>', $this->action, $this->method);
    }

    public function close() {
        echo '</form>';
    }

    /**
     * Display an element
     *
     * @param $name string
     */
    public function display($name) {
        $this->getElement($name)->display();
    }

    public function isValid() {
        if($this->isValid === null)
            $this->validate();
        return $this->isValid;
    }

    /**
     * Return if every element of the form is valid
     * Fill errors array with element's errors
     * @param array $errors
     * @return bool
     */
    protected function validate() {
        $valid = true;
        $errors = array();
        foreach($this->elements as $name => $element) {
            $elementErrors = array();
            if(!$element->isValid($elementErrors)) {
                $errors[$name] = $elementErrors;
                $valid = false;
            }
        }
        $this->errors = $errors;
        $this->isValid = $valid;
        return $this->isValid();
    }

    /**
     * Revalidate the form
     * Must be called to get correct isValid and errors after repopulating data
     *
     * @return bool
     */
    public function revalidate() {
        return $this->validate();
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors() {
        if($this->errors === null)
            $this->validate();
        return $this->errors;
    }

    /**
     * Get values of all elements
     * @return array
     */
    public function getData() {
       $data = array();
        foreach($this->elements as $element) {
            $data[$element->getId()] = $element->getValue();
        }
        return $data;
    }
}