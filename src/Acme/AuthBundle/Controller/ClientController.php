<?php

namespace Acme\AuthBundle\Controller;

use Acme\AuthBundle\Entity\Client;
use Acme\AuthBundle\Entity\User;
use Acme\AuthBundle\Entity\ClientFormValidate;
use Acme\AuthBundle\Form\Client\LoginForm;
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
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;
use Symfony\Component\Security\Core\Util\SecureRandom;
use Symfony\Component\Security\Core\Util\StringUtils;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
//use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
//use Symfony\Bundle\FrameworkBundle\Test;
use Helper\Helper;
use Symfony\Component\Validator\Constraints\Date;

require_once '..\src\Acme\AuthBundle\Lib\recaptchalib.php';


class ClientController extends Controller
{
    private $tableSource = 'AcmeRssBundle:Source';
    private $tableNews = 'AcmeRssBundle:News';
    private $tableUser = 'AcmeAuthBundle:User';

    /**
     * @Template()
     * @return Response
     */
    public function indexAction(Request $request)
    {
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
                        return array('formLogin' => $formLogin->createView(), 'errorData' => '?????? ???????????? ????? ??? ??????!');
                    }
                    else
                    {
                        $parsedYml = Helper::readEncodersParam($this->container);
                        $encoder = new MessageDigestPasswordEncoder($parsedYml['algorithm'], $parsedYml['baseAs64'], $parsedYml['iterations']);
                        $encodedPassword = $encoder->encodePassword($userPassword, $user->getSalt());

                        /*$validPassword = $encoder->isPasswordValid(
                            $user->getPassword(),
                            $password,
                            $user->getSalt());*/

                        if(!StringUtils::equals($encodedPassword, $user->getPassword()))
                        {
                            return array('formLogin' => $formLogin->createView(), 'errorData' => '?????? ???????????? ????? ??? ??????!');
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

                            return new RedirectResponse($this->generateUrl('client_login'));
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

        $sessionCreated = $session->getMetadataBag()->getCreated();
        $sessionLifeTime = $session->getMetadataBag()->getLifetime();
        $whenLogin = Helper::getDateFromTimestamp($sessionCreated, "d/m/Y H:i:s");
        echo "������� � ������� � " . $whenLogin;
        echo "</br>";
        $sessionRemaining = $sessionCreated + $sessionLifeTime;
        $a = strtotime("now");
        //$sessionRemaining = Helper::getDateFromTimestamp($a, "d/m/Y H:i:s");
        $r = $sessionRemaining - $a;
        $sessionRemaining = Helper::getDateFromTimestamp($r, "i:s");
        echo "�������� " . $sessionRemaining;
        echo "</br>";

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
                        $user->setRole(2);

                        $parsedYml = Helper::readEncodersParam($this->container);
                        $encoder = new MessageDigestPasswordEncoder($parsedYml['algorithm'], $parsedYml['baseAs64'], $parsedYml['iterations']);
                        $generator = new SecureRandom();
                        $salt = bin2hex($generator->nextBytes(32));
                        $password = $encoder->encodePassword($userPassword, $salt);
                        $user->setPassword($password);
                        $user->setSalt($salt);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();

                        return $this->redirect($this->generateUrl('client_index'));
                    }
                    else
                    {
                        return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error, 'approvePassError' => '');
                    }
                }
            }
        }

        return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '', 'approvePassError' => '');
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
        $socialToken = $session->get('socialToken');
        $socialResponse = file_get_contents('http://ulogin.ru/token.php?token=' . $socialToken . '&host=' . $_SERVER['HTTP_HOST']);
        $socialData = json_decode($socialResponse, true);
        //$session->remove('socialToken');

        if (!isset($socialData['error']))
        {
            print_r($socialData);
            
        }

        return array();
    }

    /**
     * @Template()
     * A function for adding a new source
     * @param Request $request the data of client's request
     * @return array|RedirectResponse
     */
    public function addAction(Request $request)
    {
        $formAdd = $this->createForm(new AddForm());
        $formAdd->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($formAdd->get('Add')->isClicked()) //Add new source
            {
                $postData = $request->request->get('formAdd');
                $newName = $postData['fieldName'];
                $newUrl = $postData['fieldUrl'];

                $source = new Source();
                $source->setName($newName);
                $source->setUrl($newUrl);
                $source->setActive(0);

                $em = $this->getDoctrine()->getManager();
                $em->persist($source);
                $em->flush();

                return new RedirectResponse($this->generateUrl('setting_view'));
            }
        }

        return array('formAdd' => $formAdd->createView());
    }


    /**
     * @Template()
     * A function for editing a selected source by user
     * @param Request $request the data of client's request
     * @return array|RedirectResponse
     */
    public function editAction(Request $request)
    {
        if ($request->isMethod('POST'))
        {
            $editId = $request->request->get('sourceId');

            $em = $this->getDoctrine()->getManager();

            if (isset($editId))
            {
                $source = $em->getRepository($this->tableSource)
                    ->find($editId);

                $element['name'] = $source->getName();
                $element['url'] = $source->getUrl();
                $element['sourceId'] = $editId;

                $formEdit = $this->createForm(new EditForm(), $element);

                return array('formEdit' => $formEdit->createView());
            }
            else
            {
                $postData = $request->request->get('formEdit');
                $editName = $postData['fieldName'];
                $editUrl = $postData['fieldUrl'];
                $editId = $postData['fieldSourceId'];

                $source = $em->getRepository($this->tableSource)
                    ->find($editId);
                $source->setName($editName);
                $source->setUrl($editUrl);
                $source->setActive(0);

                $em->persist($source);
                $em->flush();

                return new RedirectResponse($this->generateUrl('setting_view'));
            }
        }
    }


    /**
     * @Template()
     * A function for viewing all sources and controlled them
     * @param Request $request the data of client's request
     * @return array|RedirectResponse
     */
    public function viewAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        if($request->isXmlHttpRequest())
        {
            $arrDeleteInd = (array)json_decode($request->request->get('arrDeleteInd'));

            if (count($arrDeleteInd))
            {
                foreach($arrDeleteInd as $deleteId)
                {
                    $source = $em->getRepository($this->tableSource)
                        ->find($deleteId);

                    $em->remove($source);
                }

                $em->flush();

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $arrSaveInd = (array)json_decode($request->request->get('arrSaveInd'));

            if (count($arrSaveInd) && ($arrSaveInd[0] != -1))
            {
                $sources = $em->getRepository($this->tableSource)
                    ->findAll();

                foreach($sources as $source)
                {
                    $source->setActive(0);
                    $em->persist($source);
                }

                $em->flush();

                foreach($arrSaveInd as $saveId)
                {
                    foreach($sources as $source)
                    {
                        if ($source->getId() == $saveId)
                        {
                            $source->setActive(1);
                            $em->persist($source);

                            break;
                        }
                    }
                }

                $em->flush();

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }
            else if (count($arrSaveInd) && ($arrSaveInd[0] == -1))
            {
                $sources = $em->getRepository($this->tableSource)
                    ->findAll();

                foreach($sources as $source)
                {
                    $source->setActive(0);
                    $em->persist($source);
                }

                $em->flush();

                $response = new Response();
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

            $loadActive = $request->request->get('loadActive');

            if (isset($loadActive))
            {
                $sources = $em->getRepository($this->tableSource)
                    ->findByActive(1);

                $arrLoadActive = array();

                foreach($sources as $source)
                {
                    $arrLoadActive[] = $source->getId();
                }

                $response = new Response(json_encode(array('arrLoadActive' => $arrLoadActive)));
                $response->headers->set('Content-Type', 'application/json');
                return $response;
            }

        }

        $formView = $this->createForm(new ViewForm());
        $formView->handleRequest($request);

        $sources = $em->getRepository($this->tableSource)
            ->findAll();

        return array('count' => count($sources), 'sources' => $sources, 'formView' => $formView->createView());
    }

}
