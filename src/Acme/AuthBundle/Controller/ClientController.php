<?php

namespace Acme\AuthBundle\Controller;

use Acme\AuthBundle\Entity\Client;
use Acme\AuthBundle\Entity\Country;
use Acme\AuthBundle\Entity\Openid;
use Acme\AuthBundle\Entity\Provider;
use Acme\AuthBundle\Entity\User;
use Acme\AuthBundle\Entity\ClientFormValidate;
use Acme\AuthBundle\Form\Client\LoginForm;
use Acme\AuthBundle\Form\Client\RecoveryForm;
use Acme\AuthBundle\Form\Client\RegForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\ExpressionLanguage\Parser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
//use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Bundle\FrameworkBundle\Test;
use Helper\Helper;
use Symfony\Component\Security\Core\Util\SecureRandom;

require_once '..\src\Acme\AuthBundle\Lib\recaptchalib.php';


class ClientController extends Controller
{
    private $tableUser = 'AcmeAuthBundle:User';
    private $tableCountry = 'AcmeAuthBundle:Country';
    private $tableProvider = 'AcmeAuthBundle:Provider';

    /**
     * @Template()
     * @return Response
     */
    public function indexAction(Request $request)
    {
        /*if (false === $this->container->get('security.context')->isGranted('IS_AUTHENTICATED_REMEMBERED')) {
            //throw new AccessDeniedException();
            throw new AccessException();
        }*/

        //print_r(geoip_country_code_by_name('www.tut.by'));

        $client = new ClientFormValidate();
        $formLogin = $this->createForm(new LoginForm(), $client);
        $formLogin->handleRequest($request);

        $socialToken = $request->request->get('token');

        if (isset($socialToken) && $socialToken != null)
        {
            $session = $request->getSession();
            $session->set('socialToken', $socialToken);
            $session->save();

            return new RedirectResponse($this->generateUrl('client_openid_auth'));
        }

        if ($request->isMethod('POST'))
        {
            if ($formLogin->get('enter')->isClicked())
            {
                if ($formLogin->isValid())
                {
                    $postData = $request->request->get('formLogin');
                    $userLogin = $postData['fieldLogin'];
                    $userPassword = $postData['fieldPass'];

                    $user = $this->getDoctrine()->getRepository($this->tableUser)
                       // ->findOneBy(array('login' => $userLogin, 'password' => $userPassword))
                          ->findOneByLogin($userLogin);

                    if (!$user)
                    {
                        return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный логин или пароль!');
                    }
                    else
                    {
                        $encodedPassword = Helper::getRegPassword($userPassword, $user->getSalt());

                        if(!StringUtils::equals($encodedPassword, $user->getPassword()))
                        {
                            return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный логин или пароль!');
                        }
                        else
                        {
                           // $session = $request->getSession();
                            $firewall = 'secured_area';
                            $token = new UsernamePasswordToken($userLogin, null, $firewall, array('ROLE_CLIENT'));
                            //$session->
                            //$session->set('_security_'.$firewall, serialize($token));
                            //$session->set('TEST', serialize(123));
                            $this->get('security.context')->setToken($token);
                            //$session->save();

                            return new RedirectResponse($this->generateUrl('secure_client_index'));
                        }
                    }
                }
                else
                {
                    return array('formLogin' => $formLogin->createView(), 'errorData' => '');
                }
            }
        }

        return array('formLogin' => $formLogin->createView(), 'errorData' => '');
    }

    /**
     * @Template()
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $session = $request->getSession();

        /*if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR)) {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        } else {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
        }*/

        //$this->get('security.context')->getToken()->getUser();
        //$this->get('security.context')->getToken()->getRoles();

        /*if (false === $this->get('security.context')->isGranted('ROLE_ADMIN'))
        {
            throw new AccessDeniedException();
        }*/

        //print_r($session->get('_security_secured_area'));
        print_r($session);
        //print_r($this->get('security.context')->getToken());

        return array('test' => '');

        /*$q = $this
            ->createQueryBuilder('u')
            ->where('u.username = :username OR u.email = :email')
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->getQuery();

        try {
            // The Query::getSingleResult() method throws an exception
            // if there is no record matching the criteria.
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin AcmeUserBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 0, $e);
        }

        return $user;*/
    }

    /**
     * @Template()
     * @return Response
     */
    public function logoutAction(Request $request)
    {
        $session = $request->getSession();
        $session->clear();

        return array();
    }

    /**
     * @Template()
     * @return Response
     */
    public function rulesAction()
    {
        return array();
    }

    /**
     * @Template()
     * @return array
     */
    public function regAction(Request $request)
    {
        $clientValidate = new ClientFormValidate();
        $formReg = $this->createForm(new RegForm(), $clientValidate);
        $formReg->handleRequest($request);

        $publicKeyRecaptcha = $this->container->getParameter('publicKeyRecaptcha');
        $captcha = recaptcha_get_html($publicKeyRecaptcha);

        if ($request->isMethod('POST'))
        {
            if ($formReg->get('reg')->isClicked())
            {
                $privateKeyRecaptcha = $this->container->getParameter('privateKeyRecaptcha');
                $resp = recaptcha_check_answer($privateKeyRecaptcha, $_SERVER["REMOTE_ADDR"], $request->request->get('recaptcha_challenge_field'), $request->request->get('recaptcha_response_field'));

                if ($formReg->isValid())
                {
                    if ($resp->is_valid)
                    {
                        $postData = $request->request->get('formReg');
                        $userLogin = $postData['fieldLogin'];
                        $userPassword = $postData['fieldPass'];
                        $userEmail = $postData['fieldEmail'];

                        $user = new User();
                        $user->setLogin($userLogin);
                        $user->setEmail($userEmail);

                        $salt = Helper::getSalt();
                        $password = Helper::getRegPassword($userPassword, $salt);
                        $user->setPassword($password);
                        $user->setSalt($salt);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();

                        return $this->redirect($this->generateUrl('client_index'));
                    }
                    else
                    {
                        return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error);
                    }
                }
            }
        }

        return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '');
    }


    /**
     * @Template()
     * @return array
     */
    public function unauthorizedAction(Request $request)
    {
        return array();
    }


    /**
     * @Template()
     * @return array
     */
    public function openidAction(Request $request)
    {
        $session = $request->getSession();

        if($session->has('socialToken'))
        {
            $socialToken = $session->get('socialToken');
            $socialResponse = file_get_contents('http://ulogin.ru/token.php?token=' . $socialToken . '&host=' . $_SERVER['HTTP_HOST']);
            $socialData = json_decode($socialResponse, true);
            print_r($socialData);

            if (!isset($socialData['error']))
            {
                $clientValidate = new ClientFormValidate();
                $clientValidate->setLogin($socialData['nickname']);
                $clientValidate->setEmail($socialData['email']);

                $formReg = $this->createForm(new RegForm(), $clientValidate);
                $formReg->handleRequest($request);

                $publicKeyRecaptcha = $this->container->getParameter('publicKeyRecaptcha');
                $captcha = recaptcha_get_html($publicKeyRecaptcha);

                if ($request->isMethod('POST'))
                {
                    if ($formReg->get('reg')->isClicked())
                    {
                        $privateKeyRecaptcha = $this->container->getParameter('privateKeyRecaptcha');
                        $resp = recaptcha_check_answer($privateKeyRecaptcha, $_SERVER["REMOTE_ADDR"], $request->request->get('recaptcha_challenge_field'), $request->request->get('recaptcha_response_field'));

                        if ($resp->is_valid)
                        {
                            if ($formReg->isValid())
                            {
                                $session->remove('socialToken');
                                $postData = $request->request->get('formReg');
                                $userLogin = $postData['fieldLogin'];
                                $userPassword = $postData['fieldPass'];
                                $userEmail = $postData['fieldEmail'];

                                $user = new User();
                                $user->setLogin($userLogin);
                                $user->setEmail($userEmail);

                                $salt = Helper::getSalt();
                                $password = Helper::getRegPassword($userPassword, $salt);
                                $user->setPassword($password);
                                $user->setSalt($salt);

                                $providerName = $socialData['network'];
                                //$countryCode = geoip_country_code_by_name($_SERVER["REMOTE_ADDR"]);
                                $countryCode = 'BY';

                                $provider = $this->getDoctrine()->getRepository($this->tableProvider)
                                    ->findOneByName($providerName);

                                $country = $this->getDoctrine()->getRepository($this->tableCountry)
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

                                $em = $this->getDoctrine()->getManager();
                                $em->persist($openId);
                                $em->persist($user);
                                $em->flush();

                                return $this->redirect($this->generateUrl('client_index'));
                            }
                        }
                        else
                        {
                            return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error);
                        }
                    }
                }

                return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '');
            }
            else
            {
                return $this->redirect($this->generateUrl('client_index'));
            }
        }
        else
        {
            return $this->redirect($this->generateUrl('client_index'));
        }
    }


    /**
     * @Template()
     * @return array
     */
    public function recoveryAction(Request $request)
    {
        //$client = new ClientFormValidate();
        $formRecovery = $this->createForm(new RecoveryForm());
        $formRecovery->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($formRecovery->get('recovery')->isClicked())
            {
                if ($formRecovery->isValid())
                {
                    $postData = $request->request->get('formRecovery');
                    $userEmail = $postData['fieldEmail'];

                    $salt = Helper::getSalt();
                    $genPassword = Helper::getRandomValue(3);
                    $recoveryPassword = Helper::getRegPassword($genPassword, $salt);
                    //print_r($recoveryPassword); die;

                    $mailer = $this->get('mailer');
                    $message = \Swift_Message::newInstance()
                        ->setSubject('Восстановление пароля в системе ...')
                        ->setFrom('egorduk91@gmail.com')
                        ->setTo('gzhelka777@mail.ru')
                        //->setTo($userEmail)
                        ->setBody('Ваш новый пароль для входа в ...' . $genPassword)
                        //->setBody($this->renderView('HelloBundle:Hello:email', array('name' => $name)))
                    ;
                    $mailer->send($message);

                    /*$user = $this->getDoctrine()->getRepository($this->tableUser)
                        // ->findOneBy(array('login' => $userLogin, 'password' => $userPassword))
                        ->findOneByLogin($userLogin);

                    if (!$user)
                    {
                        return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный логин или пароль!');
                    }
                    else
                    {
                        $encodedPassword = Helper::getRegPassword($userPassword, $user->getSalt());

                        if(!StringUtils::equals($encodedPassword, $user->getPassword()))
                        {
                            return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный логин или пароль!');
                        }
                        else
                        {
                            // $session = $request->getSession();
                            $firewall = 'secured_area';
                            $token = new UsernamePasswordToken($userLogin, null, $firewall, array('ROLE_CLIENT'));
                            //$session->
                            //$session->set('_security_'.$firewall, serialize($token));
                            //$session->set('TEST', serialize(123));
                            $this->get('security.context')->setToken($token);
                            //$session->save();

                            return new RedirectResponse($this->generateUrl('secure_client_index'));
                        }
                    }*/
                }
                else
                {
                    return array('formRecovery' => $formRecovery->createView());
                }
            }
        }

        return array('formRecovery' => $formRecovery->createView());
    }



}
