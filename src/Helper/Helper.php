<?php

namespace Helper;

use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;


class Helper
{
    private static $_container;
    private static $_ymFile;
    private static $_tableUser = 'AcmeAuthBundle:User';
    private static $kernel;

    public function __construct()
    {
    }

    public static function readYamlFile()
    {
        $rootDir = self::$_container->get('kernel')->getRootDir();
        $yml = sprintf("%s/config/" . self::$_ymFile . ".yml", $rootDir);
        $parsed = Yaml::parse(file_get_contents($yml));

        return $parsed;
    }

    public static function readEncodersParam()
    {
        $container = self::getContainer();
        self::$_container = $container;
        self::$_ymFile = 'security';

        $parsedYml = self::readYamlFile();
        $algorithm = $parsedYml['security']['encoders']['Acme\AuthBundle\Entity\User']['algorithm'];
        $baseAs64 = $parsedYml['security']['encoders']['Acme\AuthBundle\Entity\User']['encode-as-base64'];
        $iterations = $parsedYml['security']['encoders']['Acme\AuthBundle\Entity\User']['iterations'];

        return array('algorithm' => $algorithm, 'baseAs64' => $baseAs64, 'iterations' => $iterations);
    }

    public static function getDateFromTimestamp($timestamp, $format)
    {
        return date($format, $timestamp);
    }

    public static function getOpenIdData($openIdData)
    {

    }

    public static function isExistsUserLogin($userLogin)
    {
        $container = self::getContainer();
        $user = $container->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneByLogin($userLogin);

        if(!$user)
        {
            return false;
        }

        return true;
    }

    public static function isExistsUserEmail($userEmail)
    {
        $container = self::getContainer();
        $user = $container->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneByEmail($userEmail);

        if(!$user)
        {
            return false;
        }

        return true;
    }

    public static function getContainer()
    {
        if(self::$kernel instanceof \AppKernel)
        {
            if(!self::$kernel->getContainer() instanceof Container)
            {
                self::$kernel->boot();
            }

            return self::$kernel->getContainer();
        }

        /*$environment = 'prod';
        if (!array_key_exists('REMOTE_ADDR', $_SERVER) || in_array(@$_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1', 'localhost'))) {
            $environment = 'dev';
        }*/

        $environment = 'dev';
        self::$kernel = new \AppKernel($environment, false);
        self::$kernel->boot();
        return self::$kernel->getContainer();
    }

    public static function getSalt()
    {
        $generator = new SecureRandom();
        $salt = bin2hex($generator->nextBytes(32));

        return $salt;
    }

    public static function getRegPassword($userPassword, $salt)
    {
        $parsedYml = Helper::readEncodersParam();
        $encoder = new MessageDigestPasswordEncoder($parsedYml['algorithm'], $parsedYml['baseAs64'], $parsedYml['iterations']);
        $regPassword = $encoder->encodePassword($userPassword, $salt);

        return $regPassword;
    }


}
