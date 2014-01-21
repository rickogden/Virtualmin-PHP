<?php

/**
 * Virtualmin connection.
 * @author Rick Ogden
 */

namespace Ricklab\Virtualmin;

use Guzzle\Http\Client;

require_once __DIR__ . '/VirtualHost.php';

/**
 * Class Virtualmin
 * @package Ricklab\Virtualmin
 */
class Virtualmin
{


    /**
     * @var null|string
     */
    private $username = '';
    /**
     * @var null|string
     */
    private $password = '';
    /**
     * @var null|string
     */
    protected $host = 'localhost';
    /**
     * @var int|null
     */
    protected $port = 10000;

    /**
     * @param null $username Virtualmin admin username
     * @param null $password Virtualmin admin password
     * @param null $host Virtualmin host (defaults to "localhost")
     * @param null $port Virtualmin port (defaults to 10000)
     */
    public function __construct($username = null, $password = null, $host = null, $port = null)
    {
        if ($username !== null) {
            $this->username = $username;
        }
        if ($password !== null) {
            $this->password = $password;
        }
        if ($host !== null) {
            $this->host = $host;
        }
        if ($port !== null) {
            $this->port = $port;
        }
    }

    /**
     *
     * @param string $domain
     * @param array $options
     * @return VirtualHost
     */
    public function registerDomain($domain, $options = [])
    {
        $options['domain'] = $domain;
        $this->run('create-domain', $options);
        return $this->getVirtualHostByDomain($domain);

    }

    public function deleteDomain($domain, $options = [])
    {
        if ($domain instanceof VirtualHost) {
            $options['domain'] = $domain->domain;
        } else {
            $options['domain'] = $domain;
        }
        $this->run('delete-domain', $options);

        return $this;
    }

    /**
     * Runs a program.
     *
     * @param string $program
     * @throws \RuntimeException if there is a virtualmin error
     * @param array $options
     * @return array results
     */
    public function run($program, array $options = [])
    {

        $options['program'] = $program;
        $options['multiline'] = '';
        $options['json'] = '1';

        $client = new Client('https://' . $this->host . ':' . $this->port);
        $client->setSslVerification(false);
        $request = $client->get(
            '/virtual-server/remote.cgi',
            [],
            [
                'query' => $options
            ]
        );
        $request->setAuth($this->username, $this->password);

        $response = json_decode($request->send()->getBody(), true);

        if ($response['status'] !== 'success') {
            throw new \RuntimeException($response['error']);
        }

        if (isset($response['data'])) {
            return $response['data'];
        } else {
            return $response;
        }
    }

    /**
     *
     * @param string $domain
     * @throws \InvalidArgumentException if domain does not exist
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByDomain($domain)
    {

        $returnArray = $this->getVirtualHosts(['domain' => $domain]);
        if (count($returnArray) === 1) {
            return $returnArray[0];
        } else {
            throw new \InvalidArgumentException('Domain does not exist');
        }
    }

    /**
     *
     * @param string $username
     * @throws \InvalidArgumentException if username does not exist
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByUsername($username)
    {


        $returnArray = $this->getVirtualHosts(['user' => $username]);
        if (count($returnArray) === 1) {
            return $returnArray[0];
        } else {
            throw new \InvalidArgumentException('User does not exist');
        }
    }

    /**
     * @param array $args for filtering vhosts returned
     * @return array of \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHosts(array $args = [])
    {
        $returnArray = $this->run('list-domains', $args);
        $hosts = [];
        foreach ($returnArray as $account) {
            $hosts[] = new VirtualHost($this, $account['name'], $account['values']);
        }
        return $hosts;
    }

}