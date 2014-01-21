Virtualmin PHP API
===================

Starting to write an OO Virtualmin library in PHP. The aim is to allow full
management of a Virtualmin server through a set of PHP classes. Starting off
with minimal features but plan to expand it (and contributions welcome).

Requirements
------------
* PHP 5.4
* Guzzle HTTP Client

Usage
-----

    $virtualmin = new \Ricklab\Virtualmin\Virtualmin('user', 'pass', 'localhost');

    $vhost = $virtualmin->getVirtualHostByUsername('someusername');

    $virtualmin->registerDomain('somedomain.com',['pass' => 'somepassword']);

    $vhost2 = $virtualmin->getVirtualHostByDomain('somedomain.com');

    $vhost2->changePassword('anotherpassword');

    $vhost2->changeEmail('new@email.addr');

    $virtualmin->deleteDomain($vhost);