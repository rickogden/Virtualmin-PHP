<?php

namespace Ricklab\Virtualmin;
require_once 'Virtualmin.php';
/**
 * Virtualmin connection.
 * Requires HTTP_Request2 from paer
 * @author Rick Ogden
 */

class VirtualHost {

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
    public static function get(Virtualmin $virtualmin, $args = []) {
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
    public static function getByUsername(Virtualmin $virtualmin, $username) {
        try {
            $returnArray = self::get($virtualmin, ['user' => $username]);
            if (length($returnArray) == 1)
                return $returnArray[0];
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
    public static function getByDomain(Virtualmin $virtualmin, $domain) {
        try {
            $returnArray = self::get($virtualmin, ['user' => $username]);
            if (length($returnArray) == 1)
                return $returnArray[0];
        } catch (Exception $e) {
            return null;
        }
    }

    public function __construct(Virtualmin $virtualmin, $name, $hostDetails = []) {
        $this->serverName = $name;
        $this->fullDetails = $hostDetails;
        $this->virtualmin = $virtualmin;
    }
    
    

}
