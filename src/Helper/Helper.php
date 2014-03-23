<?php

namespace Helper;

use Symfony\Component\Yaml\Yaml;


class Helper
{
    private static $_container;
    private static $_ymFile;

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

    public static function readEncodersParam($container)
    {
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

    public static function getOpenIdData($data)
    {

    }

}
