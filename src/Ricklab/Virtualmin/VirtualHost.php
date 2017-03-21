<?php

namespace Ricklab\Virtualmin;

require_once __DIR__ . '/Virtualmin.php';
/**
 * VirtualHost.
 * @author Rick Ogden
 */

class VirtualHost
{

    /**
     * @var string
     */
    protected $domain;
    /**
     * @var string
     */
    protected $email;
    /**
     * @var string
     */
    protected $username;
    /**
     * @var array
     */
    protected $fullDetails = [];

    /**
     *
     * @var \Ricklab\Virtualmin\Virtualmin
     */
    protected $virtualmin;


    /**
     * @param Virtualmin $virtualmin
     * @param string $domain domain name
     * @param array $hostDetails rest of the details returned from Virtualmin
     */
    public function __construct(Virtualmin $virtualmin, $domain, $hostDetails = [])
    {
        $this->domain = $domain;
        $this->fullDetails = $hostDetails;
        if (isset($hostDetails['contact_email'])) {
            $this->email = $hostDetails['contact_email'];
        }
        if (isset($hostDetails['username'])) {
            $this->username = $hostDetails['username'];
        }
        $this->virtualmin = $virtualmin;
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
        $this->modify(['quota' => $quota]);
        $this->fullDetails['user_quota'] = $quota;

        return $this;
    }

    /**
     *
     * @param string $email
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    public function changeEmail($email)
    {
        $this->modify(['email' => $email]);
        $this->email = $email;

        return $this;
    }

    /**
     * @param string $name database name
     * @param string $type the database type: "mysql" or "postgres"
     *
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    public function createDatabase($name, $type = 'mysql')
    {

        $this->virtualmin->run(
            'create-database',
            [
                'domain' => $this->domain,
                'name' => $name,
                'type' => $type
            ]
        );

        return $this;

    }

    /**
     * @return array
     */
    public function listDatabases()
    {
        return $this->virtualmin->run('list-databases', ['domain' => $this->domain]);
    }

    /**
     *
     * @param mixed $parameters
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    protected function modify($parameters = [])
    {
        $parameters['domain'] = $this->domain;
        $this->virtualmin->run('modify-domain', $parameters);
        foreach ($parameters as $key => $value) {
            $this->fullDetails[$key] = $value;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @return array
     */
    public function getFullDetails()
    {
        return $this->fullDetails;
    }





}
