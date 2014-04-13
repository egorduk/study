<?php

namespace Helper;

use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Util\StringUtils;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Acme\AuthBundle\Entity\Openid;

class Helper
{
    private static $_container;
    private static $_ymFile;
    private static $_tableUser = 'AcmeAuthBundle:User';
    private static $_tableProvider = 'AcmeAuthBundle:Provider';
    private static $_tableCountry = 'AcmeAuthBundle:Country';
    private static $_tableUserInfo = 'AcmeAuthBundle:UserInfo';
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

    public static function getUserByEmailAndIsConfirm($userEmail)
    {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneByEmail($userEmail);

       /*$em = self::getContainer()->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id, u.role_id, u.login, u.salt, u.password
            FROM AcmeAuthBundle:User u
            WHERE u.email = :email AND u.is_confirm = 1')
            ->setParameter('email', $userEmail);
            //->setParameter('is_confirm', 1);
        $user = $query->getResult();*/

        if(!$user)
        {
            return false;
        }

        return $user;
    }

    public static function isExistsUserByEmail($userEmail)
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.email = :email')
            ->setParameter('email', $userEmail);
        $user = $query->getResult();

        if(!$user)
        {
            return false;
        }

        return true;
    }

    public static function isExistsUserById($userId)
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.id = :id')
            ->setParameter('id', $userId);
        $user = $query->getResult();

        if(!$user)
        {
            return false;
        }

        return true;
    }


    public static function isExistsUserByLogin($userLogin)
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.login = :login')
            ->setParameter('login', $userLogin);
        $user = $query->getResult();

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

    public static function sendRecoveryPasswordMail($container, $userEmail, $userId, $unencodePassword, $hashCode)
    {
        $mailSender = $container->getParameter('mailSender');
        $mailTitle = $container->getParameter('mailTitle');
        $confirmPath = $container->getParameter('confirmPath');
        //$encodePassword = htmlspecialchars(rawurlencode($encodePassword));

        $mailer = $container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject('Восстановление пароля в системе')
            ->setFrom($mailSender, $mailTitle)
            ->setTo($userEmail)
            ->setBody(
                '<html>' .
                    '<head></head>' .
                    '<body>' .
                        '<p>Ваш новый пароль для входа ' . $unencodePassword . '</p>' .
                        '<p>Для подтверждения смены пароля нажмите <a href="' . $confirmPath . '?type=rec&hash_code=' . $hashCode . '&id=' .$userId. '">сюда</a></p>' .
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

        if (!$user)
        {
            //throw new NotFoundHttpException('Error!');
            return false;
        }

        return $user;
    }


    public static function isCorrectConfirmUrl($hashCode, $userId, $type)
    {
        if (isset($hashCode) && isset($userId) && ($type == "reg" || $type == "rec") && (iconv_strlen($hashCode) == 30) && !empty($hashCode) && !empty($userId) && is_numeric($userId) && ($userId > 0))
        {
            return true;
        }

        return false;
    }


    public static function getUserRoleByEmail($userEmail)
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.user_role_id FROM AcmeAuthBundle:User u WHERE u.email = :email')
            ->setParameter('email', $userEmail);
        $user = $query->getResult();

        if(!$user)
        {
            return false;
        }

        return true;
    }


    public static function getUserById($userId)
    {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneById($userId);

        if (!$user)
        {
            return false;
        }

        return $user;
    }


    public static function updateUserAfterConfirmByMail($userId, $hashCode, $type)
    {
        $container = self::getContainer();

        $em = $container->get('doctrine')->getManager();
        $user = $em->getRepository(self::$_tableUser)
            ->findOneBy(array('id' => $userId, 'hash_code' => $hashCode));

        if (!$user)
        {
            return false;
        }
        else
        {
            $user->setHash('');

            if ($type == "reg")
            {
                //$user->setPassword($hashCode);
                $user->setIsConfirm(1);
                $user->setDateConfirmReg(new \DateTime());
            }
            elseif ($type == "rec")
            {
                $encodePassword = $user->getRecoveryPassword();
                $user->setPassword($encodePassword);
                $user->setDateConfirmRecovery(new \DateTime());
                $user->setRecoveryPassword('');
            }

            $em->flush();

            return true;
        }
    }


    public static function isExistsUserByEmailAndIsConfirm($userEmail)
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.email = :email AND u.is_confirm = :is_confirm')
            ->setParameter('email', $userEmail)
            ->setParameter('is_confirm', 1);
        $user = $query->getResult();

        if(!$user)
        {
            return false;
        }

        return true;
    }


    public static function isExistsUserByHashAndByIdAndIsConfirm($userId, $hashCode, $isConfirm)
    {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.id = :id AND u.is_confirm = :is_confirm AND u.hash_code = :hash_code')
            ->setParameter('id', $userId)
            ->setParameter('is_confirm', $isConfirm)
            ->setParameter('hash_code', $hashCode);
        $user = $query->getResult();

        if(!$user)
        {
            return false;
        }

        return true;
    }


    public static function sendConfirmationReg($container, $userEmail, $userId, $hash)
    {
        $mailSender = $container->getParameter('mailSender');
        $mailTitle = $container->getParameter('mailTitle');
        $confirmPath = $container->getParameter('confirmPath');

        $mailer = $container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject('Подтверждение регистрации в системе')
            ->setFrom($mailSender, $mailTitle)
            ->setTo($userEmail)
            //->setTo("gzhelka777@mail.ru")
            ->setBody(
                '<html>' .
                '<head></head>' .
                '<body>' .
                '<p>Для подтверждения регистрации на сайте нажмите <a href="' . $confirmPath . '?hash_code=' . $hash . '&type=reg&id=' .$userId. '">сюда</a></p>' .
                '</body>' .
                '</html>',
                'text/html'
            );

        $mailer->send($message);
    }


    public static function addNewOpenIdData($socialData, $providerName, $countryCode)
    {
        $provider = self::getContainer()->get('doctrine')->getRepository(self::$_tableProvider)
            ->findOneByName($providerName);

        $country = self::getContainer()->get('doctrine')->getRepository(self::$_tableCountry)
            ->findOneByCode($countryCode);

        $openId = new Openid();

        $openId->setUid($socialData['uid']);
        $openId->setProfileUrl($socialData['profile']);
        $openId->setEmail($socialData['email']);
        $openId->setNickname($socialData['nickname']);
        $openId->setFirstName($socialData['first_name']);
        $openId->setIdentity($socialData['identity']);
        $openId->setPhotoBig($socialData['photo_big']);
        $openId->setPhoto($socialData['photo']);
        $openId->setProvider($provider);
        $openId->setCountry($country);

        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($openId);
        $em->flush();

        return $openId->getId();
    }


    public static function getUserInfoById($userId)
    {
        $userInfo = self::getContainer()->get('doctrine')->getRepository(self::$_tableUserInfo)
            ->findOneById($userId);

        return $userInfo;
    }


    public static function updateUserInfo($postData, $userInfo)
    {
        $userName = $postData['fieldUsername'];
        $userSurname = $postData['fieldSurname'];
        $userLastname = $postData['fieldLastname'];
        $userSkype = $postData['fieldSkype'];
        $userIcq = $postData['fieldIcq'];
        $userMobilePhone = $postData['fieldMobilePhone'];
        $userStaticPhone = $postData['fieldStaticPhone'];
        $userSelectedCountryCode = $postData['selectorCountry'];

        $em = self::getContainer()->get('doctrine')->getManager();
        $country = $em->getRepository(self::$_tableCountry)
            ->findOneByCode($userSelectedCountryCode);

        $userInfo->setSkype($userSkype);
        $userInfo->setIcq($userIcq);
        $userInfo->setUsername($userName);
        $userInfo->setLastname($userLastname);
        $userInfo->setSurname($userSurname);
        $userInfo->setMobilePhone($userMobilePhone);
        $userInfo->setStaticPhone($userStaticPhone);
        $userInfo->setCountry($country);

        $em->merge($userInfo);
        $em->flush();
    }


}
