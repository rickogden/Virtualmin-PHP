<?php

/**
 * Virtualmin connection.
 * Requires HTTP_Request2 from paer
 * @author Rick Ogden
 */

namespace Ricklab\Virtualmin;

require_once 'VirtualHost.php';

class Virtualmin {

    
    protected $username = '';
    protected $password = '';
    protected $host = 'localhost';
    protected $port = 10000;

    public function __construct($username = null, $password = null, $host = null, $port = null) {
        if($username !== null) $this->username = $username;
        if($password !== null) $this->password = $password;
        if($host !== null) $this->host = $host;
        if($port !== null) $this->port = $port;
    }
    
    /**
     * 
     * @param string $domain
     * @param array $options
     * @return VirtualHost
     */
    public function registerDomain($domain, $options = []) {
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
    public function run($program, $options = []) {
        
        $options['program'] = $program;
        $options['multiline'] = '';
        $options['json'] = '1';
        
        $url = 'http://'.$this->host.':'.$this->port.'/virtual-server/remote.cgi';
        $request = new HTTP_Request2($url);
        $request->setAuth($this->username, $this->password);
        $request->getUrl()->setQueryVariables($options);
        
        return json_decode($request->send()->getBody());
    }
    
    /**
     * 
     * @param string $domain
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByDomain($domain) {
        return VirtualHost::getByDomain($this, $domain);
    }
    
    /**
     * 
     * @param string $username
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getVirtualHostByUsername($username) {
        return VirtualHost::getByUsername($this, $username);
    }
    
    /**
     * 
     * @return \Ricklab\Virtualmin\virtualHost
     */
    public function getAllVirtualHosts() {
        return VirtualHost::get($this);
    }

}