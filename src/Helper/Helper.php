<?php

namespace Helper;

use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Util\StringUtils;


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

    public static function getEncodersParam()
    {
        self::$_container = self::getContainer();
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

    public static function getRandomValue($size)
    {
        $generator = new SecureRandom();
        $genValue = bin2hex($generator->nextBytes($size));

        return $genValue;
    }

    public static function getRegPassword($userPassword, $salt)
    {
        $parsedYml = Helper::getEncodersParam();
        $encoder = new MessageDigestPasswordEncoder($parsedYml['algorithm'], $parsedYml['baseAs64'], $parsedYml['iterations']);
        $regPassword = $encoder->encodePassword($userPassword, $salt);

        return $regPassword;
    }

    /*public static function getRecoveryPassword($userPassword, $salt)
    {
        $parsedYml = Helper::readEncodersParam();
        $encoder = new MessageDigestPasswordEncoder($parsedYml['algorithm'], $parsedYml['baseAs64'], $parsedYml['iterations']);
        //$recoveryPassword = $encoder->encodePassword($userPassword, $salt);
        $encoder = new

        return $recoveryPassword;
    }*/

    public static function sendRecoveryPasswordMail($container, $userEmail, $userId, $unencodePassword, $encodePassword)
    {
        $mailSender = $container->getParameter('mailSender');
        $mailTitle = $container->getParameter('mailTitle');
        $uniqCode = $container->getParameter('uniqCode');

        $mailer = $container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject('Восстановление пароля в системе ...')
            ->setFrom($mailSender, $mailTitle)
            ->setTo($userEmail)
            ->setBody(
                '<html>' .
                    '<head></head>' .
                    '<body>' .
                        '<p>Ваш новый пароль для входа ' . $unencodePassword . '</p>' .
                        '<p>Для подтверждения смены пароля нажмите <a href="http://localhost/study/web/app_dev.php/client/confirm?uniq_code=' .$uniqCode. '&hash_code=' . $encodePassword . '&id=' .$userId. '">сюда</a></p>' .
                    '</body>' .
                '</html>',
                'text/html'
            );
        //->setBody($this->renderView('HelloBundle:Hello:email', array('name' => $name)));
        $mailer->send($message);

        return true;
    }

    public static function getUserByEmail($userEmail)
    {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneByEmail($userEmail);

        if(!$user)
        {
            return 0;
        }

        return $user;
    }

    public static function isCorrectConfirmUrl($container, $uniqCode, $hashCode, $userId)
    {
        if(isset($uniqCode) && isset($hashCode) && isset($userId) && (iconv_strlen($uniqCode) == 17) && !empty($hashCode) && !empty($userId))
        {
            $realUniqCode = $container->getParameter('uniqCode');

            if(StringUtils::equals($realUniqCode, $uniqCode))
            {
                return true;
            }
        }

        return false;
    }

}
