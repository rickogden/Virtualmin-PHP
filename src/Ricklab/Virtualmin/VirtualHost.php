<?php

namespace Ricklab\Virtualmin;

require_once __DIR__ . '/Virtualmin.php';
/**
 * Virtualmin connection.
 * Requires HTTP_Request2 from paer
 * @author Rick Ogden
 */

class VirtualHost
{

    protected $serverName;
    protected $fullDetails = [];

    /**
     *
     * @var Virtualmin
     */
    protected $virtualmin;

    /**
     *
     * @param \Ricklab\Virtualmin\Virtualmin $virtualmin
     * @param string $args
     * @return \self
     * @throws \Exception
     */
    public static function get(Virtualmin $virtualmin, $args = [])
    {
        $returnArray = $virtualmin->run('list-domains', $args);
        $hosts = [];
        if ($returnArray['status'] == 'success') {
            foreach ($returnArray['data'] as $account) {
                $hosts[] = new self($virtualmin, $account['name'], $account['values']);
            }
        } else {
            throw new \Exception($returnArray['error']);
        }

        return $hosts;
    }

    /**
     *
     * @param \Ricklab\Virtualmin\Virtualmin $virtualmin
     * @param string $username
     * @return \self
     */
    public static function getByUsername(Virtualmin $virtualmin, $username)
    {
        try {
            $returnArray = self::get($virtualmin, ['user' => $username]);
            if (length($returnArray) == 1) {
                return $returnArray[0];
            }
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     *
     * @param \Ricklab\Virtualmin\Virtualmin $virtualmin
     * @param string $domain
     * @return \self
     */
    public static function getByDomain(Virtualmin $virtualmin, $domain)
    {
        try {
            $returnArray = self::get($virtualmin, ['domain' => $domain]);
            if (length($returnArray) == 1) {
                return $returnArray[0];
            }
        } catch (Exception $e) {
            return null;
        }
    }

    public function __construct(Virtualmin $virtualmin, $name, $hostDetails = [])
    {
        $this->serverName = $name;
        $this->fullDetails = $hostDetails;
        $this->virtualmin = $virtualmin;
    }

    public function emailUser($subject, $message)
    {

    }

    /**
     *
     * @param string $password
     * @return \Ricklab\Virtualmin\VirtualHost
     * @throws \Exception
     */
    public function changePassword($password)
    {

        return $this->modify(['pass' => $password]);
    }

    /**
     *
     * @param int $quota
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    public function changeQuota($quota)
    {
        return $this->modify(['quota' => $quota]);
    }

    /**
     *
     * @param type $email
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    public function changeEmail($email)
    {
        return $this->modify(['email' => $email]);
    }

    /**
     *
     * @param mixed $parameters
     * @return \Ricklab\Virtualmin\VirtualHost
     * @throws \Exception
     */
    protected function modify($parameters = [])
    {
        $return = $this->virtualmin->run('modify-domain', array_merge(['domain' => $this->serverName], $parameters));
        if ($return['status'] == 'failure') {
            throw new \Exception($return['error']);
        }

        return $this;
    }

    public function __get($property)
    {
        if ($property == 'serverName') {
            return $this->serverName;
        } else {
            return $this->fullDetails[$property];
        }
    }

}
