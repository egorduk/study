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


    public static function getCountOrdersForGrid($mode = null, $sField = null, $sData = null, $user) {
        //if ($mode != null)
        {
            $table = null;

            /*if ($sField == 'price') {
                $table = 'author_has_estimated_order';
            }
            else if ($sField == 'specialty_id') {
                $table = 'specialty';
            }
            else if ($sField == 'type_id') {
                $table = 'type';
            }

            if ($mode == 'eq') {
                if ($sField == 'price' || $sField == 'specialty_id' || $sField == 'type_id') {
                    $row = $this->_db->query("SELECT COUNT(id) AS count FROM $table WHERE '$sField' = '$sData'");
                    $row = $row->fetch(PDO::FETCH_ASSOC);

                    return $row['count'];
                }
                else {
                    $qWhere = ' WHERE ' . $sField . '=' . $this->_db->quote($sData);
                }
            }
            else if ($mode == 'ne') {
                if ($sField == 'price' || $sField == 'specialty_id' || $sField == 'type_id') {
                    $row = $this->_db->query("SELECT COUNT(id) AS count FROM $table WHERE '$sField' <> '$sData'");
                    $row = $row->fetch(PDO::FETCH_ASSOC);

                    return $row['count'];
                }
                else {
                    $qWhere = ' WHERE ' . $sField . '<>' . $this->_db->quote($sData);
                }
            }
            else if ($mode == 'cn') {
                if ($sField == 'price' || $sField == 'specialty_id' || $sField == 'type_id') {
                    $row = $this->_db->query("SELECT COUNT(id) AS count FROM $table WHERE '$sField' LIKE '%" . $sData . "%'");
                    $row = $row->fetch(PDO::FETCH_ASSOC);

                    return $row['count'];
                }
                else {
                    $qWhere = ' WHERE ' . $sField . ' LIKE ' . $this->_db->quote('%' . $sData . '%');
                }
            }
            else if ($mode == 'bw') {
                if ($sField == 'price' || $sField == 'specialty_id' || $sField == 'type_id') {
                    $row = $this->_db->query("SELECT COUNT(id) AS count FROM $table WHERE '$sField' LIKE '" . $sData . "%'");
                    $row = $row->fetch(PDO::FETCH_ASSOC);

                    return $row['count'];
                }
                else
                {
                    $qWhere = ' WHERE ' . $sField . ' LIKE ' . $this->_db->quote($sData . '%');
                }
            }
        }
        else {
            $qWhere = '';
        }*/

        //$row = $this->_db->query('SELECT COUNT(id) AS count FROM `order`'.$qWhere);
        //$row = $row->fetch(PDO::FETCH_ASSOC);

            $em = self::getContainer()->get('doctrine')->getManager();

            $order = $em->getRepository(self::$_tableUserOrder)
                ->findByUser($user);

            return count($order);
        }
    }


    public static function getClientOrdersForGrid($mode = null, $sField = null, $sData = null, $firstRowIndex, $rowsPerPage, $user) {
        //if ($sField != null && $mode != null)
        {
            /*if ($sField == 'date_create' || $sField == 'date_expire') {
                $sTable = 'datetime';
                $where = $sTable . '.' . $sField;
            }
            else if ($sField == 'specialty_id') {
                $sTable = 'specialty';
                $where = $sTable . '.name';
            }
            else if ($sField == 'num') {
                $sTable = 'order';
                $where = $sTable . '.' . $sField;
            }
            else if ($sField == 'name_theme') {
                $sTable = 'order';
                $where = $sTable . '.' . $sField;
            }
            else if ($sField == 'type_id') {
                $sTable = 'type';
                $where = $sTable . '.name';
            }
            else if ($sField == 'price') {
                $sTable = 'author_has_estimated_order';
                $where = $sTable . '.price';
            }

            if ($mode == 'cn') {
                $row = $this->_db->query("SELECT order.id,order.name_theme,order.num,datetime.date_create,datetime.date_expire,type.name AS tname,specialty.name AS sname,author_has_estimated_order.price
					FROM `order`
					INNER JOIN `datetime` ON datetime.id = order.datetime_id
					INNER JOIN `type` ON type.id = order.type_id
					INNER JOIN specialty ON specialty.id = order.specialty_id
					LEFT JOIN author_has_estimated_order ON author_has_estimated_order.order_id = order.id AND author_has_estimated_order.author_est_id = '$authorId'
					WHERE $where LIKE '%" . $sData . "%'
					LIMIT $limit");
            }
            else if ($mode == 'bw')
            {
                $row = $this->_db->query("SELECT order.id,order.name_theme,order.num,datetime.date_create,datetime.date_expire,type.name AS tname,specialty.name AS sname,author_has_estimated_order.price
					FROM `order`
					INNER JOIN `datetime` ON datetime.id = order.datetime_id
					INNER JOIN `type` ON type.id = order.type_id
					INNER JOIN specialty ON specialty.id = order.specialty_id
					LEFT JOIN author_has_estimated_order ON author_has_estimated_order.order_id = order.id AND author_has_estimated_order.author_est_id = '$authorId'
					WHERE $where LIKE '" . $sData . "%'
					LIMIT $limit");
            }
            else if ($mode == 'eq')
            {
                $row = $this->_db->query("SELECT order.id,order.name_theme,order.num,datetime.date_create,datetime.date_expire,type.name AS tname,specialty.name AS sname,author_has_estimated_order.price
					FROM `order`
					INNER JOIN `datetime` ON datetime.id = order.datetime_id
					INNER JOIN `type` ON type.id = order.type_id
					INNER JOIN specialty ON specialty.id = order.specialty_id
					LEFT JOIN author_has_estimated_order ON author_has_estimated_order.order_id = order.id AND author_has_estimated_order.author_est_id = '$authorId'
					WHERE $where = '$sData'
					LIMIT $limit");
            }
            else if ($mode == 'ne')
            {
                $row = $this->_db->query("SELECT order.id,order.name_theme,order.num,datetime.date_create,datetime.date_expire,type.name AS tname,specialty.name AS sname,author_has_estimated_order.price
					FROM `order`
					INNER JOIN `datetime` ON datetime.id = order.datetime_id
					INNER JOIN `type` ON type.id = order.type_id
					INNER JOIN specialty ON specialty.id = order.specialty_id
					LEFT JOIN author_has_estimated_order ON author_has_estimated_order.order_id = order.id AND author_has_estimated_order.author_est_id = '$authorId'
					WHERE $where <> '$sData'
					LIMIT $limit");
            }
        }
        else*/
        {
            /*$row = $this->_db->query("SELECT o.id,o.name_theme,o.num,d.date_create,d.date_expire,t.name AS tname,s.name AS sname,aheo.price
				FROM `order` o
				INNER JOIN `datetime` d ON d.id = o.datetime_id
				INNER JOIN `type` t ON t.id = o.type_id
				INNER JOIN specialty s ON s.id = o.specialty_id
				LEFT JOIN author_has_estimated_order aheo ON aheo.order_id = o.id AND aheo.author_est_id = '$authorId'
				ORDER BY '.$sortingField . ' ' . $sortingOrder.'
				LIMIT $limit");*/

            $em = self::getContainer()->get('doctrine')->getManager();
            $orders = $em->getRepository(self::$_tableUserOrder)
                ->findBy(
                    array('user' => $user),
                    array('num' => 'ASC'),
                    10,
                    $rowsPerPage
                );

            //var_dump($em); die;
        }

            return $orders;
        }
    }

}
