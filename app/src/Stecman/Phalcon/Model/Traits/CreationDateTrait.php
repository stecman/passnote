<?php

namespace Stecman\Phalcon\Model\Traits;

trait CreationDateTrait {

    /**
     * @var string
     */
    protected $created;

    public function beforeValidationOnCreate()
    {
        // Set the creation date
        $this->created = date('Y-m-d H:i:s');
    }

    /**
     * @param string $format - a format string for the built-in date() function
     * @return string
     */
    public function getDateCreated($format = null)
    {
        if ($this->created) {
            if ($format) {
                return date($format, strtotime($this->created));
            } else {
                return $this->created;
            }
        }
    }

}