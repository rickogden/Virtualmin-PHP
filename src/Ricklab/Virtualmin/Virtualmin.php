<?php

/**
 * Virtualmin connection.
 * @author Rick Ogden
 */

namespace Ricklab\Virtualmin;

use GuzzleHttp\Client;
use Ricklab\Virtualmin\Exception\VirtualHostNotFoundException;

require_once __DIR__ . '/VirtualHost.php';

/**
 * Class Virtualmin
 * @package Ricklab\Virtualmin
 */
class Virtualmin
{

    /**
     * @var Client
     */
    protected $client;

    /**
     * @var string[]
     */
    protected $auth;

    /**
     * @param Client $guzzleClient A Guzzle client set up to interface with Virtualmin
     * @param string[] $auth The auth credentials.
     */
    public function __construct(Client $guzzleClient, $auth = [])
    {
        $this->client = $guzzleClient;
        $this->auth   = $auth;
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
            $options['domain'] = $domain->getDomain();
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
        $options['json'] = 1;
        $response = $this->client->request('GET',
            'virtual-server/remote.cgi',
            [
                'query' => $options,
                'auth'  => $this->auth
            ]
        );

        $json = \GuzzleHttp\json_decode($response->getBody()->getContents(), true);

        if ($json['status'] !== 'success') {
            throw new \RuntimeException($json['error']);
        }

        if (isset($json['data'])) {
            return $json['data'];
        } elseif (isset($json['output'])) {
            return $json['output'];
        } else {
            return $json;
        }
    }

    /**
     *
     * @param string $domain
     *
     * @throws VirtualHostNotFoundException if domain does not exist
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByDomain($domain)
    {

        $returnArray = $this->getVirtualHosts(['domain' => $domain]);
        if (count($returnArray) === 1) {
            return $returnArray[0];
        } else {
            throw new VirtualHostNotFoundException('Domain does not exist');
        }
    }

    /**
     *
     * @param string $username
     *
     * @throws VirtualHostNotFoundException if username does not exist
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByUsername($username)
    {


        $returnArray = $this->getVirtualHosts(['user' => $username]);
        if (count($returnArray) === 1) {
            return $returnArray[0];
        } else {
            throw new VirtualHostNotFoundException('User does not exist');
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