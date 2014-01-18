<?php

/**
 * Virtualmin connection.
 * Requires HTTP_Request2 from paer
 * @author Rick Ogden
 */

namespace Ricklab\Virtualmin;

use Guzzle\Http\Client;

require_once __DIR__ . '/VirtualHost.php';

class Virtualmin
{


    private $username = '';
    private $password = '';
    protected $host = 'localhost';
    protected $port = 10000;

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
        return VirtualHost::getByDomain($this, $domain);

    }

    /**
     * Runs a program.
     *
     * @param string $program
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

        return json_decode($request->send()->getBody(), true);
    }

    /**
     *
     * @param string $domain
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByDomain($domain)
    {
        return VirtualHost::getByDomain($this, $domain);
    }

    /**
     *
     * @param string $username
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByUsername($username)
    {
        return VirtualHost::getByUsername($this, $username);
    }

    /**
     *
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getAllVirtualHosts()
    {
        return VirtualHost::get($this);
    }

}