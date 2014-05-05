<?php

namespace Acme\SecureBundle\Controller;


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
//use Symfony\Component\PropertyAccess\Exception\AccessException;
//use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\StringUtils;
//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Helper\Helper;
//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Acme\SecureBundle\Form\Client\ClientProfileForm;
use Acme\SecureBundle\Entity\ClientProfileFormValidate;
use Acme\SecureBundle\Form\Client\CreateOrderForm;
use Acme\SecureBundle\Entity\CreateOrderFormValidate;


class ClientController extends Controller
{
    private $_tableUserInfo = 'AcmeAuthBundle:UserInfo';

    /**
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
        //throw new NotFoundHttpException('Sorry not existing!');
        $userId = $this->get('security.context')->getToken()->getUser();
        //$userRole = $this->get('security.context')->getToken()->getRoles();
        $session = $request->getSession();
        $sessionCreated = $session->getMetadataBag()->getCreated();
        $sessionLifeTime = $session->getMetadataBag()->getLifetime();
        //$sessionUpdated = $session->getMetadataBag()->getLastUsed();
        //$whenUpdated = Helper::getDateFromTimestamp($sessionUpdated, "d/m/Y H:i:s");
        $whenLogin = Helper::getDateFromTimestamp($sessionCreated, "d/m/Y H:i:s");
        $sessionRemaining = $sessionCreated + $sessionLifeTime;
        $nowTimestamp = strtotime("now");
        $sessionRemaining = $sessionRemaining - $nowTimestamp;
        $sessionRemaining = Helper::getDateFromTimestamp($sessionRemaining, "i:s");

        $user = Helper::getUserById($userId);

        //print_r($this->get('security.context')->getToken()); //die;

        return array('userId' => $userId, 'userLogin' => $user->getLogin(), 'userRole' => 'Заказчик', 'whenLogin' => $whenLogin, 'remainingTime' => $sessionRemaining);
    }


    /**
     * @Template()
     * @return array
     */
    public function profileAction(Request $request, $type)
    {
        if ($type == "view" || $type == "edit")
        {
            $userId = $this->get('security.context')->getToken()->getUser();
            $user = Helper::getUserById($userId);
            $userInfoId = $user->getUserInfoId();
            $userInfo = Helper::getUserInfoById($userInfoId);
        }

        if ($type == "view")
        {
            return array('formProfile' =>'', 'user' => $user, 'userInfo' => $userInfo, 'showWindow' => false);
        }
        elseif ($type == "edit")
        {
            $userInfo = Helper::getUserInfoById($userInfoId);

            $profileValidate = new ClientProfileFormValidate();
            $profileValidate->setIcq($userInfo->getIcq());
            $profileValidate->setSkype($userInfo->getSkype());
            $profileValidate->setMobilePhone($userInfo->getMobilePhone());
            $profileValidate->setStaticPhone($userInfo->getStaticPhone());
            $profileValidate->setUsername($userInfo->getUsername());
            $profileValidate->setSurname($userInfo->getSurname());
            $profileValidate->setLastname($userInfo->getLastname());
            $profileValidate->setCountry($userInfo->getCountry()->getCode());

            $formProfile = $this->createForm(new ClientProfileForm(), $profileValidate);
            $formProfile->handleRequest($request);

            if ($request->isMethod('POST'))
            {
                if ($formProfile->get('save')->isClicked())
                {
                    if ($formProfile->isValid())
                    {
                        $postData = $request->request->get('formProfile');

                        Helper::updateUserInfo($postData, $userInfo);
                        return array('formProfile' => $formProfile->createView(), 'user' => '', 'userInfo' => $userInfo, 'showWindow' => true);
                    }
                }
            }

            return array('formProfile' => $formProfile->createView(), 'user' => '', 'userInfo' => $userInfo, 'showWindow' => false);
        }
        else
        {

        }
    }


    /**
     * @Template()
     * @return array
     */
    public function settingsAction(Request $request, $type)
    {

    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function orderAction(Request $request, $type)
    {
        if ($type == "view" || $type == "create")
        {
            $userId = $this->get('security.context')->getToken()->getUser();
            $user = Helper::getUserById($userId);
            //$userInfoId = $user->getUserInfoId();
            //$userInfo = Helper::getUserInfoById($userInfoId);
        }

        if ($type == "create")
        {
            $createOrderValidate = new CreateOrderFormValidate();

            $formOrder = $this->createForm(new CreateOrderForm(), $createOrderValidate);
            $formOrder->handleRequest($request);
            $showWindow = false;

            $session = $request->getSession();
            $sessionFolderFiles = $session->get("folderFiles");
            if (isset($sessionFolderFiles)){
                $folderFiles = $sessionFolderFiles;
            }
            else{
                $folderFiles = Helper::getRandomValue(10);
                $session->set("folderFiles", $folderFiles);
                $session->save();
            }

            if ($request->isMethod('POST'))
            {
                if ($formOrder->get('create')->isClicked())
                {
                    if ($formOrder->isValid())
                    {
                       // print_r($request->request->get('formCreateOrder')); die;

                        $folderFiles = dirname($_SERVER['SCRIPT_FILENAME']) . "/uploads/attachments/" . $folderFiles . "/originals";
                        $arrayFiles = Helper::getFilesFromFolder($folderFiles);
                        var_dump($arrayFiles);

                        $postData = $request->request->get('formCreateOrder');
                        $userId = 1;
                        //Helper::createNewOrder($postData, $userId, $folderFiles);
                        //$showWindow = true;
                        //$formOrder = $this->createForm(new CreateOrderForm());

                        //return new RedirectResponse($this->generateUrl('', array()));
                    }
                }
            }

            return $this->render(
                'AcmeSecureBundle:Client:order_add.html.twig', array('formOrder' => $formOrder->createView(), 'showWindow' => $showWindow, 'folderFiles' => $folderFiles)
            );
        }
        else{

        }

    }


    /**
     * @Route("/upload", name="upload")
     */
    public function uploadAction(Request $request)
    {
        $editId = $this->getRequest()->get('editId');
        $fileName = $this->getRequest()->get('file');

        //if (preg_match('/^\d+$/', $editId))
        {
            if ($fileName){
                $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/' . $editId, 'action' => 'delete'));
            }
            else{
                $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/' . $editId));
            }
        }
        //else
        {

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
