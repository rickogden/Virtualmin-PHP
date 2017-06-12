<?php
/**
 * Author: rick
 * Date: 12/06/2017
 * Time: 11:39
 */

namespace Ricklab\Virtualmin\Exception;

class VirtualminException extends \RuntimeException
{

    /** @var string */
    protected $command, $status, $fullError;

    public function __construct(array $json)
    {
        parent::__construct($json['error']);
        if (isset($json['status'])) {
            $this->status = $json['status'];
        }
        if (isset($json['command'])) {
            $this->command = $json['command'];
        }
        if (isset($json['full_error'])) {
            $this->message = $json['full_error'];
        }
    }

    /**
     * @return string
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string
     */
    public function getFullError()
    {
        return $this->fullError;
    }


}