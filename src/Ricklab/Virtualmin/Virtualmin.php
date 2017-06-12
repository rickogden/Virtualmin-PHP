<?php

/**
 * Virtualmin connection.
 * @author Rick Ogden
 */

namespace Ricklab\Virtualmin;

use GuzzleHttp\ClientInterface;
use Ricklab\Virtualmin\Exception\VirtualHostNotFoundException;
use Ricklab\Virtualmin\Exception\VirtualminException;

require_once __DIR__ . '/VirtualHost.php';

/**
 * Class Virtualmin
 * @package Ricklab\Virtualmin
 */
class Virtualmin
{

    /**
     * @var ClientInterface
     */
    protected $client;

    /**
     * @var string[]
     */
    protected $auth;

    /**
     * @param ClientInterface $guzzleClient A Guzzle client set up to interface with Virtualmin
     * @param string[] $auth The auth credentials.
     */
    public function __construct(ClientInterface $guzzleClient, $auth = [])
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
     *
     * @throws VirtualminException if there is a virtualmin error
     *
     * @param array $options
     * @param bool $multiline if a multiline response is required. Defaults to true.
     *
     * @return array results
     */
    public function run($program, array $options = [], $multiline = true)
    {

        $options['program'] = $program;
        if ($multiline)
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
            throw new VirtualminException($json);
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
     * @param bool $populateFull populate the full details from the start (slow but prevents 2 requests)
     *
     * @throws VirtualHostNotFoundException if domain does not exist
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByDomain($domain, $populateFull = false)
    {

        $returnArray = $this->getVirtualHosts(['domain' => $domain], $populateFull);
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
     * @param bool $populateFull populate the full details from the start (slow but prevents 2 requests)
     *
     * @return virtualHost if username does not exist
     * @throws VirtualHostNotFoundException if username does not exist
     */
    public function getVirtualHostByUsername($username, $populateFull = false)
    {


        $returnArray = $this->getVirtualHosts(['user' => $username], $populateFull);
        if (count($returnArray) === 1) {
            return $returnArray[0];
        } else {
            throw new VirtualHostNotFoundException('User does not exist');
        }
    }

    /**
     * @param $username
     * @param bool $populateFull populate the full details from the start (slow but prevents 2 requests)
     *
     * @return VirtualHost[]
     */
    public function getVirtualHostsByUsername($username, $populateFull = false)
    {
        $returnArray = $this->getVirtualHosts(['user' => $username], $populateFull);

        return $returnArray;
    }

    /**
     * @param array $args for filtering vhosts returned
     * @param bool $populateFull populate the full details from the start (slow but prevents 2 requests)
     * @return VirtualHost[]
     */
    public function getVirtualHosts(array $args = [], $populateFull = false)
    {

        if ( ! $populateFull)
            $args['name-only'] = '';
        $returnArray = $this->run('list-domains', $args, $populateFull);
        $hosts = [];
        foreach ($returnArray as $account) {
            $hosts[] = new VirtualHost($this, $account['name'], $account['values']);
        }
        return $hosts;
    }

}