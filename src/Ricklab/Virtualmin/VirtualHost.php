<?php

namespace Ricklab\Virtualmin;

use Ricklab\Virtualmin\Exception\VirtualminException;

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
        $this->domain      = $domain;
        $this->fullDetails = $hostDetails;
        $this->virtualmin  = $virtualmin;
    }

    /**
     *
     * @param string $password
     *
     * @return \Ricklab\Virtualmin\VirtualHost
     * @throws VirtualminException
     */
    public function changePassword($password)
    {

        return $this->modify(['pass' => $password]);
    }

    /**
     *
     * @param int $quota
     *
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
     *
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    public function changeEmail($email)
    {
        $this->modify(['email' => $email]);
        $this->fullDetails['contact_email'] = [$email];

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
                'name'   => $name,
                'type'   => $type
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
     *
     * @return \Ricklab\Virtualmin\VirtualHost
     */
    protected function modify($parameters = [])
    {
        $parameters['domain'] = $this->domain;
        $this->virtualmin->run('modify-domain', $parameters);
        foreach ($parameters as $key => $value) {
            $this->fullDetails[$key] = [$value];
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
        return $this->getFullDetails()['contact_email'][0];
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->getFullDetails()['username'][0];
    }

    /**
     * @return array
     */
    public function getFullDetails()
    {
        if (empty($this->fullDetails)) {
            $response          = $this->virtualmin->run('list-domains', ['domain' => $this->domain]);
            $this->fullDetails = $response[0]['values'];
        }
        return $this->fullDetails;
    }


    public function changeMysqlPassword($password)
    {
        return $this->changeDbPassword('mysql', $password);

    }

    public function changePostgresPassword($password)
    {
        return $this->changeDbPassword('postgres', $password);
    }

    protected function changeDbPassword($db, $password)
    {
        return $this->virtualmin->run('modify-database-pass',
            ['domain' => $this->getDomain(), 'type' => $db, 'pass' => $password]);
    }

    public function createPostgresDatabase($dbname)
    {
        return $this->createDatabase($dbname, 'mysql');
    }

    public function createMysqlDatabase($dbname)
    {
        $this->createDatabase($dbname, 'postgres');
    }



}
