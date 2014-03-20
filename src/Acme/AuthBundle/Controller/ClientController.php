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

        if ($request->isMethod('POST'))
        {
            if ($formLogin->get('enter')->isClicked())
            {
                if ($formLogin->isValid())
                {
                    //return $this->redirect($this->generateUrl(''));
                    $postData = $request->request->get('formLogin');
                    $user = new User();
                    $userLogin = $postData['fieldLogin'];
                    $userPassword = $postData['fieldPass'];
                    //return array('formLogin' => $formLogin->createView());

                    $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
                    /*$generator = new SecureRandom();
                    $salt = bin2hex($generator->nextBytes(32));
                    $password = $encoder->encodePassword($userPassword, $salt);

                    $em = $this->getDoctrine()->getManager();
                    $client = $em->getRepository('AcmeAuthBundle:User')->findBy(
                        array('login' => $userLogin),
                        array('password' => $password)
                    );

                    if (!$client)
                    {
                        throw $this->createNotFoundException
                        (
                            'No client found'
                        );
                    }

                    $em->flush();*/

                    /*$user = ;

                    $encoder = $encoder->getEncoder($user);

                    // will return $weakEncoder (see above)

                    $encodedPassword = $encoder->encodePassword($password, $user->getSalt());

                    // check if the password is valid:

                    $validPassword = $encoder->isPasswordValid(
                        $user->getPassword(),
                        $password,
                        $user->getSalt());

                    return array('formLogin' => $formLogin->createView(), 'test' => $client);*/
                }
                else
                {
                    return array('formLogin' => $formLogin->createView());*/

                    //$em = $this->getDoctrine()->getManager();
                    //$user = $em->getRepository('AcmeAuthBundle:User')
                    //    ->findByLogin($userLogin);

                    $em = $this->getDoctrine()->getEntityManager();
                    $user = $em->getRepository($this->tableUser)
                        ->findOneByLogin($userLogin);

                    if (!$user)
                    {
                        return array('formLogin' => $formLogin->createView(), 'errorData' => 'Введен неправильный логин или пароль!');
                    }
                    else
                    {
                        $userId = $user->getId();


                    }

                    /*$validPassword = $encoder->isPasswordValid(
                        $user->getPassword(),
                        $userPassword,
                        $user->getSalt()
                    );*/

                    /*if ($this->get('request')->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
                    {
                        $error = $this->get('request')->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
                    }
                    else
                    {
                        $error = $this->get('request')->getSession()->get(SecurityContext::AUTHENTICATION_ERROR);
                    }*/

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
        //$user = $this->getUser();

        /*if (false === $this->get('security.context')->isGranted('ROLE_ADMIN')) {
            throw new AccessDeniedException();
        }*/

        /*$session = $request->getSession();

        // get the login error if there is one
        if ($request->attributes->has(SecurityContext::AUTHENTICATION_ERROR))
        {
            $error = $request->attributes->get(SecurityContext::AUTHENTICATION_ERROR);
        }
        else
        {
            $error = $session->get(SecurityContext::AUTHENTICATION_ERROR);
            $session->remove(SecurityContext::AUTHENTICATION_ERROR);
        }

        $user = $this->get('security.context')->getToken()->getUser();
        return array('test' => $session->get(SecurityContext::LAST_USERNAME));*/


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
     * @return array
     */
    public function regAction(Request $request)
    {
        $clientValidate = new ClientFormValidate();
        $formReg = $this->createForm(new RegForm(), $clientValidate);
        $formReg->handleRequest($request);

        $publickey = "6LcYmecSAAAAAJlYNqogbOXJVMvGgim5JoM0hcAi";
        $captcha = recaptcha_get_html($publickey);

        if ($request->isMethod('POST'))
        {
            if ($formReg->get('reg')->isClicked())
            {
                $privatekey = "6LcYmecSAAAAANaq8JFPxT3xEA_urO6st1gfVOXL";
                $resp = recaptcha_check_answer($privatekey, $_SERVER["REMOTE_ADDR"], $request->request->get('recaptcha_challenge_field'), $request->request->get('recaptcha_response_field'));

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

                        $encoder = new MessageDigestPasswordEncoder('sha512', true, 10);
                        $generator = new SecureRandom();
                        $salt = bin2hex($generator->nextBytes(32));
                        $password = $encoder->encodePassword($userPassword, $salt);
                        $user->setPassword($password);
                        $user->setSalt($salt);

                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();

                        //$user->getUserRoles()->add($role);

                        return $this->redirect($this->generateUrl('client_index'));
                    }
                    else
                    {
                        return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => $resp->error, 'approvePassError' => '');
                    }
                }
                /*else
                czxczxczxcsdfsdfsdfgdfggzxczxczxcvxcv
                {
                    return array('formLogin' => $formReg->createView());
                }*/
            }
        }

        return array('formReg' => $formReg->createView(), 'captcha' => $captcha, 'captchaError' => '', 'approvePassError' => '');
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
