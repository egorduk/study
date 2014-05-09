<?php

namespace Helper;

use Acme\SecureBundle\Entity\OrderFile;
use Acme\SecureBundle\Entity\UserOrder;
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
    private static $_tableSubject = 'AcmeSecureBundle:Subject';
    private static $_tableTypeOrder = 'AcmeSecureBundle:TypeOrder';
    private static $_tableUserOrder = 'AcmeSecureBundle:UserOrder';
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
            //->setTo($userEmail)
            //->setTo("gzhelka777@mail.ru")
            ->setTo("egordukk@tut.by")
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


    public static function addNewOpenIdData($socialData, $country, $user)
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $provider = $em->getRepository(self::$_tableProvider)
            ->findOneByName($socialData['network']);

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
        $openId->setUser($user);
        $em->persist($openId);
        $em->flush();
        return $user->getId();
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


    public static function createNewOrder($postData, $userId, $folderFiles, $arrayFiles)
    {
        $theme = $postData['fieldTheme'];
        $task = $postData['fieldTask'];
        $dateExpire = $postData['fieldDateExpire'];
        $originality = $postData['fieldOriginality'];
        $countSheet = $postData['fieldCountSheet'];
        $subjectId = $postData['selectorSubject'];
        $typeTypeOrderId = $postData['selectorTypeOrder'];
        $flagSuccess = false;

        $theme = strip_tags($theme);
        $theme = trim($theme);
        $theme = preg_replace('|\s+|', ' ', $theme);

        $em = self::getContainer()->get('doctrine')->getManager();

        $subject = $em->getRepository(self::$_tableSubject)
            ->findOneById($subjectId);

        $typeOrder = $em->getRepository(self::$_tableTypeOrder)
            ->findOneById($typeTypeOrderId);

        $user = $em->getRepository(self::$_tableUser)
            ->findOneById($userId);

        $order = new UserOrder(self::getContainer());
        $order->setTheme($theme);
        $order->setTask($task);
        $order->setDateExpire($dateExpire);
        $order->setOriginality($originality);
        $order->setCountSheet($countSheet);
        $order->setSubject($subject);
        $order->setTypeOrder($typeOrder);
        $order->setUser($user);
        $filesFolderOld = Helper::getFullPathFolderFiles($folderFiles);
        $filesFolder = str_replace("non", "act", $folderFiles);
        $filesFolderNew = Helper::getFullPathFolderFiles($filesFolder);
        rename($filesFolderOld, $filesFolderNew);
        $order->setFilesFolder($filesFolder);

        $em->persist($order);
        $em->flush();

        foreach($arrayFiles as $file) {
            $fileSize = $file['size'];
            $fileName = $file['name'];
            $fileDateUpload = $file['dateUpload'];
            $format = 'Y-m-d H:i:s';
            $fileDateUpload = self::getFormatDateForInsert($fileDateUpload, $format);

            self::insertInfoAboutFileInDb($fileSize, $fileName, $fileDateUpload, $order, $em);
        }

        $flagSuccess = true;

        return $flagSuccess;
    }


    public static function insertInfoAboutFileInDb($fileSize, $fileName, $fileDateUpload, $order, $em) {
        $orderFile = new OrderFile();
        $orderFile->setDateUpload($fileDateUpload);
        $orderFile->setName($fileName);
        $orderFile->setSize($fileSize);
        $orderFile->setUserOrder($order);

        $em->persist($orderFile);
        $em->flush();
    }


    public static function convertFromUtfToCp($task) {
        return iconv("UTF-8", "CP1251", $task);
    }


    public static function getFilesFromFolder($filesFolder) {
        $fileHandler = opendir($filesFolder);
        $arrayInfoFiles = [];
        $i = 0;

        while (false !== ($filename = readdir($fileHandler))) {
            if ($filename != "." && $filename != "..") {
                $arrayInfoFiles[$i]['size'] = Helper::getSizeFile(filesize($filesFolder . "/" . $filename));
                //$arrayInfoFiles[$i]['extension'] = Helper::getExtensionFile($filesFolder . "/" . $filename);
                $arrayInfoFiles[$i]['dateUpload'] = Helper::getDateUploadFile($filesFolder . "/" . $filename);
                $arrayInfoFiles[$i]['name'] = $filename;
                $i++;
            }
        }
        closedir($fileHandler);

        return $arrayInfoFiles;
    }


    public static function getSizeFile($bytes){
        $label = array('Б', 'КБ', 'МБ');
        for($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);
        return(round($bytes, 1) . " " . $label[$i]);
    }


    /*public static function getTypeFile($filename){
        $mime_types = array(
            'txt' => 'text/plain',
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            '7z' => 'application/x-7z-compressed',
            'zip' => 'application/zip',
            'rar' => 'application/x-rar-compressed',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'ppt' => 'application/vnd.ms-powerpoint',
        );

        $ext = strtolower(array_pop(explode('.', $filename)));
        if (array_key_exists($ext, $mime_types)) {
            return $mime_types[$ext];
        }
        else{

        }
    }*/


    public static function getExtensionFile($filename) {
        $pathInfo = pathinfo($filename);
        return $pathInfo['extension'];
    }


    public static function getDateUploadFile($filename) {
        if (file_exists($filename)) {
            return date("Y-m-d H:i:s", filemtime($filename));
        }
    }


    public static function getFullPathFolderFiles($folderFiles, $type = null) {
        if ($type == "originals") {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/attachments/" . $folderFiles . "/originals";
        }
        elseif ($type == null) {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/attachments/" . $folderFiles;
        }

    }


    public static function getFormatDateForInsert($sourceDate, $format) {
        $date = \DateTime::createFromFormat($format, $sourceDate);
        $date = $date->format('Y-m-d H:i:s');
        return new \Datetime($date);
    }


    public static function getCountOrdersForGrid($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findByUser(array('user' => $user, 'is_show' > 1));
        return count($order);
    }


    public static function getClientOrdersForGrid($sOper = null, $sField = null, $sData = null, $firstRowIndex, $rowsPerPage, $user, $sortingField, $sortingOrder) {
        $em = self::getContainer()->get('doctrine')->getManager();

        if ($sField != null && $sOper != null && $sData != null) {
            if ($sField == "subject") {
                $field = 'child_name';
            }
            elseif ($sField == "type_order") {
                $field = 'name';
            }

            if ($sOper == 'eq') {
                if ($sField != "subject" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)
                        ->findBy(
                            array('user' => $user, 'is_show' => 1, $sField => $sData),
                            array($sField => $sortingOrder),
                            $rowsPerPage, $firstRowIndex
                        );
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' = :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
            }
            elseif ($sOper == 'ne') {
                if ($sField != "subject" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->andWhere('o.' . $sField . ' != :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' != :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
            }
            elseif ($sOper == 'bw') {
                if ($sField != "subject" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->andWhere('o.' . $sField . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', '%' . $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
            }
            elseif ($sOper == 'cn') {
                if ($sField != "subject" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->andWhere('o.' . $sField . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', '%' . $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
            }
        }
        else {
            if (isset($sortingField) && $sortingField != "" && isset($sortingOrder) && $sortingOrder != "") {
                $orders = $em->getRepository(self::$_tableUserOrder)
                    ->findBy(
                        array('user' => $user, 'is_show' => 1),
                        array($sortingField => $sortingOrder),
                        $rowsPerPage,
                        $firstRowIndex
                    );
            }
            else {
                $orders = $em->getRepository(self::$_tableUserOrder)
                    ->findBy(
                        array('user' => $user, 'is_show' => 1),
                        array('num' => 'ASC'),
                        $rowsPerPage,
                        $firstRowIndex
                    );
            }
        }

        return $orders;
    }


    public static function getCutSentence($sentence, $size) {
        $sentence = mb_substr($sentence, 0, $size, "UTF-8");
        $pos = strrpos($sentence, ' ');
        if ($pos === false) {
            $sentence . '...';
        }
        else {
            $sentence = substr($sentence, 0, $pos) . '...';
        }

        return $sentence;
    }


    public static function getMonthNameFromDate($date) {
        $dateArray = explode('.', $date);
        if ($dateArray[0][0] == '0') {
            $dateArray[0][0] = '';
        }
        if($dateArray[1] == "1"){$month="января";}
        elseif($dateArray[1] == "2"){$month="февраля";}
        elseif($dateArray[1] == "3"){$month="марта";}
        elseif($dateArray[1] == "4"){$month="апреля";}
        elseif($dateArray[1] == "5"){$month="мая";}
        elseif($dateArray[1] == "6"){$month="июня";}
        elseif($dateArray[1] == "7"){$month="июля";}
        elseif($dateArray[1] == "8"){$month="августа";}
        elseif($dateArray[1] == "9"){$month="сентября";}
        elseif($dateArray[1] == "10"){$month="октября";}
        elseif($dateArray[1] == "11"){$month="ноября";}
        elseif($dateArray[1] == "12"){$month="декабря";}
        $fullDate = $dateArray[0] . ' ' . $month . ' ' . $dateArray[2];
        return $fullDate;
    }

}
