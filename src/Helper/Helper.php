<?php

namespace Helper;

use Acme\SecureBundle\Entity\OrderFile;
use Acme\SecureBundle\Entity\UserBid;
use Acme\SecureBundle\Entity\UserOrder;
use Proxies\__CG__\Acme\SecureBundle\Entity\Author\AuthorFile;
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
    private static $_tableStatusOrder = 'AcmeSecureBundle:StatusOrder';
    private static $_tableOrderFile = 'AcmeSecureBundle:OrderFile';
    private static $_tableUserBid = 'AcmeSecureBundle:UserBid';
    private static $kernel;

    public function __construct() {
    }

    public static function readYamlFile() {
        $rootDir = self::$_container->get('kernel')->getRootDir();
        $yml = sprintf("%s/config/" . self::$_ymFile . ".yml", $rootDir);
        $parsed = Yaml::parse(file_get_contents($yml));
        return $parsed;
    }

    public static function getEncodersParam() {
        self::$_container = self::getContainer();
        self::$_ymFile = 'security';
        $parsedYml = self::readYamlFile();
        $algorithm = $parsedYml['security']['encoders']['Acme\AuthBundle\Entity\User']['algorithm'];
        $baseAs64 = $parsedYml['security']['encoders']['Acme\AuthBundle\Entity\User']['encode-as-base64'];
        $iterations = $parsedYml['security']['encoders']['Acme\AuthBundle\Entity\User']['iterations'];
        return array('algorithm' => $algorithm, 'baseAs64' => $baseAs64, 'iterations' => $iterations);
    }

    public static function getUploadMaxFile() {
        self::$_container = self::getContainer();
        self::$_ymFile = 'services';
        //$rootDir = self::$_container->get('kernel')->getRootDir();
        //$yml = sprintf("%s/vendor/punkave/symfony2-file-uploader-bundle/PunkAve/FileUploaderBundle/Resources/config/" . self::$_ymFile . ".yml", $rootDir);
        $yml = "D:/OpenServer/domains/localhost/study/vendor/punkave/symfony2-file-uploader-bundle/PunkAve/FileUploaderBundle/Resources/config/" . self::$_ymFile . ".yml";
        $parsed = Yaml::parse(file_get_contents($yml));
        return $parsed;
    }

    public static function getDateFromTimestamp($timestamp, $format) {
        return date($format, $timestamp);
    }

    public static function getUserByEmailAndIsConfirm($userEmail) {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneByEmail($userEmail);

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


    public static function getUserById($userId) {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneById($userId);
        if (!$user) {
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
        $userInfo->setCountry($country);
        $userInfo->setSkype($userSkype);
        $userInfo->setIcq($userIcq);
        $userInfo->setUsername($userName);
        $userInfo->setLastname($userLastname);
        $userInfo->setSurname($userSurname);
        $userInfo->setMobilePhone($userMobilePhone);
        $userInfo->setStaticPhone($userStaticPhone);
        $em->merge($userInfo);
        $em->flush();
    }


    public static function createNewOrder($postData, $user, $folderFiles, $arrayFiles)
    {
        $theme = $postData['fieldTheme'];
        $task = $postData['fieldTask'];
        $dateExpire = $postData['fieldDateExpire'];
        $originality = $postData['fieldOriginality'];
        $countSheet = $postData['fieldCountSheet'];
        $subjectId = $postData['selectorSubject'];
        $typeTypeOrderId = $postData['selectorTypeOrder'];
        $theme = strip_tags($theme);
        $theme = trim($theme);
        $theme = preg_replace('|\s+|', ' ', $theme);
        $taskWithoutTags = strip_tags($task);
        if ($taskWithoutTags == "Placeholder") {
            $task = "Не указана";
        }

        $em = self::getContainer()->get('doctrine')->getManager();
        $subject = $em->getRepository(self::$_tableSubject)
            ->findOneById($subjectId);
        $typeOrder = $em->getRepository(self::$_tableTypeOrder)
            ->findOneById($typeTypeOrderId);
        /*$user = $em->getRepository(self::$_tableUser)
            ->findOneById($userId);*/
        $statusOrder = $em->getRepository(self::$_tableStatusOrder)
            ->findOneById(1);

        $order = new UserOrder(self::getContainer());
        $order->setTheme($theme);
        $order->setTask($task);
        $order->setDateExpire($dateExpire);
        $order->setOriginality($originality);
        $order->setCountSheet($countSheet);
        $order->setSubjectOrder($subject);
        $order->setTypeOrder($typeOrder);
        $order->setStatusOrder($statusOrder);
        $order->setUser($user);
        $filesFolderOld = Helper::getFullPathFolderFiles($folderFiles);
        $isExistFilesFolder = self::isExistFilesFolder($filesFolderOld);
        $filesFolder = str_replace("non", "act", $folderFiles);
        if ($isExistFilesFolder) {
            $filesFolderNew = Helper::getFullPathFolderFiles($filesFolder);
            rename($filesFolderOld, $filesFolderNew);
        }
        $order->setFilesFolder($filesFolder);
        $em->persist($order);
        //$em->flush();
        if ($isExistFilesFolder) {
            foreach($arrayFiles as $file) {
                $fileSize = $file['size'];
                $fileName = $file['name'];
                $fileDateUpload = $file['dateUpload'];
                $format = 'Y-m-d H:i:s';
                $fileDateUpload = self::getFormatDateForInsert($fileDateUpload, $format);
                self::insertInfoAboutFileInDb($fileSize, $fileName, $fileDateUpload, $order, $em);
            }
        }
        $em->flush();
    }


    public static function isExistFilesFolder($filesFolder) {
        if (is_dir($filesFolder)) {
            return true;
        }
        else {
            return false;
        }
    }

    public static function insertInfoAboutOrderFileInDb($fileSize, $fileName, $fileDateUpload, $order, $em) {
        $orderFile = new OrderFile();
        $orderFile->setDateUpload($fileDateUpload);
        $orderFile->setName($fileName);
        $orderFile->setSize($fileSize);
        $orderFile->setUserOrder($order);
        $em->persist($orderFile);
    }


    public static function convertFromUtfToCp($task) {
        return iconv("UTF-8", "CP1251", $task);
    }


    public static function getFilesFromFolder($filesFolder) {
        if (self::isExistFilesFolder($filesFolder)) {
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
        else {
            //die($folderFiles);
            //$f = self::getFullPathFolderFiles($folderFiles);
           // mkdir($f, 0777);
        }
    }


    public static function getSizeFile($bytes){
        $label = array('B', 'KB', 'MB');
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
        elseif ($type == "author") {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/author/" . $folderFiles . "/originals";
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


    public static function getCountOrdersForClientGrid($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)
            ->findBy(array('user' => $user, 'is_show_client' => 1));
        return count($orders);
    }

    public static function getCountOrdersForAuthorGrid() {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)
            ->findBy(array('is_show_author' => 1));
        return count($orders);
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
                            array('user' => $user, 'is_show_client' => 1, $sField => $sData),
                            array($sField => $sortingOrder),
                            $rowsPerPage, $firstRowIndex
                        );
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show_client = 1')
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
                        ->andWhere('o.is_show_client = 1')
                        ->andWhere('o.' . $sField . ' != :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show_client = 1')
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
                        ->andWhere('o.is_show_client = 1')
                        ->andWhere('o.' . $sField . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show_client = 1')
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
                        ->andWhere('o.is_show_client = 1')
                        ->andWhere('o.' . $sField . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show_client = 1')
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
                        array('user' => $user, 'is_show_client' => 1),
                        array($sortingField => $sortingOrder),
                        $rowsPerPage,
                        $firstRowIndex
                    );
            }
            else {
                $orders = $em->getRepository(self::$_tableUserOrder)
                    ->findBy(
                        array('user' => $user, 'is_show_client' => 1),
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


    public static function deleteOrderByClient($orderId, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('user' => $user, 'is_show_client' => 1, 'id' => $orderId));
        if ($order) {
            $order->setIsShowClient(0);
            $order->setIsShowAuthor(0);
            $em->flush();
            return true;
        }
        else {
            return false;
        }
    }


    public static function hideOrderFromAuthor($orderId, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('user' => $user, 'is_show_client' => 1, 'is_show_author' => 1, 'id' => $orderId));
        if ($order) {
            $status = $em->getRepository(self::$_tableStatusOrder)
                ->findOneByCode('h');
            $order->setIsShowAuthor(0);
            $order->setStatusOrder($status);
            $em->flush();
            return true;
        }
        else {
            return false;
        }
    }


    public static function showOrderForAuthor($orderId, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('user' => $user, 'is_show_client' => 1, 'is_show_author' => 0, 'id' => $orderId));
        if ($order) {
            $status = $em->getRepository(self::$_tableStatusOrder)
                ->findOneByCode('s');
            $order->setIsShowAuthor(1);
            $order->setStatusOrder($status);
            $em->flush();
            return true;
        }
        else {
            return false;
        }
    }


    public static function getOrderByNumForClient($num, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('user' => $user, 'is_show_client' => 1, 'num' => $num));
        if ($order) {
            return $order;
        }
        else {
            return false;
        }
    }


    public static function getOrderByNumForAuthor($num) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('is_show_author' => 1, 'num' => $num));
        if ($order) {
            return $order;
        }
        else {
            return false;
        }
    }


    public static function uploadAuthorFileInfo($user) {
        $folderFiles = Helper::getFullPathFolderFiles($user->getId(), "author");
        $isExistFilesFolder = self::isExistFilesFolder($folderFiles);
        if ($isExistFilesFolder) {
            $em = self::getContainer()->get('doctrine')->getManager();
            $arrayFiles = Helper::getFilesFromFolder($folderFiles);
            foreach($arrayFiles as $file) {
                $authorFile = new AuthorFile();
                $authorFile->setSize($file['size']);
                $authorFile->setName($file['name']);
                $fileDateUpload = self::getFormatDateForInsert($file['dateUpload'], 'Y-m-d H:i:s');
                $authorFile->setDateUpload($fileDateUpload);
                $authorFile->setUser($user);
                $em->persist($authorFile);
            }
            $user->setIsAuthorfile(1);
            $em->flush();
            return true;
        }
        return false;
    }


    public static function getAuthorOrdersForGrid($sOper = null, $sField = null, $sData = null, $firstRowIndex, $rowsPerPage, $sortingField, $sortingOrder) {
        $em = self::getContainer()->get('doctrine')->getManager();
        if ($sField != null && $sOper != null && $sData != null) {
            if ($sField == "subject_order") {
                $field = 'child_name';
            }
            elseif ($sField == "type_order") {
                $field = 'name';
            }

            if ($sOper == 'eq') {
                if ($sField != "subject_order" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)
                        ->findBy(
                            array('is_show_author' => 1, $sField => $sData),
                            array($sField => $sortingOrder),
                            $rowsPerPage, $firstRowIndex
                        );
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.is_show_author = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' = :data')
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
            }
            elseif ($sOper == 'ne') {
                if ($sField != "subject_order" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.is_show_author = 1')
                        ->andWhere('o.' . $sField . ' != :data')
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.is_show_author = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' != :data')
                        ->setParameter('data', $sData)
                        ->getQuery()
                        ->getResult();
                }
            }
            elseif ($sOper == 'bw') {
                if ($sField != "subject_order" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.is_show_author = 1')
                        ->andWhere('o.' . $sField . ' LIKE :data')
                        ->setParameter('data', $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.is_show_author = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' LIKE :data')
                        ->setParameter('data', '%' . $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
            }
            elseif ($sOper == 'cn') {
                if ($sField != "subject_order" && $sField != "type_order") {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.user = :user')
                        ->andWhere('o.is_show_client = 1')
                        ->andWhere('o.' . $sField . ' LIKE :data')
                        ->setParameter('user', $user)
                        ->setParameter('data', $sData . '%')
                        ->getQuery()
                        ->getResult();
                }
                else {
                    $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('o')
                        ->andWhere('o.is_show_author = 1')
                        ->innerJoin('o.' . $sField , 'a')
                        ->andWhere('a.' . $field . ' LIKE :data')
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
                        array('is_show_author' => 1),
                        array($sortingField => $sortingOrder),
                        $rowsPerPage,
                        $firstRowIndex
                    );
            }
            else {
                $orders = $em->getRepository(self::$_tableUserOrder)
                    ->findBy(
                        array('is_show_author' => 1),
                        array('num' => 'ASC'),
                        $rowsPerPage,
                        $firstRowIndex
                    );
            }
        }
        return $orders;
    }


    public static function getFilesForOrder($order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableOrderFile)
            ->findByOrder($order);
        return $orders;
    }


    public static function getAllAuthorBid($user, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bids = $em->getRepository(self::$_tableUserBid)
            ->findBy(array('user' => $user, 'order' => $order), array('id' => 'DESC'));
        return $bids;
    }


    public static function updateAuthorBid($postData, $user, $order) {
        $sum = $postData['fieldSum'];
        $comment = $postData['fieldComment'];
        if (isset($postData['fieldDay'])) {
            $day = $postData['fieldDay'];
        }

        $userBid = new UserBid();
        $userBid->setUser($user);
        $userBid->setUserOrder($order);
        $userBid->setSum($sum);
        $userBid->setComment($comment);
        if (isset($day)) {
            $userBid->setDay($day);
        }
        else {
            $userBid->setIsClientDate(1);
        }
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($userBid);
        $em->flush();
    }


    public static function convertFormErrorObjToString($error) {
        $errorMessageTemplate = $error->getMessageTemplate();
        foreach ($error->getMessageParameters() as $key => $value) {
            $errorMessageTemplate = str_replace($key, $value, $errorMessageTemplate);
        }
        return $errorMessageTemplate;
    }
}
