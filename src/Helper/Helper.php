<?php

namespace Helper;

use Acme\SecureBundle\Entity\AuctionBid;
use Acme\SecureBundle\Entity\CancelRequest;
use Acme\SecureBundle\Entity\FavoriteOrder;
use Acme\SecureBundle\Entity\MoneyOutput;
use Acme\SecureBundle\Entity\OrderFile;
use Acme\SecureBundle\Entity\SelectBid;
use Acme\SecureBundle\Entity\StatusOrder;
use Acme\SecureBundle\Entity\UserBid;
use Acme\SecureBundle\Entity\UserOrder;
use Acme\SecureBundle\Entity\UserPs;
use Acme\SecureBundle\Entity\WebchatMessage;
use Proxies\__CG__\Acme\AuthBundle\Entity\User;
use Proxies\__CG__\Acme\SecureBundle\Entity\Author\AuthorFile;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Yaml\Yaml;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Util\StringUtils;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Acme\AuthBundle\Entity\Openid;
use Symfony\Component\HttpFoundation\Response;

class Helper
{
    private static $_container;
    private static $_ymFile;
    private static $_tableUser = 'AcmeAuthBundle:User';
    private static $_tableProvider = 'AcmeAuthBundle:Provider';
    private static $_tableUserRole = 'AcmeAuthBundle:UserRole';
    private static $_tableCountry = 'AcmeAuthBundle:Country';
    private static $_tableUserInfo = 'AcmeAuthBundle:UserInfo';
    private static $_tableSubject = 'AcmeSecureBundle:SubjectOrder';
    private static $_tableTypeOrder = 'AcmeSecureBundle:TypeOrder';
    private static $_tableUserOrder = 'AcmeSecureBundle:UserOrder';
    private static $_tableStatusOrder = 'AcmeSecureBundle:StatusOrder';
    private static $_tableOrderFile = 'AcmeSecureBundle:OrderFile';
    private static $_tableUserBid = 'AcmeSecureBundle:UserBid';
    private static $_tableFavoriteOrder = 'AcmeSecureBundle:FavoriteOrder';
    private static $_tableWebchatMessage = 'AcmeSecureBundle:WebchatMessage';
    private static $_tableCancelRequest = 'AcmeSecureBundle:CancelRequest';
    private static $_tableUserPs = 'AcmeSecureBundle:UserPs';
    private static $_tableTypePs = 'AcmeSecureBundle:TypePs';
    private static $_tableMailOption = 'AcmeSecureBundle:MailOption';
    //private static $kernel;

    private static $_avatarFolder = '/study/web/uploads/avatars/';

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

    /**
     * Get user by email and if he confirmed activation
     * @param $userEmail
     * @return bool
     */
    public static function getUserByEmailAndIsConfirm($userEmail) {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneBy(array('email' => $userEmail, 'is_confirm' => 1, 'is_ban' => 0));
        if (!$user) {
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
        if (!$user) {
            return false;
        }
        return true;
    }

    public static function isExistsUserById($userId) {
        $container = self::getContainer();
        $em = $container->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.id = :id')
            ->setParameter('id', $userId);
        $user = $query->getResult();
        if (!$user) {
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
        if (!$user) {
            return false;
        }
        return true;
    }


    /*public static function getContainer() {
        if(self::$kernel instanceof \AppKernel) {
            if(!self::$kernel->getContainer() instanceof Container) {
                self::$kernel->boot();
            }
            return self::$kernel->getContainer();
        }
        $environment = 'dev';
        self::$kernel = new \AppKernel($environment, false);
        self::$kernel->boot();
        return self::$kernel->getContainer();
    }*/


    public static function getContainer() {
        global $kernel;
        $container = $kernel->getContainer();
        return $container;
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


    public static function getUserPassword($userPassword, $salt)
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

    public static function sendRecoveryPasswordMail($container, $user)
    {
        $mailSender = $container->getParameter('mailSender');
        $mailTitle = $container->getParameter('mailTitle');
        $confirmPath = $container->getParameter('confirmPath');
        $unEncodePassword = $user->getUnEncodePass();
        $hashCode = $user->getHash();
        $userId = $user->getId();
        $userEmail = $user->getEmail();
        //$encodePassword = htmlspecialchars(rawurlencode($encodePassword));
        $mailer = $container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject('Восстановление пароля в системе')
            ->setFrom($mailSender, $mailTitle)
            ->setTo($userEmail)
            //->setTo('a_1300@mail.ru')
            ->setBody(
                '<html>' .
                    '<head></head>' .
                    '<body>' .
                        '<p>Ваш новый пароль для входа ' . $unEncodePassword . '</p>' .
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
        if ($user) {
            return $user;
        }
        return false;
    }


    public static function isCorrectConfirmUrl($hashCode, $userId, $type)
    {
        if (isset($hashCode) && isset($userId) && ($type == "reg" || $type == "rec") && (iconv_strlen($hashCode) == 30) && !empty($hashCode) && !empty($userId) && is_numeric($userId) && ($userId > 0)) {
            return true;
        }
        return false;
    }


    public static function getUserRoleByEmail($userEmail)
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(self::$_tableUser)
            ->findOneByEmail($userEmail);
        if ($user) {
            return $user->getRole();
        }
        return false;
    }


    public static function getUserById($userId) {
        $user = self::getContainer()->get('doctrine')->getRepository(self::$_tableUser)
            ->findOneById($userId);
        if ($user) {
            return $user;
        }
        return false;
    }


    public static function updateUserAfterConfirmByMail($userId, $hashCode, $type)
    {
        $em =  self::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(self::$_tableUser)
            ->findOneBy(array('id' => $userId, 'hash_code' => $hashCode));
        if (!$user) {
            return false;
        }
        else {
            $user->setHash("");
            if ($type == "reg") {
                //$user->setPassword($hashCode);
                $user->setIsConfirm(1);
                $user->setDateConfirmReg(new \DateTime());
            }
            elseif ($type == "rec") {
                $encodePassword = $user->getRecoveryPassword();
                $user->setPassword($encodePassword);
                $user->setDateConfirmRecovery(new \DateTime());
                $user->setRecoveryPassword('');
            }
            $em->flush();
            return true;
        }
    }


    public static function isExistsUserByEmailAndIsConfirmAndIsUnban($userEmail)
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.email = :email AND u.is_confirm = :is_confirm AND u.is_ban = :is_ban')
            ->setParameter('email', $userEmail)
            ->setParameter('is_confirm', 1)
            ->setParameter('is_ban', 0);
        $user = $query->getResult();
        if ($user) {
            return true;
        }
        return false;
    }


    public static function isExistsUserByHashAndByIdAndIsConfirm($userId, $hashCode, $isConfirm)
    {
        $em = self::getContainer()->get('doctrine')->getManager();
        $query = $em->createQuery('SELECT u.id FROM AcmeAuthBundle:User u WHERE u.id = :id AND u.is_confirm = :is_confirm AND u.hash_code = :hash_code AND u.is_ban = :is_ban')
            ->setParameter('id', $userId)
            ->setParameter('is_confirm', $isConfirm)
            ->setParameter('hash_code', $hashCode)
            ->setParameter('is_ban', 0);
        $user = $query->getResult();
        if ($user) {
            return true;
        }
        return true;
    }


    public static function sendConfirmationReg($container, $user)
    {
        $mailSender = $container->getParameter('mailSender');
        $mailTitle = $container->getParameter('mailTitle');
        $confirmPath = $container->getParameter('confirmPath');
        $mailer = $container->get('mailer');
        $hash = $user->getHash();
        $userId = $user->getId();
        $userEmail = $user->getEmail();
        $message = \Swift_Message::newInstance()
            ->setSubject('Подтверждение регистрации в системе')
            ->setFrom($mailSender, $mailTitle)
            ->setTo($userEmail)
            //->setTo("a_1300@mail.ru")
            ->setBody(
                '<html>' .
                '<head></head>' .
                '<body>' .
                '<p>Для подтверждения регистрации на сайте нажмите <a href="' . $confirmPath . '?hash_code=' . $hash . '&type=reg&id=' . $userId . '">сюда</a></p>' .
                '</body>' .
                '</html>',
                'text/html'
            );
        $mailer->send($message);
    }

    /**Add new record with openid information about user
     * @param $socialData
     * @param $country
     * @param $user
     * @return mixed
     */
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
        $statusOrder = $em->getRepository(self::$_tableStatusOrder)
            ->findOneById(1);
        $order = new UserOrder(self::getContainer(), "new");
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
                self::insertInfoAboutOrderFileInDb($fileSize, $fileName, $fileDateUpload, $order, $em);
            }
        }
        $em->flush();
    }


    public static function isExistFilesFolder($filesFolder) {
        if (is_dir($filesFolder)) {
            return true;
        }
        return false;
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


    public static function getSizeFile($bytes) {
        $label = array('B', 'KB', 'MB', 'GB');
        for ($i = 0; $bytes >= 1024 && $i < (count($label) - 1); $bytes /= 1024, $i++);
        return(round($bytes, 1) . " " . $label[$i]);
    }


    public static function getMimeType($type) {
        $mimeTypes = array(
            'txt' => 'text/plain',
            'png' => 'image/png',
            'jpeg' => 'image/jpeg',
            'jpg' => 'image/jpeg',
            'gif' => 'image/gif',
            'bmp' => 'image/bmp',
            '7z' => 'application/octet-stream',
            'zip' => 'application/zip',
            'rar' => 'application/rar',
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            //'rtf' => 'application/rtf',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'ppt' => 'application/vnd.ms-powerpoint',
            'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            //'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'docx' => 'binary/octet-stream',
            'rtf' => 'text/rtf'
        );
        //$ext = strtolower(array_pop(explode('.', $filename)));
        /*if (array_search($type, $mimeTypes)) {
            return $mimeTypes[$type];
        }
        else{
            return "FALSE";
        }*/
        return array_search($type,$mimeTypes);
    }


    public static function getExtensionFile($filename) {
        $pathInfo = pathinfo($filename);
        return $pathInfo['extension'];
    }


    public static function getDateUploadFile($filename) {
        if (file_exists($filename)) {
            return date("Y-m-d H:i:s", filemtime($filename));
        }
        return new Exception();
    }


    public static function getFullPathFolderFiles($folderFiles, $type = null) {
        if ($type == "originals") {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/attachments/" . $folderFiles . "/originals";
        }
        elseif ($type == "author") {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/author/" . $folderFiles . "/originals";
        }
        /*elseif ($type == "order") {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/attachments/" . $folderFiles . "/originals";
        }*/
        elseif ($type == null) {
            return dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/attachments/" . $folderFiles;
        }
        else {
            return new Exception();
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


    public static function getCountNewOrdersForAuthorGrid() {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.date_expire > :now')
            ->innerJoin('uo.status_order', 'so')
            ->andWhere("so.code = 'sa'")
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
        return count($orders);
    }


    /*public static function getCountBidOrdersForAuthorGrid() {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.date_expire > :now')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
        return count($orders);
    }*/


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


    public static function getCutSentence($sentence, $size, $symbol = '...') {
        $sentence = mb_substr($sentence, 0, $size, "UTF-8");
        $pos = strrpos($sentence, ' ');
        if ($pos === false) {
            $sentence = $sentence . $symbol;
        } else {
            $sentence = substr($sentence, 0, $pos) . $symbol;
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
        return false;
    }


    public static function deleteSelectedAuthorBid($bidId, $user, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userBid = $em->getRepository(self::$_tableUserBid)
            ->findOneById($bidId);
        if ($userBid) {
            $userBid->setIsShowAuthor(0);
            $userBid->setIsShowClient(0);
            $nearBid = $em->getRepository(self::$_tableUserBid)->createQueryBuilder('ub')
                ->innerJoin('ub.user_order', 'uo')
                ->andWhere('ub.user = :user')
                ->andWhere('ub.user_order = :order')
                ->andWhere('ub.is_show_client = 0')
                ->andWhere('ub.id < :id')
                ->setParameter('user', $user)
                ->setParameter('order', $order)
                ->setParameter('id', $bidId)
                ->setMaxResults('1')
                ->orderBy('ub.id', 'DESC')
                ->getQuery()
                ->getResult();
            if ($nearBid) {
                $bid = $nearBid[0];
                $bid->setIsShowClient(1);
            }
            $em->flush();
            return true;
        }
        return false;
    }

    public static function refreshSelectedAuthorBid($bidId, $user, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userBid = $em->getRepository(self::$_tableUserBid)
            ->findOneById($bidId);
        if ($userBid) {
            $oldBid = $em->getRepository(self::$_tableUserBid)
                ->findOneBy(array('is_show_client' => 1));
            $oldBid->setIsShowClient(0);
            $userBid->setDateBid(new \DateTime());
            $userBid->setIsShowClient(1);
            $em->flush();
            return true;
        }
        return false;
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
        return false;
    }


    public static function hideBidForClient($bidId) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bid = $em->getRepository(self::$_tableUserBid)
            ->findOneBy(array('is_show_author' => 1, 'id' => $bidId));
        if ($bid) {
            $bid->setIsShowAuthor(0);
            $em->flush();
            return true;
        }
        return false;
    }


    public static function showOrderForAuthor($orderId, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('user' => $user, 'is_show_client' => 1, 'is_show_author' => 0, 'id' => $orderId));
        if ($order) {
            $status = $em->getRepository(self::$_tableStatusOrder)
                ->findOneByCode('sa');
            $order->setIsShowAuthor(1);
            $order->setStatusOrder($status);
            $em->flush();
            return true;
        }
        return false;
    }


    public static function getOrderByNumForClient($num, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneBy(array('user' => $user, 'is_show_client' => 1, 'num' => $num));
        if ($order) {
            return $order;
        }
        return false;
    }


    public static function getOrderByNumForAuthor($num, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->innerJoin('uo.status_order', 'so')
            ->andWhere('uo.num = :num')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('so.code = :code')
            ->setParameter('num', $num)
            ->setParameter('code', 'sa')
            ->setMaxResults('1')
            ->getQuery()
            ->getResult();
        if ($order) {
            return $order[0];
        } else {
            $user = $em->merge($user);
            $order = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->innerJoin(self::$_tableUserBid, 'ub', 'WITH', 'ub.user_order = uo')
                ->innerJoin('uo.status_order', 'so')
                ->andWhere('uo.num = :num')
                ->andWhere('ub.user = :user')
                ->andWhere('ub.is_show_client = 1')
                ->andWhere('ub.is_show_author = 1')
                ->andWhere('ub.is_select_client = 1')
                ->andWhere('ub.is_confirm_author = 1')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->andWhere('so.code IN(:code)')
                ->setParameter('code', array_values(array('w', 'e', 'g', 'f', 'cl', 'co')))
                ->setParameter('user', $user)
                ->setParameter('num', $num)
                ->setMaxResults('1')
                ->getQuery()
                ->getResult();
            if ($order) {
                return $order[0];
            }
        }
        return false;
    }

    /**
     * Upload author file to folder and set flag access to order
     * @param $user
     * @return bool
     */
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
            $user->setIsAccessOrder(1);
            $em->flush();
            return true;
        }
        return false;
    }


    public static function getNewOrdersForAuthorGrid($sOper = null, $sField = null, $sData = null, $firstRowIndex, $rowsPerPage, $sortingField, $sortingOrder, $user) {
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
                /*$orders = $em->getRepository(self::$_tableUserOrder)
                    ->findBy(
                        array('is_show_author' => 1, 'is_show_client' => 1),
                        array($sortingField => $sortingOrder),
                        $rowsPerPage,
                        $firstRowIndex
                    );*/
                $now = new \DateTime('now');
                $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                    //->select('favorite_order AS favorite')
                    //->select('MAX(ub.sum) AS max_sum, uo, ub.date_bid AS date_bid')
                    //->select('fo')
                    //->innerJoin('AcmeSecureBundle:UserBid', 'ub', 'WITH', 'ub.user_order = uo')
                    //->innerJoin('AcmeSecureBundle:UserBid', 'ub')
                    //->andWhere('ub.user_order = uo')
                    //->innerJoin('AcmeSecureBundle:StatusOrder', 'so', 'WITH', 'so = uo.status_order')
                    //->innerJoin(self::$_tableFavoriteOrder, 'fo')
                    //->innerJoin(self::$_tableFavoriteOrder, 'uo')
                    ->innerJoin('uo.status_order', 'so')
                    ->andWhere('uo.is_show_author = 1')
                    ->andWhere('uo.is_show_client = 1')
                    ->andWhere('uo.date_expire > :now')
                    ->andWhere('so.code IN(:code)')
                    //->groupBy('ub.user_order')
                    //->orWhere("so.code = 'ca'")
                    ->orderBy('uo.' . $sortingField, $sortingOrder)
                    ->setParameter('now', $now)
                    ->setParameter('code', array_values(array('sa')))
                    ->setFirstResult($firstRowIndex)
                    ->setMaxResults($rowsPerPage)
                    ->getQuery()
                    ->getResult();
                //var_dump(count($orders));die;
                $query = $em->createQuery("SELECT MAX(ub.sum) AS max_sum, MIN(ub.sum) AS min_sum, uo.id AS order_id FROM AcmeSecureBundle:UserOrder AS uo
                    JOIN AcmeSecureBundle:UserBid AS ub WITH ub.user_order = uo
                    WHERE uo.is_show_author = 1
                    AND uo.is_show_client = 1
                    AND ub.is_show_author = 1
                    AND ub.is_show_client = 1
                    AND uo.date_expire > :now
                    GROUP BY uo.id");
                $query->setParameter('now', $now);
                $bids = $query->getResult();
                $favoriteOrders = $em->getRepository(self::$_tableFavoriteOrder)
                    ->findByUser($user);

                $userId = $user->getId();
                $query = $em->getConnection()
                    ->prepare("SELECT * FROM (
                    SELECT ub.user_id AS uid,ub.sum,uo.id AS order_id FROM user_bid AS ub JOIN user_order AS uo ON ub.user_order_id = uo.id JOIN `user` AS u ON ub.user_id = u.id JOIN status_order so ON uo.status_order_id = so.id WHERE so.`code` = 'sa' AND ub.is_show_author = '1' AND u.id = '$userId' ORDER BY ub.date_bid DESC) AS t GROUP BY order_id");
                $query->execute();
                $authorBids = $query->fetchAll();
                $countBids = count($authorBids);
                //var_dump($countBids);die;

                foreach($orders as $order) {
                    foreach($bids as $index => $bid) {
                        if ($order->getId() == $bid['order_id']) {
                            $order->setMaxSum($bid['max_sum']);
                            break;
                        }
                    }
                    foreach($bids as $index => $bid) {
                        if ($order->getId() == $bid['order_id']) {
                            $order->setMinSum($bid['min_sum']);
                            break;
                        }
                    }
                    foreach($favoriteOrders as $index => $favoriteOrder) {
                        if ($order->getId() == $favoriteOrder->getUserOrder()->getId()) {
                            $order->setIsFavorite(1);
                            break;
                        }
                    }
                    for ($i = 0; $i < $countBids; $i++) {
                        if ($order->getId() == $authorBids[$i]['order_id']) {
                            $order->setAuthorLastSumBid($authorBids[$i]['sum']);
                            break;
                        }
                    }
                }
            }
            else {
                $orders = $em->getRepository(self::$_tableUserOrder)
                    ->findBy(
                        array('is_show_author' => 1, 'is_show_client' => 1),
                        //array('num' => 'ASC'),
                        $rowsPerPage,
                        $firstRowIndex
                    );
            }
        }
        return $orders;
    }


    public static function getFavoriteOrdersForAuthorGrid($firstRowIndex = null, $rowsPerPage = null, $user, $mode) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $user = $em->merge($user);
        if ($mode == 'orders') {
            $orders = $em->getRepository(self::$_tableFavoriteOrder)->createQueryBuilder('fo')
                ->innerJoin('fo.user_order', 'uo')
                ->andWhere('fo.user = :user')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->setParameter('user', $user)
                ->setFirstResult($firstRowIndex)
                ->setMaxResults($rowsPerPage)
                ->orderBy('fo.date_favorite', 'desc')
                ->getQuery()
                ->getResult();
            $userId = $user->getId();
            $query = $em->getConnection()
                ->prepare("SELECT * FROM (SELECT ub.user_id AS uid,ub.sum,uo.id AS order_id FROM user_bid AS ub JOIN user_order AS uo ON ub.user_order_id = uo.id JOIN `user` AS u ON ub.user_id = u.id WHERE ub.is_show_author = '1' AND u.id = '$userId' ORDER BY ub.date_bid DESC) AS t GROUP BY order_id");
            $query->execute();
            $bids = $query->fetchAll();
            foreach($orders as $order) {
                $userOrder = $order->getUserOrder();
                foreach($bids as $bid) {
                    if ($userOrder->getId() == $bid['order_id']) {
                        $userOrder->setAuthorLastSumBid($bid['sum']);
                        break;
                    }
                }
            }
            return $orders;
        } elseif ($mode == 'count') {
            $orders = $em->getRepository(self::$_tableFavoriteOrder)->createQueryBuilder('fo')
                ->innerJoin('fo.user_order', 'uo')
                ->andWhere('fo.user = :user')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->setParameter('user', $user)
                ->getQuery()
                ->getResult();
            return count($orders);
        }
    }


    public static function getCountBidOrdersForAuthorGrid($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->innerJoin('AcmeSecureBundle:UserBid', 'ub')
            ->andWhere('ub.user = :user')
            ->andWhere('ub.user_order = uo')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('ub.is_show_client = 1')
            ->andWhere('ub.is_show_author = 1')
            ->andWhere('uo.date_expire > :now')
            ->groupBy('ub.user_order')
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
        return count($orders);
    }


    public static function getBidOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->select('ub.sum AS curr_sum, uo, ub.date_bid AS date_bid')
            ->innerJoin('AcmeSecureBundle:UserBid', 'ub')
            ->andWhere('ub.user = :user')
            ->andWhere('ub.user_order = uo')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('ub.is_show_client = 1')
            ->andWhere('ub.is_show_author = 1')
            ->andWhere('uo.date_expire > :now')
            ->groupBy('ub.user_order')
            ->orderBy('ub.date_bid', 'desc')
            ->setFirstResult($firstRowIndex)
            ->setMaxResults($rowsPerPage)
            ->setParameter('user', $user)
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
        //var_dump($orders);die;
        /*$userId = $user->getId();
        $query = $em->getConnection()
            ->prepare("SELECT * FROM (SELECT ub.user_id AS uid,ub.sum,uo.id AS order_id FROM user_bid AS ub JOIN user_order AS uo ON ub.user_order_id = uo.id JOIN `user` AS u ON ub.user_id = u.id WHERE ub.is_show_author = '1' AND u.id = '$userId' ORDER BY ub.date_bid DESC) AS t GROUP BY order_id");
        $query->execute();
        $bids = $query->fetchAll();
        foreach($orders as $order) {
            $userOrder = $order->getUserOrder();
            foreach($bids as $bid) {
                if ($userOrder->getId() == $bid['order_id']) {
                    $userOrder->setAuthorLastSumBid($bid['sum']);
                    break;
                }
            }
        }*/
        return $orders;
    }


    public static function getOrderFiles($order, $mode = null, $user = null) {
        $em = self::getContainer()->get('doctrine')->getManager();
        if ($mode == 'client' && !empty($user) && !empty($order)) {
            $files = $em->getRepository(self::$_tableOrderFile)->createQueryBuilder('f')
                ->innerJoin(self::$_tableUser, 'u', 'WITH', 'f.user = u')
                ->innerJoin(self::$_tableUserOrder, 'uo', 'WITH', 'f.user_order = uo')
                ->andWhere('f.user != :user')
                ->andWhere('f.user_order = :user_order')
                ->andWhere('f.is_delete = 0')
                ->orderBy('f.date_upload', 'desc')
                ->setParameter('user', $user)
                ->setParameter('user_order', $order)
                ->getQuery()
                ->getResult();
            $arrayClientFiles = [];
            foreach($files as $file) {
                $dir = dirname($_SERVER['SCRIPT_FILENAME']) . self::getAttachmentsUrl('client', $order->getNum()) . $file->getName();
                if (file_exists($dir)) {
                    $file->setUrl(self::getContainer()->get('router')->generate('secure_client_download_file', array('type' => 'attachments', 'num' => $order->getNum(), 'filename' => $file->getName())));
                    //$file->setUrl(Helper::getFullUrl() . self::getAttachmentsUrl('client', $order->getNum()) . $file->getName());
                    $file->setThumbnailUrl(self::getThumbnailUrlFile($file->getName(), $order));
                    $arrayClientFiles[] = $file;
                }
            }
            return $arrayClientFiles;
        } elseif ($mode == 'author' && !empty($user) && !empty($order)) {
            $files = $em->getRepository(self::$_tableOrderFile)->createQueryBuilder('f')
                ->innerJoin(self::$_tableUser, 'u', 'WITH', 'f.user = u')
                ->innerJoin(self::$_tableUserOrder, 'uo', 'WITH', 'f.user_order = uo')
                ->andWhere('f.user = :user')
                ->andWhere('f.user_order = :user_order')
                ->andWhere('f.is_delete = 0')
                ->orderBy('f.date_upload', 'desc')
                ->setParameter('user', $user)
                ->setParameter('user_order', $order)
                ->getQuery()
                ->getResult();
        } else {
            $files = $em->getRepository(self::$_tableOrderFile)
                ->findBy(array('user_order' => $order));
        }
        return $files;
    }


    public static function getAllAuthorsBidsForSelectedOrder($user, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bids = $em->getRepository(self::$_tableUserBid)
            ->findBy(array('user' => $user, 'user_order' => $order, 'is_show_author' => 1), array('date_bid' => 'DESC'));
        return $bids;
    }


    public static function getAllAuthorsBidsForOrder($order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $id = $order->getId();
        $stmt = $em->getConnection()
        //id,sum,day,comment,date_bid,is_client_date,user_id,avatar,login
            ->prepare("SELECT * FROM (SELECT b.user_id uid,b.*,u.avatar,u.login FROM user_bid b JOIN user_order uo ON b.user_order_id = uo.id JOIN `user` u ON b.user_id = u.id WHERE b.user_order_id = $id AND b.is_show_author = 1 AND b.is_show_client = 1 ORDER BY b.date_bid DESC) AS t GROUP BY uid ORDER BY t.sum ASC");
        $stmt->execute();
        $bids = $stmt->fetchAll();
        //var_dump($bids); die;
        return $bids;
    }


    /** Sets new author's bid from page selected order or page all new orders
     * @param $postData
     * @param $user
     * @param $order
     */
    public static function setAuthorBid($postData, $user, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $sum = $postData['fieldSum'];
        $sum = str_replace(' ', '', $sum);
        $comment = $postData['fieldComment'];
        $userBid = new UserBid();
        $user = $em->merge($user);
        $userBid->setUser($user);
        $userBid->setUserOrder($order);
        $userBid->setSum($sum);
        $userBid->setComment($comment);
        if (isset($postData['fieldDay']) && !empty($postData['fieldDay'])) {
            $day = $postData['fieldDay'];
            $userBid->setDay($day);
        } else {
            $userBid->setIsClientDate(1);
        }
        $em->persist($userBid);
        $bids = $em->getRepository(self::$_tableUserBid)->createQueryBuilder('ub')
            ->innerJoin('ub.user_order', 'uo')
            ->andWhere('ub.user = :user')
            ->andWhere('ub.user_order = :order')
            ->andWhere('ub.is_show_client = 1')
            ->setParameter('user', $user)
            ->setParameter('order', $order)
            ->getQuery()
            ->getResult();
        foreach($bids as $bid) {
            $bid->setIsShowClient(0);
        }
        $em->flush();
        return true;
    }


    public static function convertFormErrorObjToString($error) {
        $errorMessageTemplate = $error->getMessageTemplate();
        foreach ($error->getMessageParameters() as $key => $value) {
            $errorMessageTemplate = str_replace($key, $value, $errorMessageTemplate);
        }
        return $errorMessageTemplate;
    }


    //public static function getFullPathToAvatar($user = null, $fileName = null, $userId = null) {
    public static function getFullPathToAvatar($user = null, $userId = null) {
        if (!$user && $userId) {
            $user = self::getUserById($userId);
        }
        $fileName = $user->getAvatar();
        $userRole = $user->getUserRole()->getCode();
        if ($fileName == 'default_m.jpg' || $fileName == 'default_w.jpg' || $fileName == 'default.png') {
            return self::$_avatarFolder . $fileName;
        } else {
            $userId = $user->getId();
            return self::$_avatarFolder . $userRole . '/' . $userId . '/' . $fileName;
        }
    }

    /** Select author bid and send notice mail to author
     * @param $user
     * @param $bidId
     * @param $order
     * @param $container
     * @return bool
     */
    /*public static function selectAuthorBid($user, $bidId, $order, $container) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bid = $em->getRepository(self::$_tableUserBid)
            ->findOneById($bidId);
        if ($bid) {
            $bid->setIsSelectClient(1);
            $statusOrder = $em->getRepository(self::$_tableStatusOrder)
                ->findOneByCode('ca');
            $order->setStatusOrder($statusOrder);
            $em->flush();
            $orderNum = $order->getNum();
            $email = $bid->getUser()->getEmail();
            $path = $container->get('router');
            $path = 'http://localhost' . $path->generate('secure_author_order', array('num' => $orderNum));
            $subject = "Вас выбрали, подтвердите, что согласны!";
            $body = "Вы выбраны для выполнения заказа № " . $orderNum . ". Тема - " . $order->getTheme() . ". Заказчик - " . $user->getLogin() . ". <a href='" . $path . "'>Переход на заказ</a>";
            self::sendMail($email, $subject, $body, $container);
            return true;
        }
        return false;
    }*/


    public static function sendMail($email, $subject, $body, $container) {
        $mailSender = $container->getParameter('mailSender');
        $mailTitle = $container->getParameter('mailTitle');
        $mailer = $container->get('mailer');
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($mailSender, $mailTitle)
            ->setTo($email)
            //->setTo("gzhelka777@mail.ru")
            ->setBody(
                '<html>' .
                '<head></head>' .
                '<body>' .
                $body .
                '</body>' .
                '</html>',
                'text/html'
            );
        $mailer->send($message);
    }

    /** Cancel selected author's bid
     * @param $bidId
     * @return bool
     */
    /*public static function cancelSelectedAuthorBid($bidId, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bid = $em->getRepository(self::$_tableUserBid)
            ->findOneBy(array('id' => $bidId, 'is_show_client' => 1, 'is_confirm_author' => 0, 'is_select_client' => 1));
        if ($bid) {
            $bid->setIsSelectClient(0);
            $statusOrder = $em->getRepository(self::$_tableStatusOrder)
                ->findOneByCode('sa');
            $order->setStatusOrder($statusOrder);
            $em->flush();
            return true;
        }
        return false;
    }*/


    public static function getCountBidsForEveryOrder($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bids = $em->getRepository(self::$_tableUserBid)->createQueryBuilder('ub')
            ->select('COUNT(ub.id) AS count_bids,uo.id AS user_order_id')
            ->innerJoin('ub.user_order', 'uo')
            ->andWhere('uo.user = :user')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('ub.is_show_author = 1')
            ->andWhere('ub.is_show_client = 1')
            //->groupBy('uo.num')
            ->groupBy('user_order_id')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        //var_dump($bids); die;
        return $bids;
    }


    public static function getOrderById($orderId) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneById($orderId);
        return $order;
    }


    public static function saveEditedTaskAndDateExpireForOrder($order, $newTask, $newDateExpire) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order->setTask($newTask);
        $order->setDateExpire($newDateExpire);
        $order->setDateEdit(new \DateTime());
        $em->merge($order);
        $em->flush();
    }

    /** Create auction bid with client's price and day for author by his made bid
     * @param $bidId
     * @param $order
     * @param $auctionPrice
     * @param $auctionDay
     * @return bool
     */
    /*public static function createAuctionByAuthorBid($bidId, $order, $auctionPrice, $auctionDay, $container) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bid = $em->getRepository(self::$_tableUserBid)
            ->findOneById($bidId);
        if ($bid) {
            $user = $bid->getUser();
            $auctionBid = new AuctionBid();
            $auctionBid->setDay($auctionDay);
            $auctionBid->setPrice($auctionPrice);
            $auctionBid->setUserOrder($order);
            $auctionBid->setUser($user);
            $em->persist($auctionBid);
            $em->flush();
            $orderNum = $order->getNum();
            $email = $user->getEmail();
            $path = $container->get('router');
            $path = 'http://localhost' . $path->generate('secure_author_order', array('num' => $orderNum));
            $subject = "Заказчик предложил вам торг!";
            $body = "Заказчик предложил вам торг в заказе № " . $orderNum . ". Тема - " . $order->getTheme() . ". Заказчик - " . $user->getLogin() . ". <a href='" . $path . "'>Переход на заказ</a>";
            self::sendMail($email, $subject, $body, $container);
            return true;
        }
        return false;
    }*/

    public static function authorConfirmSelection($order, $user, $bidId, $mode, $container) {
        if ($mode == null) {
            return false;
        }
        elseif ($mode == 'confirm' || $mode == 'fail') {
            $em = self::getContainer()->get('doctrine')->getManager();
            $bid = $em->getRepository(self::$_tableUserBid)
                ->findOneById($bidId);
            if ($bid) {
                if ($mode == 'confirm') {
                    $bid->setIsConfirmAuthor(1);
                    $subject = "Автор согласился выполнять заказ!";
                    $statusOrder = $em->getRepository(self::$_tableStatusOrder)
                        ->findOneByCode('w');
                    $order->setStatusOrder($statusOrder);
                }
                else {
                    $bid->setIsConfirmFail(1);
                    $subject = "Автор отказался выполнять заказ!";
                }
                $selectBid = new SelectBid();
                $selectBid->setUser($user);
                $selectBid->setUserBid($bid);
                $em->persist($selectBid);
                $em->flush();
                $orderNum = $order->getNum();
                $email = $bid->getUser()->getEmail();
                $path = $container->get('router');
                $path = 'http://localhost' . $path->generate('secure_author_order', array('num' => $orderNum));
                $body = "<a href='" . $path . "'>Переход на заказ</a>";
                self::sendMail($email, $subject, $body, $container);
                return true;
            }
            return false;
        }
    }

    /** Gets the author's bid which client selected
     * @param $user
     * @param $order
     * @return bool
     */
    public static function getClientSelectedBid($user, $order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $bid = $em->getRepository(self::$_tableUserBid)
            ->findOneBy(array('user' => $user, 'user_order' => $order, 'is_select_client' => 1, 'is_show_author' => 1, 'is_show_client' => 1, 'is_confirm_author' => 0, 'is_confirm_fail' => 0));
        if ($bid) {
            return true;
        }
        return false;
    }


    public static function favoriteOrder($orderId, $user, $type) {
        $em = self::getContainer()->get('doctrine')->getManager();
        if ($type == "favorite" || $type == "unfavorite") {
            $order = $em->getRepository(self::$_tableUserOrder)
                ->findOneById($orderId);
            if ($order) {
                $user = $em->merge($user);
                if ($type == "favorite") {
                    $favoriteOrder = new FavoriteOrder();
                    $favoriteOrder->setUserOrder($order);
                    $favoriteOrder->setUser($user);
                    $em->persist($favoriteOrder);
                    $em->flush();
                } else {
                    $favoriteOrder = $em->getRepository(self::$_tableFavoriteOrder)
                        ->findOneBy(array('user_order' => $order, 'user' => $user));
                    $em->remove($favoriteOrder);
                    $em->flush();
                }
                return true;
            }
        }
        return false;
    }


    public static function getCountryByCode($countryCode) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $country = $em->getRepository(self::$_tableCountry)
            ->findOneByCode($countryCode);
        if ($country) {
            return $country;
        }
        return false;
    }


    public static function addNewUserInfo($userInfo) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($userInfo);
        $em->flush();
    }

    /**
     * Get user's role by user id
     * @param $userId
     */
    public static function getUserRoleByRoleId($roleId) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $role = $em->getRepository(self::$_tableUserRole)
            ->findOneById($roleId);
        if ($role) {
            return $role;
        }
        return false;
    }


    public static function addNewUser($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $em->persist($user);
        $em->flush();
        return $user;
    }


    public static function getChatMessages($user, $order, $lastId) {
       $em = self::getContainer()->get('doctrine')->getManager();
       $messages = $em->getRepository(self::$_tableWebchatMessage)->createQueryBuilder('wm')
           //->select("u.id AS sender_id,wm.date_write,wm.message AS msg,wm.id,u.login AS user_login")
           ->andWhere('wm.id > :lastId')
            ->innerJoin('wm.user','u')
            ->andWhere('wm.user_order = :order')
            ->setParameter('order', $order)
            ->setParameter('lastId', $lastId)
            ->getQuery()
            //->useResultCache(true, 60, 'chat')
            //->setResultCacheId('chat')
            //->getArrayResult();
            ->getResult();
       /* $query = $em->createQuery("SELECT u.id AS sender_id,wm.date_write,wm.message AS msg,wm.id,u.login AS user_login
            FROM AcmeSecureBundle:WebchatMessage AS wm
            INNER JOIN AcmeAuthBundle:User AS u WITH u = :user
            WHERE wm.user_order = :order AND wm.id > '$lastId'")
            ->setParameter('user', $user)
            ->setParameter('order', $order)
            ->useResultCache(true, 36000, 'chat')
            //->useResultCache(true)
            //->useQueryCache(true)
            //->setResultCacheId('chat')
        ;
        //var_dump($query->getQueryCacheDriver());die;
        $messages = $query->getResult();*/
        return $messages;
    }


    public static function addNewWebchatMessage($user, $order, $message) {
        $em = self::getContainer()->get('doctrine')->getManager();
        //$cacheDriver = $em->getConfiguration()->getResultCacheImpl();
        //$cacheDriver->delete('chat');
        //var_dump($em->getUnitOfWork());die;
        //$detachedEntity = unserialize($user);
        //$entity = $em->merge($detachedEntity);
        $webchatMessage = new WebchatMessage();
        $webchatMessage->setUserOrder($order);
        $user = self::getUserById($user->getId());
        $webchatMessage->setUser($user);
        $webchatMessage->setMessage($message);
        //$em->merge($entity);
        $em->persist($webchatMessage);
        $em->flush();
        return $webchatMessage->getId();
        /*$date = new \DateTime();
        $date = $date->format('Y-m-d H:i:s');
        $userId = $user->getId();
        $orderId = $order->getId();
        $stmt = $em->getConnection()
            ->prepare("INSERT INTO webchat_message(message, date_write, user_id, user_order_id) VALUES('$message','$date','$userId','$orderId')");
        $stmt->execute();*/
    }


    public static function checkUserAccessForOrder($user, $order) {
        $statusOrder = $order->getStatusOrder()->getCode();
        if ($statusOrder == 'w') {
            $em = self::getContainer()->get('doctrine')->getManager();
            $bid = $em->getRepository(self::$_tableUserBid)
                ->findOneBy(array('user' => $user, 'user_order' => $order, 'is_show_client' => 1, 'is_select_client' => 1, 'is_confirm_author' => 1));
            if ($bid) {
                return true;
            }
        } elseif ($statusOrder == 'sa') {
            return true;
        }   elseif ($statusOrder == 'ca') {
            return true;
        }
        return false;
    }


    /*public static function checkUserAccessForOrders($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(self::$_tableUser)
            ->findOneByUser($user);
        if ($allowAccess) {
            return true;
        }
        return false;
    }*/


    public static function getUserAvatar($user) {
        $pathAvatar = Helper::getFullPathToAvatar($user);
        $userAvatar = "<img src='$pathAvatar' align='middle' alt='Аватар' width='110px' height='auto' class='thumbnail'>";
        $user->setAvatar($userAvatar);
        return $user;
    }

    public static function getUserAvatarForPdf($user) {
        $pathAvatar =  $_SERVER['DOCUMENT_ROOT'] . Helper::getFullPathToAvatar($user);
        $userAvatar = "<img src='" . $pathAvatar . "'align='middle' alt='Аватар' width='110px' height='auto' class='thumbnail'>";
        return $userAvatar;
    }


    public static function getUserLinkProfile($order, $role, $container) {
        if ($role == "author") {
            $em = self::getContainer()->get('doctrine')->getManager();
            $bid = $em->getRepository(self::$_tableUserBid)
                ->findOneBy(array('user_order' => $order, 'is_show_client' => 1, 'is_select_client' => 1, 'is_confirm_author' => 1));
            if ($bid) {
                $user = $bid->getUser();
                $login = $user->getLogin();
                //$authorAvatar = $user->getAvatar();
                $authorId = $user->getId();
                $pathAvatar = Helper::getFullPathToAvatar($user);
                $url = $container->get('router')->generate('secure_client_view_author', array('id' => $authorId));
            } else {
                return null;
            }
        } elseif ($role == "client") {
            $login = $order->getUser()->getLogin();
            $pathAvatar = Helper::getFullPathToAvatar($order->getUser());
            $clientId = $order->getUser()->getId();
            $url = $container->get('router')->generate('secure_author_view_client', array('id' => $clientId));
        }
        $link = "<img src='$pathAvatar' align='middle' alt='Аватар' width='110px' height='auto' class='thumbnail'><a href='$url' target='_blank' class='label label-primary'>$login</a>";
        return $link;
    }


    public static function generateNewPassForRecovery($userEmail) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $user = $em->getRepository(self::$_tableUser)
            ->findOneByEmail($userEmail);
        if ($user) {
            $userSalt = $user->getSalt();
            $unEncodePassword = self::getRandomValue(3);
            $encodePassword = self::getRegPassword($unEncodePassword, $userSalt);
            $hashCode = self::getRandomValue(15);
            $user->setHash($hashCode);
            $user->setRecoveryPassword($encodePassword);
            $em->flush();
            $user->setHash($hashCode);
            $user->setUnEncodePass($unEncodePassword);
            return $user;
        }
        return false;
    }


    public static function deleteAuthorBid($user, $numOrder) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneByNum($numOrder);
        $bid = $em->getRepository(self::$_tableUserBid)
            ->findOneBy(array('user_order' => $order, 'user' => $user));
        if ($bid) {
            $em->remove($bid);
            $em->flush();
            return true;
        }
        return false;
    }


    public static function getWorkOrdersForAuthorGrid($firstRowIndex = null, $rowsPerPage = null, $user, $mode) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $user = $em->merge($user);
        if ($mode == "getRecords") {
            $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->select('ub.sum AS curr_sum, uo')
                ->innerJoin(self::$_tableUserBid, 'ub', 'WITH', 'ub.user_order = uo')
                ->andWhere('ub.user = :user')
                ->andWhere('ub.user_order = uo')
                ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
                ->andWhere('so.code IN(:code)')
                ->andWhere('ub.is_select_client = 1')
                ->andWhere('ub.is_confirm_author = 1')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->andWhere('ub.is_show_client = 1')
                ->andWhere('ub.is_show_author = 1')
                //->andWhere('uo.date_expire > :now')
                ->groupBy('ub.user_order')
                ->orderBy('uo.date_expire', 'asc')
                ->setFirstResult($firstRowIndex)
                ->setMaxResults($rowsPerPage)
                ->setParameter('user', $user)
                ->setParameter('code', array_values(array('w', 'e', 'g')))
                //->setParameter('now', new \DateTime('now'))
                ->getQuery()
                ->getResult();
            return $orders;
        } elseif ($mode == 'getCountRecords') {
            $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->innerJoin(self::$_tableUserBid, 'ub', 'WITH', 'ub.user_order = uo')
                ->andWhere('ub.user = :user')
                ->andWhere('ub.user_order = uo')
                ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
                ->andWhere('so.code IN(:code)')
                ->andWhere('ub.is_select_client = 1')
                ->andWhere('ub.is_confirm_author = 1')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->andWhere('ub.is_show_client = 1')
                ->andWhere('ub.is_show_author = 1')
                ->groupBy('ub.user_order')
                ->setParameter('user', $user)
                ->setParameter('code', array_values(array('w', 'e', 'g')))
                ->getQuery()
                ->getResult();
            return count($orders);
        }
    }


    public static function getFinishOrdersForAuthorGrid($firstRowIndex = null, $rowsPerPage = null, $user, $mode) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $user = $em->merge($user);
        if ($mode == "getRecords") {
            $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->innerJoin(self::$_tableUserBid, 'ub', 'WITH', 'ub.user_order = uo')
                ->andWhere('ub.user = :user')
                ->andWhere('ub.user_order = uo')
                ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
                ->andWhere('so.code = :code')
                ->andWhere('ub.is_select_client = 1')
                ->andWhere('ub.is_confirm_author = 1')
                ->groupBy('ub.user_order')
                ->orderBy('uo.date_complete', 'desc')
                ->setFirstResult($firstRowIndex)
                ->setMaxResults($rowsPerPage)
                ->setParameter('user', $user)
                ->setParameter('code', 'f')
                ->getQuery()
                ->getResult();
            return $orders;
        } elseif ($mode == 'getCountRecords') {
            $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->innerJoin(self::$_tableUserBid, 'ub', 'WITH', 'ub.user_order = uo')
                ->andWhere('ub.user = :user')
                ->andWhere('ub.user_order = uo')
                ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
                ->andWhere('so.code = :code')
                ->andWhere('ub.is_select_client = 1')
                ->andWhere('ub.is_confirm_author = 1')
                ->groupBy('ub.user_order')
                ->setParameter('user', $user)
                ->setParameter('code', 'f')
                ->getQuery()
                ->getResult();
            return count($orders);
        }
    }


    /*public static function getCountWorkOrdersForAuthorGrid($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->innerJoin('AcmeSecureBundle:UserBid', 'ub', 'WITH', 'ub.user_order = uo')
            ->andWhere('ub.user = :user')
            ->andWhere('ub.user_order = uo')
            ->innerJoin('uo.status_order', 'so', 'WITH', 'so = uo.status_order')
            ->andWhere('ub.is_select_client = 1')
            ->andWhere('ub.is_confirm_author = 1')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('ub.is_show_client = 1')
            ->andWhere('ub.is_show_author = 1')
            ->groupBy('ub.user_order')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
        return count($orders);
    }*/


    public static function getAuthorTotalCompletedOrdersForClient($client, $author) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
            ->select('ub.sum AS curr_sum, uo')
            ->innerJoin(self::$_tableUserBid, 'ub', 'WITH', 'ub.user_order = uo')
            ->andWhere('ub.user = :author')
            ->andWhere('ub.user_order = uo')
            ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
            ->andWhere('uo.user = :client')
            ->andWhere('so.code = :code')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('ub.is_select_client = 1')
            ->andWhere('ub.is_confirm_author = 1')
            ->groupBy('ub.user_order')
            ->orderBy('uo.id', 'asc')
            ->setParameter('client', $client)
            ->setParameter('author', $author)
            ->setParameter('code', 'co')
            ->getQuery()
            ->getResult();
            return $orders;
    }


    public static function getUsersForWebchat() {
        $em = self::getContainer()->get('doctrine')->getManager();
        $stmt = $em->getConnection()
            ->prepare("SELECT * FROM (SELECT b.user_id AS uid,b.*,u.avatar,u.login FROM user_bid AS b JOIN user_order AS uo ON b.user_order_id = uo.id JOIN `user` AS u ON b.user_id = u.id WHERE b.user_order_id = '$id' AND b.is_show_author = '1' ORDER BY b.date_bid DESC) AS t GROUP BY uid");
        $stmt->execute();
        $users = $stmt->fetchAll();
        return $users;
    }


    public static function getClientTotalOrders($user, $mode) {
        $em = self::getContainer()->get('doctrine')->getManager();
        if ($mode == 'totalOrders') {
            $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
                ->innerJoin(self::$_tableUser, 'u', 'WITH', 'uo.user = u')
                ->andWhere('uo.user = :user')
                ->andWhere('so.code IN(:code)')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->setParameter('user', $user)
                ->setParameter('code', array_values(array('w', 'e', 'co', 'g', 'cl')))
                ->orderBy('uo.num', 'asc')
                ->getQuery()
                ->getResult();
        } elseif ($mode == 'totalTypeOrders') {
            $orders = $em->getRepository(self::$_tableUserOrder)->createQueryBuilder('uo')
                ->innerJoin(self::$_tableStatusOrder, 'so', 'WITH', 'so = uo.status_order')
                ->innerJoin(self::$_tableUser, 'u', 'WITH', 'uo.user = u')
                ->andWhere('uo.user = :user')
                ->andWhere('so.code IN(:code)')
                ->andWhere('uo.is_show_client = 1')
                ->andWhere('uo.is_show_author = 1')
                ->setParameter('user', $user)
                ->setParameter('code', array_values(array('w', 'e', 'co', 'g', 'cl')))
                ->orderBy('uo.num', 'asc')
                //->groupBy('uo.type_order')
                ->getQuery()
                ->getResult();
        }
        if ($orders) {
            return $orders;
        }
        return null;
    }


    public static function createCancelOrderRequest($order, $preparedData, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $cancelRequest = new CancelRequest();
        $cancelRequest->setUserOrder($order);
        $cancelRequest->setComment($preparedData['comment']);
        $cancelRequest->setPercent($preparedData['percent']);
        $cancelRequest->setIsTogetherApply($preparedData['isTogetherApply']);
        $cancelRequest->setCreator($user->getId());
        $em->persist($cancelRequest);
        $em->flush();
        self::sendEmail('createCancelRequest', $user, $order, $preparedData);
        return $cancelRequest;
    }


    public static function getCancelRequestsByOrder($order, $user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $cancelRequests = $em->getRepository(self::$_tableCancelRequest)
            ->findBy(
                array('user_order' => $order, 'is_show' => 1),
                array('date_create' => 'ASC')
            );
        $arrayRoles = ['author' => 'Автор', 'client' => 'Заказчик', 'system' => 'Система'];
        foreach($cancelRequests as $cancelRequest) {
            if ($cancelRequest->getCreator() == $user->getId()) {
                $codeRole = $user->getUserRole()->getCode();
                $cancelRequest->setCreatorRole($arrayRoles[$codeRole]);
            } elseif ($cancelRequest->getCreator() == '17') {
                $cancelRequest->setCreatorRole($arrayRoles['system']);
            } elseif ($cancelRequest->getCreator() != '17') {
                $codeRole = $order->getUser()->getUserRole()->getCode();
                $cancelRequest->setCreatorRole($arrayRoles[$codeRole]);
            }
        }
        return $cancelRequests;
    }


    public static function getDateVerdict($order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $orderId = $order->getId();
        $query = $em->getConnection()
            ->prepare("SELECT date_create FROM cancel_request cr WHERE cr.user_order_id = '$orderId' AND cr.is_show = '1' LIMIT 1");
        $query->execute();
        $result = $query->fetch();
        //??? days for verdict
        $dateVerdict = date("d.m.Y H:i", strtotime($result['date_create'] . ' + 3 days'));
       // $dateVerdict = date("d.m.Y H:i") <  $dateVerdict ? $dateVerdict : "";
        return $dateVerdict;
    }


    public static function addNewOrderFile($fileUpload) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $session = self::getContainer()->get('session');
       // $user = self::getUserById($session->get('user'));
        $user = $em->merge($session->get('user'));
        $order = self::getOrderByNumForAuthor($session->get('order'), $user);
        if ($order) {
            $file = new OrderFile();
            $file->setName($fileUpload->name);
            $file->setSize($fileUpload->size);
            $file->setUser($user);
            $file->setUserOrder($order);
            $em->persist($file);
            $em->flush();
            return $file->getDateUpload()->format('d.m.Y H:i');
        }
        return null;
    }


    public static function getFullUrl() {
        return
            (isset($_SERVER['HTTPS']) ? 'https://' : 'http://').
            (isset($_SERVER['REMOTE_USER']) ? $_SERVER['REMOTE_USER'].'@' : '').
            (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : ($_SERVER['SERVER_NAME'].
                (isset($_SERVER['HTTPS']) && $_SERVER['SERVER_PORT'] === 443 ||
                $_SERVER['SERVER_PORT'] === 80 ? '' : ':'.$_SERVER['SERVER_PORT']))).
            substr($_SERVER['SCRIPT_NAME'],0, strrpos($_SERVER['SCRIPT_NAME'], '/'));
    }


    public static function getThumbnailUrlFile($fileName, $order){
        $arrayAllowedExt = ['jpg', 'jpeg', 'png', 'gif'];
        $extensionFile = self::getExtensionFile($fileName);
        $thumbnailUrl = !in_array($extensionFile, $arrayAllowedExt) ? '/study/web/bundles/images/icons/' . $extensionFile . '.png' : '/study/web/uploads/attachments/orders/' . $order->getNum() . '/thumbnails_client/' . $fileName;
        return $thumbnailUrl;
    }


    public static function getAttachmentsUrl($mode, $num) {
        if ($mode == 'client') {
            return '/uploads/attachments/orders/' . $num . '/client/';
        } elseif ($mode == 'author') {
            return '/uploads/attachments/orders/' . $num . '/author/';
        }
    }


    public static function setOrderStatus($order, $statusName) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $arrayStatuses = ['completed' => 'co', 'work' => 'w', 'guarantee' => 'g', 'cancel' => 'cl'];
        $newStatusCode = $arrayStatuses[$statusName];
        $status = $em->getRepository(self::$_tableStatusOrder)
            ->findOneByCode($newStatusCode);
        if ($newStatusCode == 'g') {
            $order->setStatusOrder($status);
            //Guarantee period ???
            $dateGuarantee = date("Y-m-d", strtotime(date('Y-m-d') . ' + 7 days'));
            $dateGuarantee = self::getFormatDateForInsert($dateGuarantee, 'Y-m-d');
            $order->setDateGuarantee($dateGuarantee);
            $em->flush();
            //return $status->getName();
        } elseif ($newStatusCode == 'cl') {
            $order->setStatusOrder($status);
            $order->setDateCancel(new \DateTime());
            $em->flush();
            return $order;
        }
    }


    public static function getDiffBetweenDates($date) {
       //var_dump($date);die;
        $dateNow = new \DateTime();
        return date_diff($dateNow, $date);
        //return date("d", (strtotime($dateNow->format("d.m.Y")) - strtotime($date->format("d.m.Y"))));
        /*$date1 = strtotime($date->format('Y-m-d H:i'));
        $date2 = time();
        $subTime = $date1 - $date2;
        $y = ($subTime/(60*60*24*365));
        $d = ($subTime/(60*60*24))%365;
        $h = ($subTime/(60*60))%24;
        $m = ($subTime/60)%60;
        echo "Difference between ".date('Y-m-d H:i:s',$date1)." and ".date('Y-m-d H:i:s',$date2)." is:\n";
        echo $y." years\n";
        echo $d." days\n";
        echo $h." hours\n";
        echo $m." minutes\n";*/
    }


    public static function getUserPsByUser($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userPs = $em->getRepository(self::$_tableUserPs)
            ->findBy(array('user' => $user));
        // ->array($sField => $sortingOrder);
        return $userPs;
    }


    public static function addNewUserPs($user, $postData) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $typePs = $em->getRepository(self::$_tableTypePs)
            ->findOneByCode($postData['fieldType']);
        $user = Helper::getUserById($user->getId());
       // $em->detach($user);
        //var_dump($user);die;
        $userPs = new UserPs();
        $userPs->setNum($postData['fieldNum']);
        $userPs->setName($postData['fieldName']);
        $userPs->setTypePs($typePs);
        $userPs->setUser($user);
        $em->persist($userPs);
        $em->flush();
    }


    public static function getUserPsByPsId($user, $psId) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userPs = $em->getRepository(self::$_tableUserPs)
            ->findOneBy(array('user' => $user, 'id' => $psId));
        if ($userPs) {
            return true;
        }
        return false;
    }


    public static function deleteUserPs($psId) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userPs = $em->getRepository(self::$_tableUserPs)
            ->findOneById($psId);
        $em->remove($userPs);
        $em->flush();
    }


    public static function updateUserPs($psId, $postData) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $userPs = $em->getRepository(self::$_tableUserPs)
            ->findOneById($psId);
        $typePs = $em->getRepository(self::$_tableTypePs)
            ->findOneByCode($postData['fieldType']);
        $userPs->setNum($postData['fieldNum']);
        $userPs->setName($postData['fieldName']);
        $userPs->setTypePs($typePs);
        $userPs->setDateEdit(new \DateTime());
        $em->flush();
    }


    public static function updateMailOptions($user, $arrayOptions) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $mailOptions = $em->getRepository(self::$_tableMailOption)
            ->findOneByUser($user);
        $mailOptions->setChatResponse(0);
        $mailOptions->setNewOrders(0);
        if (is_array($arrayOptions)) {
            if (in_array('cr', $arrayOptions)) {
                $mailOptions->setChatResponse(1);
            }
            if (in_array('no', $arrayOptions)) {
                $mailOptions->setNewOrders(1);
            }
        }
        $mailOptions->setDateEdit(new \DateTime());
        $em->flush();
    }


    public static function getMailOptions($user) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $mailOptions = $em->getRepository(self::$_tableMailOption)
            ->findOneByUser($user);
        $str = "";
        if ($mailOptions->getChatResponse()) {
            $str = 'cr';
        }
        if ($mailOptions->getNewOrders()) {
            $str .= 'no';
        }
        return str_split($str, 2);
    }


    public static function updateUserAvatar($fileName, $user = null, $mode = null) {
        $em = self::getContainer()->get('doctrine')->getManager();
        if ($mode == 'controller') {
            //$user = self::getUserById($user->getId());
            $user = $em->merge($user);
        } elseif ($mode == 'uploader') {
            $session = self::getContainer()->get('session');
            //$user = self::getUserById($session->get('user'));
            $user = $session->get('user');
            $user = $em->merge($user);
        }
        $user->setAvatar($fileName);
        $user->setDateUploadAvatar(new \DateTime());
        $em->flush();
        /*try {
            $em->flush();
        } catch (\Exception $exc){die($exc);};*/
    }


    public static function getAvatarOption($user) {
        $avatar = $user->getAvatar();
        if (!empty($avatar)) {
            $arrAvatars = ['man' => 'default_m.jpg', 'woman' => 'default_w.jpg'];
            if ($key = array_search($avatar, $arrAvatars)) {
                return $key;
            }
            return 'custom';
        } else {
            return 'Error';
        }
    }


    public static function deleteAllFilesFromFolder($dir) {
        foreach(glob($dir . '*.*') as $file){
            unlink($file);
        }
    }


    public static function getAvatarFolder($user) {
        $userRole = $user->getUserRole()->getCode();
        if ($userRole == 'author') {
            return $_SERVER['DOCUMENT_ROOT'] . '/study/web/uploads/avatars/author/' . $user->getId() . '/';
        }
    }


    public static function getOrderIdByOrderNum($orderNum) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $order = $em->getRepository(self::$_tableUserOrder)
            ->findOneByNum($orderNum);
        return $order->getId();
    }


    public static function getRemainingTime($order, $mode) {
        if ($mode == 'work') {
            $dateOrderExpire = $order[0]->getDateExpire();
            $remaining = self::getDiffBetweenDates($dateOrderExpire);
        } elseif ($mode == 'guarantee') {
            $dateOrderGuarantee = $order[0]->getDateGuarantee();
            $remaining = self::getDiffBetweenDates($dateOrderGuarantee);
        }
        return $remaining->format('%d дн. %h ч. %i мин.');
    }


    public static function getFormErrors($form) {
        $errors = [];
        $arrayResponse = [];
        foreach ($form as $fieldName => $formField) {
            $errors[$fieldName] = $formField->getErrors();
        }
        foreach ($errors as $index => $error) {
            if (isset($error[0])) {
                $arrayResponse[$index] = $error[0]->getMessage();
            }
        }
        return $arrayResponse;
    }


    public static function isCorrectOrder($num) {
        if (is_numeric($num) && $num > 0) {
            return true;
        }
        return false;
    }


    public static function prepareCancelRequestData($postData) {
        $comment = $postData['fieldComment'];
        $comment = strip_tags($comment, 'br');
        $isTogetherApply = isset($postData['fieldIsTogetherApply']) ? 1 : 0;;
        $percent = $postData['fieldPercent'];
        $textPercent = $isTogetherApply ? "По обоюдному согласию с заказчиком." : $percent . '%';
        $percent = $isTogetherApply ? 0 : $percent;
        $arrPreparedData = [];
        $arrPreparedData['comment'] = $comment;
        $arrPreparedData['percent'] = $percent;
        $arrPreparedData['isTogetherApply'] = $isTogetherApply;
        $arrPreparedData['textPercent'] = $textPercent;
        return $arrPreparedData;
    }


    public static function getOrderTask($task) {
        $task = strip_tags($task);
        $task = stripcslashes($task);
        $task = preg_replace("/&nbsp;/", "", $task);
        if (strlen($task) >= 20) {
            $task = self::getCutSentence($task, 45);
        }
        return $task;
    }


    public static function isVerdictCancelRequest($cancelRequests) {
        foreach ($cancelRequests as $cancelRequest) {
            $creatorId = $cancelRequest->getCreator();
            if ($creatorId == 17) {
                return true;
            }
        }
        return false;
    }


    public static function removeCancelRequest($user, $cancelRequests) {
        if ($cancelRequests[0]->getCreator() == $user->getId() && $cancelRequests != null) {
            $em = self::getContainer()->get('doctrine')->getManager();
            foreach ($cancelRequests as $cancelRequest) {
                $cancelRequest->setIsShow(0);
            }
            $em->flush();
            $order = $cancelRequests[0]->getUserOrder();
            self::sendEmail('removeCancelRequest', $user, $order);
            return 'valid';
        }
        return 'error';
    }


    public static function getBtnRemoveCancelRequest() {
        return '<label class="btn btn-success" for="formCancelRequest_cancel"><span class="icon-cancel-circled-4">&nbsp;Отменить заявку</span></label><button class="hidden" name="formCancelRequest[cancel]" id="formCancelRequest_cancel" type="button">Cancel</button>';
    }


    public static function sendEmail($mode, $user = null, $order = null, $param = null) {
        $container = self::getContainer();
        $mailSender = $container->getParameter('mail_sender');
        $mailTitle = $container->getParameter('mail_title');
        $userEmail = $user->getEmail();
        if ($mode == 'createCancelRequest') {
            $subject = 'Создана заявка на отмену заказа номер ' . $order->getNum();
            $template = MailTemplate::getCreateCancelRequest($order, $param);
        } elseif ($mode == 'removeCancelRequest') {
            $subject = 'Отменен арбитраж по заказу номер ' . $order->getNum();
            $template = MailTemplate::getRemoveCancelRequest($order, $param);
        }
        $message = \Swift_Message::newInstance()
            ->setSubject($subject)
            ->setFrom($mailSender, $mailTitle)
            //->setTo($userEmail)
            ->setTo('egorduk@yandex.ru')
            ->setBody(
                '<html>' .
                '<head></head>' .
                '<body>' .
                $template .
                '</body>' .
                '</html>',
                'text/html'
            );
            //->setBody($this->renderView('HelloBundle:Hello:email', array('name' => $name)));
        $container->get('mailer')->send($message);
    }


    public static function createPdfOrder($order) {
        //$pdfObj = self::getContainer()->get("white_october.tcpdf")->create();
        $num = $order->getNum();
        $theme = $order->getTheme();
        $user = $order->getUser();
        $userAvatar = self::getUserAvatarForPdf($user);
        //var_dump($userAvatar);die;
        $pdfObj = self::getContainer()->get('slik_dompdf');
        $html =
            '<html><meta http-equiv="content-type" content="text/html; charset=utf-8" /><body>'.
            '<h1>Заказ № ' . $num . '</h1>' .
            '<p>Тема - ' . $theme . '</p>' .
            '<p>Заказчик ' . $userAvatar . '<span>' . $user->getLogin() . '</span</p>' .
            '</body></html>';
        $pdfObj->getpdf($html);
        $canvas = $pdfObj->getCanvas();
        $w = $canvas->get_width();
        $h = $canvas->get_height();
        $font = \Font_Metrics::get_font("helvetica", "normal");
        $txtHeight = \Font_Metrics::get_font_height($font, 8);
        $y = $h - 2 * $txtHeight - 24;
        $color = array(0, 0, 0);
        $canvas->line(16, $y, $w - 16, $y, $color, 1);
        $canvas->page_text(16, $y, "Page: {PAGE_NUM} of {PAGE_COUNT}", $font, 8, $color);
        $text = "Dompdf is awesome";
        $width = \Font_Metrics::get_text_width($text, $font, 8);
        $canvas->text($w - $width - 16, $y, $text, $font, 8);
        $pdf = $pdfObj->output();
        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/study/web/uploads/pdf/' . $num;
        if (!is_dir($basePath)) {
            mkdir($basePath);
        }
        $filePath = $basePath .'/order_' . $num . '.pdf';
        file_put_contents($filePath, $pdf);
        $filePath = '/study/web/uploads/pdf/2/order_' . $num . '.pdf';
        return $filePath;
    }


    public static function getFileUrl($filename, $orderNum) {
        return self::getContainer()->get('router')->generate('secure_author_download_file', array('type' => 'attachments', 'num' => $orderNum, 'filename' => $filename));
    }


    public static function getClientTotalTypeOrdersForChart($arrayTypeOrders) {
        if ($countTypeOrders = count($arrayTypeOrders)) {
            $resultArray = [];
            foreach($arrayTypeOrders as $typeOrder) {
                if ($typeOrder == 'kontr') {
                    $resultArray['kontr']++;
                } elseif ($typeOrder == 'kurs') {
                    $resultArray['kurs']++;
                } elseif ($typeOrder == 'diplom') {
                    $resultArray['diplom']++;
                } else {
                    $resultArray['other']++;
                }
            }
            return $resultArray;
        }
        return null;
    }


    public static function createMoneyOutput($user, $postData) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $outputSum = $postData['fieldSum'];
        $userPsId = $postData['fieldType'];
        $comment = $postData['fieldComment'];
        $outputSum = str_replace(' ', '', $outputSum);
        if ($user->getAccount() < $outputSum) {
            return false;
        }
        if (is_numeric($userPsId) && $userPsId > 0) {
            $userPs = $em->getRepository(self::$_tableUserPs)
                ->findOneBy(array('user' => $user, 'id' => $userPsId));
            if ($userPs) {
                $moneyOutput = new MoneyOutput();
                $moneyOutput->setUserPs($userPs);
                $moneyOutput->setSum($outputSum);
                $moneyOutput->setComment($comment);
                $em->persist($moneyOutput);
                $em->flush();
                return true;
            }
            return false;
        }
    }


    public static function getMaxMinOrderBids($order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $query = $em->createQuery("SELECT MAX(ub.sum) AS max_sum, MIN(ub.sum) AS min_sum, COUNT(uo.id) AS cnt FROM AcmeSecureBundle:UserOrder AS uo
                    JOIN AcmeSecureBundle:UserBid AS ub WITH ub.user_order = uo
                    JOIN AcmeSecureBundle:StatusOrder AS so WITH uo.status_order = so
                    WHERE uo.is_show_author = 1
                    AND uo.id = :orderId
                    AND uo.is_show_client = 1
                    AND ub.is_show_author = 1
                    AND ub.is_show_client = 1
                    AND so.code IN(:statusCode)
                    GROUP BY uo.id");
        $query->setParameter('orderId', $order->getId());
        $query->setParameter('statusCode', array_values(array('sa', 'ca')));
        $bids = $query->getResult();
        if (!count($bids)) {
            $bids[0]['cnt'] = 0;
            $bids[0]['max_sum'] = 0;
            $bids[0]['min_sum'] = 0;
        }
        return $bids;
    }


    public static function getAuthorByOrder($order) {
        $em = self::getContainer()->get('doctrine')->getManager();
        $author = $em->getRepository(self::$_tableUserBid)->createQueryBuilder('ub')
            ->innerJoin('ub.user_order', 'uo')
            ->andWhere('uo = :order')
            ->andWhere('uo.is_show_client = 1')
            ->andWhere('uo.is_show_author = 1')
            ->andWhere('ub.is_show_author = 1')
            ->andWhere('ub.is_show_client = 1')
            ->setParameter('order', $order)
            ->getQuery()
            ->getResult();
        return $author;
    }
}
