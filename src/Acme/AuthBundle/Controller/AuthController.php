<?php

namespace Acme\AuthBundle\Controller;

use Acme\AuthBundle\Entity\Country;
use Acme\AuthBundle\Entity\Openid;
use Acme\AuthBundle\Entity\User;
use Acme\AuthBundle\Entity\Provider;
use Acme\AuthBundle\Entity\ClientRegFormValidate;
use Acme\AuthBundle\Entity\AuthorRegFormValidate;
use Acme\AuthBundle\Entity\LoginFormValidate;
use Acme\AuthBundle\Form\LoginForm;
use Acme\AuthBundle\Form\RecoveryForm;
use Acme\AuthBundle\Entity\UserInfo;
use Acme\AuthBundle\Form\ClientRegForm;
use Acme\AuthBundle\Form\AuthorRegForm;
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
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Helper\Helper;
//use Symfony\Component\Security\Core\Util\SecureRandom;

require_once '..\src\Acme\AuthBundle\Lib\recaptchalib.php';


class AuthController extends Controller
{
    //private $tableUser = 'AcmeAuthBundle:User';
    private $tableCountry = 'AcmeAuthBundle:Country';
    //private $tableProvider = 'AcmeAuthBundle:Provider';



    /**
     * @Template()
     * @return Response
     */
    public function loginAction(Request $request)
    {
        $loginValidate = new LoginFormValidate();
        $formLogin = $this->createForm(new LoginForm(), $loginValidate);
        $formLogin->handleRequest($request);

        $socialToken = $request->request->get('token');

        if (isset($socialToken) && $socialToken != null)
        {
            $session = $request->getSession();
            $session->set('socialToken', $socialToken);
            $session->save();

            return new RedirectResponse($this->generateUrl('openid_auth'));
        }

        if ($request->isMethod('POST'))
        {
            if ($formLogin->get('enter')->isClicked())
            {
                if ($formLogin->isValid())
                {
                    $postData = $request->request->get('formLogin');
                    $userEmail = $postData['fieldEmail'];
                    $userPassword = $postData['fieldPass'];

                    $user = Helper::getUserByEmailAndIsConfirm($userEmail);

                    if (!$user)
                    {
                        return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный Email или пароль!');
                    }
                    else
                    {
                        $encodedPassword = Helper::getRegPassword($userPassword, $user->getSalt());

                        if (!StringUtils::equals($encodedPassword, $user->getPassword()))
                        {
                            return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный Email или пароль!');
                        }
                        else
                        {
                            $firewall = 'secured_area';
                            $role = $user->getRole();

                            if ($role == 1)
                            {
                                $role = 'ROLE_AUTHOR';
                            }
                            else
                            {
                                $role = 'ROLE_CLIENT';
                            }

                            $token = new UsernamePasswordToken($user->getId(), null, $firewall, array($role));
                            $this->get('security.context')->setToken($token);

                            if ($role == 'ROLE_AUTHOR')
                            {
                                return new RedirectResponse($this->generateUrl('secure_author_index'));
                            }
                            else
                            {
                                return new RedirectResponse($this->generateUrl('secure_client_index'));
                            }
                        }
                    }
                }
                /*else
                {
                    return array('formLogin' => $formLogin->createView(), 'errorData' => '');
                }*/
            }
        }

        return array('formLogin' => $formLogin->createView(), 'errorData' => '');
    }


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
     * @return array
     */
    public function regAction(Request $request, $type)
    {
        if ($type == "client")
        {
            $clientValidate = new ClientRegFormValidate();
            $formReg = $this->createForm(new ClientRegForm(), $clientValidate);
            $formReg->handleRequest($request);

            $publicKeyRecaptcha = $this->container->getParameter('publicKeyRecaptcha');
            $captcha = recaptcha_get_html($publicKeyRecaptcha);

            if ($request->isMethod('POST'))
            {
                if ($formReg->get('reg')->isClicked())
                {
                    if ($formReg->isValid())
                    {
                        $privateKeyRecaptcha = $this->container->getParameter('privateKeyRecaptcha');
                        $resp = recaptcha_check_answer($privateKeyRecaptcha, $_SERVER["REMOTE_ADDR"], $request->request->get('recaptcha_challenge_field'), $request->request->get('recaptcha_response_field'));

                        if (!$resp->is_valid)
                        {
                            $postData = $request->request->get('formReg');
                            $userLogin = $postData['fieldLogin'];
                            $userPassword = $postData['fieldPass'];
                            $userEmail = $postData['fieldEmail'];

                            $em = $this->getDoctrine()->getManager();

                            $userInfo = new UserInfo();

                            $em->persist($userInfo);
                            $em->flush();

                            $userInfoId = $userInfo->getId();

                            $user = new User();
                            $user->setLogin($userLogin);
                            $user->setEmail($userEmail);
                            $user->setRole(2);

                            $salt = Helper::getSalt();
                            $password = Helper::getRegPassword($userPassword, $salt);
                            $user->setPassword($password);
                            $user->setSalt($salt);
                            $hashCode = Helper::getRandomValue(15);
                            $user->setHash($hashCode);
                            $user->setUserInfoId($userInfoId);

                            $em->persist($user);
                            $em->flush();

                            $userId = $user->getId();

                            Helper::sendConfirmationReg($this->container, $userEmail, $userId, $hashCode);

                            //return $this->redirect($this->generateUrl('client_success_reg'));
                        }
                        else
                        {
                            //return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error);
                            return $this->render(
                                'AcmeAuthBundle:Client:reg.html.twig', array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error)
                            );
                        }
                    }
                }
            }

            //return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '');
            return $this->render(
                'AcmeAuthBundle:Client:reg.html.twig', array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '')
            );
        }
        elseif ($type == "author")
        {
            $authorValidate = new AuthorRegFormValidate();
            $formReg = $this->createForm(new AuthorRegForm(), $authorValidate);
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
                        if (!$resp->is_valid)
                        {
                            $postData = $request->request->get('formReg');
                            $userLogin = $postData['fieldLogin'];
                            $userPassword = $postData['fieldPass'];
                            $userEmail = $postData['fieldEmail'];
                            $userMobileTel = $postData['fieldMobileTel'];
                            $userSkype = $postData['fieldSkype'];
                            $userIcq = $postData['fieldIcq'];
                            $userCountryCode = $postData['selectorCountry'];

                            $em = $this->getDoctrine()->getManager();

                            $userInfo = new UserInfo();
                            $userInfo->setSkype($userSkype);
                            $userInfo->setIcq($userIcq);
                            $userInfo->setMobileTel($userMobileTel);

                            $country = $this->getDoctrine()->getRepository($this->tableCountry)
                                ->findOneByCode($userCountryCode);

                            $userInfo->setCountry($country);

                            $em->persist($userInfo);
                            $em->flush();

                            $userInfoId = $userInfo->getId();

                            $user = new User();
                            $user->setLogin($userLogin);
                            $user->setEmail($userEmail);
                            $user->setRole(1);

                            $salt = Helper::getSalt();
                            $password = Helper::getRegPassword($userPassword, $salt);
                            $user->setPassword($password);
                            $user->setSalt($salt);
                            $hashCode = Helper::getRandomValue(15);
                            $user->setHash($hashCode);
                            $user->setUserInfoId($userInfoId);

                            $em->persist($user);
                            $em->flush();

                            $userId = $user->getId();

                            //Helper::sendConfirmationReg($this->container, $userEmail, $userId, $hashCode);

                            //return $this->redirect($this->generateUrl('client_success_reg'));
                        }
                        else
                        {
                            return $this->render(
                                'AcmeAuthBundle:Author:reg.html.twig', array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error)
                            );
                        }
                    }
                }
            }

            return $this->render(
                'AcmeAuthBundle:Author:reg.html.twig', array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '')
            );
        }
        else
        {
            throw new AccessException();
        }

    }


    /**
     * Template for denied area
     *
     * @Template()
     * @return array
     */
    public function unauthorizedAction(Request $request)
    {
        return array();
    }


    /**
     * Auth by openID
     *
     * @Template()
     * @return array
     */
    public function openidAction(Request $request)
    {
        $session = $request->getSession();

        if ($session->has('socialToken'))
        {
            $socialToken = $session->get('socialToken');
            $socialResponse = file_get_contents('http://ulogin.ru/token.php?token=' . $socialToken . '&host=' . $_SERVER['HTTP_HOST']);
            $socialData = json_decode($socialResponse, true);
            //print_r($socialData); die;

            if (!isset($socialData['error']))
            {
                $userEmail = $socialData['email'];
                $isExistsUser = Helper::isExistsUserByEmailAndIsConfirm($userEmail);

                if ($isExistsUser)
                {
                    $role = Helper::getUserRoleByEmail($userEmail);

                    if ($role == 1)
                    {
                        $role = 'ROLE_AUTHOR';
                        $pathRedirect = 'secure_author_index';
                    }
                    else
                    {
                        $role = 'ROLE_CLIENT';
                        $pathRedirect = 'secure_client_index';
                    }

                    $token = new UsernamePasswordToken($userEmail, null, 'secured_area', array($role));
                    $this->get('security.context')->setToken($token);

                    return new RedirectResponse($this->generateUrl($pathRedirect));
                }
                else
                {
                    $clientValidate = new ClientRegFormValidate();
                    $clientValidate->setLogin($socialData['nickname']);
                    $clientValidate->setEmail($socialData['email']);

                    $formReg = $this->createForm(new ClientRegForm(), $clientValidate);
                    $formReg->handleRequest($request);

                    $publicKeyRecaptcha = $this->container->getParameter('publicKeyRecaptcha');
                    $captcha = recaptcha_get_html($publicKeyRecaptcha);

                    if ($request->isMethod('POST'))
                    {
                        if ($formReg->get('reg')->isClicked())
                        {
                            if ($formReg->isValid())
                            {
                                $privateKeyRecaptcha = $this->container->getParameter('privateKeyRecaptcha');
                                $resp = recaptcha_check_answer($privateKeyRecaptcha, $_SERVER["REMOTE_ADDR"], $request->request->get('recaptcha_challenge_field'), $request->request->get('recaptcha_response_field'));

                                if (!$resp->is_valid)
                                {
                                    $session->remove('socialToken');
                                    $em = $this->getDoctrine()->getManager();

                                    $postData = $request->request->get('formReg');
                                    $userLogin = $postData['fieldLogin'];
                                    $userPassword = $postData['fieldPass'];
                                    $userEmail = $postData['fieldEmail'];

                                    $userInfo = new UserInfo();
                                    $em->persist($userInfo);
                                    $em->flush();
                                    $userInfoId = $userInfo->getId();

                                    $user = new User();
                                    $user->setLogin($userLogin);
                                    $user->setEmail($userEmail);
                                    $user->setRole(2);

                                    $salt = Helper::getSalt();
                                    $password = Helper::getRegPassword($userPassword, $salt);
                                    $user->setPassword($password);
                                    $user->setSalt($salt);
                                    $hashCode = Helper::getRandomValue(15);
                                    $user->setHash($hashCode);
                                    $user->setUserInfoId($userInfoId);

                                    $providerName = $socialData['network'];
                                    //$countryCode = geoip_country_code_by_name($_SERVER["REMOTE_ADDR"]);
                                    $countryCode = 'by';

                                    $openId = Helper::addNewOpenIdData($socialData, $providerName, $countryCode);

                                    $user->setOpenId($openId);

                                    $em->persist($user);
                                    $em->flush();
                                    $userId = $user->getId();

                                    Helper::sendConfirmationReg($this->container, $userEmail, $userId, $hashCode);

                                    return $this->render(
                                        'AcmeAuthBundle:Auth:successReg.html.twig', array('userLogin' => $userLogin)
                                    );
                                }
                                else
                                {
                                    return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error);
                                }
                            }
                        }
                    }
                }

                return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '');
            }
        }

        return $this->redirect($this->generateUrl('login'));
    }


    /**
     * @Template()
     * @return array
     */
    public function recoveryAction(Request $request)
    {
        $formRecovery = $this->createForm(new RecoveryForm());
        $clonedFormRecovery = clone $formRecovery;
        $formRecovery->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($formRecovery->get('recovery')->isClicked())
            {
                if ($formRecovery->isValid())
                {
                    $postData = $request->request->get('formRecovery');
                    $userEmail = $postData['fieldEmail'];
                    $user = Helper::getUserByEmail($userEmail);

                    if ($user)
                    {
                        $userId = $user->getId();
                        $userSalt = $user->getSalt();
                        $unencodePassword = Helper::getRandomValue(3);
                        $encodePassword = Helper::getRegPassword($unencodePassword, $userSalt);
                        $hashCode = Helper::getRandomValue(15);

                        $user->setHash($hashCode);
                        $user->setRecoveryPassword($encodePassword);

                        $em = $this->getDoctrine()->getManager();
                        $em->merge($user);
                        $em->flush();

                        Helper::sendRecoveryPasswordMail($this->container, $userEmail, $userId, $unencodePassword, $hashCode);

                        return array('formRecovery' => $clonedFormRecovery->createView(), 'msgSuccess' => 'Проверьте почту!');
                    }
                    else
                    {
                        return array('formRecovery' => $clonedFormRecovery->createView(), 'msgSuccess' => 'Проверьте почту!');
                    }
                }
                else
                {
                    return array('formRecovery' => $formRecovery->createView(), 'msgSuccess' => '');
                }
            }
        }

        return array('formRecovery' => $formRecovery->createView(), 'msgSuccess' => '');
    }


    /**
     * Confirm register and recovery password by Email
     *
     * @Template()
     * @return array
     */
    public function confirmAction(Request $request)
    {
        $hashCode = $request->get('hash_code');
        $userId = $request->get('id');
        $type = $request->get('type');

        $isCorrectUrl = Helper::isCorrectConfirmUrl($hashCode, $userId, $type);

        if ($isCorrectUrl)
        {
                if ($type == "reg")
                {
                    $isExistsUser = Helper::isExistsUserByHashAndByIdAndIsConfirm($userId, $hashCode, 0);

                    if ($isExistsUser)
                    {
                        $isSuccess = Helper::updateUserAfterConfirmByMail($userId, $hashCode, $type);

                        if ($isSuccess)
                        {
                            return array('msgError' => 'Аккаунт активирован!', 'viewLink' => true);
                        }
                    }

                    return array('msgError' => 'Ошибка подтверждения регистрации!', 'viewLink' => false);
                }
                elseif ($type == "rec")
                {
                    $isExistsUser = Helper::isExistsUserByHashAndByIdAndIsConfirm($userId, $hashCode, 1);

                    if ($isExistsUser)
                    {
                        $isSuccess = Helper::updateUserAfterConfirmByMail($userId, $hashCode, $type);

                        if ($isSuccess)
                        {
                            return array('msgError' => 'Новый пароль активирован!', 'viewLink' => true);
                        }
                    }

                    return array('msgError' => 'Ошибка смены пароля!', 'viewLink' => false);
                }
                else
                {
                    return array('msgError' => 'Ошибка!', 'viewLink' => false);
                }
        }
        else
        {
            return array('msgError' => 'Ошибка!', 'viewLink' => false);
        }
    }



}
