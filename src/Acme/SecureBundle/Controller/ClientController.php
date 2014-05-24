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
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\Security\Core\Util\StringUtils;
//use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Helper\Helper;
//use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Acme\SecureBundle\Form\Client\ClientProfileForm;
use Acme\SecureBundle\Entity\Client\ClientProfileFormValidate;
use Acme\SecureBundle\Form\Client\CreateOrderForm;
use Acme\SecureBundle\Entity\Client\CreateOrderFormValidate;
use Symfony\Component\HttpFoundation\JsonResponse;


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
        $userId = 1;
        //$userRole = $this->get('security.context')->getToken()->getRoles();
        $session = $request->getSession();
        $sessionCreated = $session->getMetadataBag()->getCreated();
        $sessionLifeTime = $session->getMetadataBag()->getLifetime();
        //$sessionUpdated = $session->getMetadataBag()->getLastUsed();
        //$whenUpdated = Helper::getDateFromTimestamp($sessionUpdated, "d/m/Y H:i:s");
        $whenLogin = Helper::getDateFromTimestamp($sessionCreated, "d/m/Y H:i");
        $sessionRemaining = $sessionCreated + $sessionLifeTime;
        $nowTimestamp = strtotime("now");
        $sessionRemaining = $sessionRemaining - $nowTimestamp;
        $sessionRemaining = Helper::getDateFromTimestamp($sessionRemaining, "i:s");
        $user = Helper::getUserById($userId);
        $userLogin = $user->getLogin();
        $userRole = $user->getRole()->getName();

        return array('userId' => $userId, 'userLogin' => $userLogin, 'userRole' => $userRole, 'whenLogin' => $whenLogin, 'remainingTime' => $sessionRemaining);
    }


    /**
     * @Template()
     * @return array
     */
    public function profileAction(Request $request, $type)
    {
        if ($type == "view" || $type == "edit") {
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 1;
            $user = Helper::getUserById($userId);
            $userInfo = $user->getUserInfo();
            $showWindow = false;
        }

        if ($type == "view") {
            return array('formProfile' =>'', 'user' => $user, 'userInfo' => $userInfo, 'showWindow' => $showWindow);
        }
        elseif ($type == "edit") {
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

            if ($request->isMethod('POST')) {
                if ($formProfile->get('save')->isClicked()) {
                    if ($formProfile->isValid()) {
                        $postData = $request->request->get('formProfile');
                        Helper::updateUserInfo($postData, $userInfo);
                        $showWindow = true;
                    }
                }
            }

            return array('formProfile' => $formProfile->createView(), 'user' => '', 'userInfo' => $userInfo, 'showWindow' => $showWindow);
        }
        else {
            return new RedirectResponse($this->generateUrl('secure_client_index'));
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
    public function ordersAction(Request $request, $type)
    {
        if ($type == "view" || $type == "create" || $type == "delete" || $type == "hide" || $type == "configure" || $type == "show") {
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 1;
            $user = Helper::getUserById($userId);
        }

        if ($type == "create") {
            $createOrderValidate = new CreateOrderFormValidate();
            $formOrder = $this->createForm(new CreateOrderForm(), $createOrderValidate);
            $formOrder->handleRequest($request);
            $showWindow = false;

            $session = $request->getSession();
            $sessionFolderFiles = $session->get("folderFiles");
            if (isset($sessionFolderFiles)) {
                $folderFiles = $sessionFolderFiles;
            }
            else {
                $folderFiles = "non_" . Helper::getRandomValue(5);
                $session->set("folderFiles", $folderFiles);
                $session->save();
            }

            if ($request->isMethod('POST')) {
                if ($formOrder->get('create')->isClicked()) {
                    if ($formOrder->isValid()) {
                        $filesFolder = Helper::getFullPathFolderFiles($folderFiles, "originals");
                        $arrayFiles = Helper::getFilesFromFolder($filesFolder, $folderFiles);
                        $postData = $request->request->get('formCreateOrder');
                        Helper::createNewOrder($postData, $user, $folderFiles, $arrayFiles);
                        $session->remove("folderFiles");
                        $showWindow = true;
                    }
                }
            }

            return $this->render(
                'AcmeSecureBundle:Client:order_add.html.twig', array('formOrder' => $formOrder->createView(), 'showWindow' => $showWindow, 'folderFiles' => $folderFiles)
            );
        }
        elseif ($type == "view") {
            if($request->isXmlHttpRequest()) {
                //$response = new Response(json_encode(array('response' => 'hello')));
                //$response->headers->set('Content-Type', 'application/json');
                //return $response;
                //$response->setStatusCode(Response::HTTP_NOT_FOUND);
                //$user = Helper::getUserById($userId);
                $postData = $request->request->all();
                $curPage = $postData['page'];
                $rowsPerPage = $postData['rows'];
                $sortingField = $postData['sidx'];
                $sortingOrder = $postData['sord'];
                $search = $postData['_search'];
                $sField = $sData = $sTable = $sOper = null;

                if (isset($search) && $search == "true") {
                    $sOper = $postData['searchOper'];
                    $sData = $postData['searchString'];
                    $sField = $postData['searchField'];
                }

                $countOrders = Helper::getCountOrdersForGrid($user);

                /*if ($totalRows < $rowsPerPage)
                    $response->page = 1;
                else
                    $response->page = $curPage;*/

                $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                $orders = Helper::getClientOrdersForGrid($sOper, $sField, $sData, $firstRowIndex, $rowsPerPage, $user, $sortingField, $sortingOrder);
                $response = new Response();
                $response->total = ceil($countOrders / $rowsPerPage);
                $response->records = $countOrders;
                $response->page = $curPage;
                $i = 0;
                $responseAuthor = 0;

                foreach($orders as $order) {
                    $task = strip_tags($order->getTask());
                    $task = stripcslashes($task);
                    $task = preg_replace("/&nbsp;/", "", $task);
                    if (strlen($task) >= 20) {
                        $task = Helper::getCutSentence($task, 35);
                    }
                    $dateCreate = Helper::getMonthNameFromDate($order->getDateCreate()->format("d.m.Y"));
                    $dateCreate = $dateCreate . "<br><span class='gridCellTime'>" . $order->getDateCreate()->format("H:s") . "</span>";
                    $dateExpire = Helper::getMonthNameFromDate($order->getDateExpire()->format("d.m.Y"));
                    $dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order->getDateExpire()->format("H:s") . "</span>";
                    $response->rows[$i]['id'] = $order->getId();
                    $response->rows[$i]['cell'] = array(
                        $order->getId(),
                        $order->getNum(),
                        $order->getTypeOrder()->getName(),
                        $order->getTheme(),
                        $order->getSubjectOrder()->getChildName(),
                        $task,
                        $order->getStatusOrder()->getName(),
                        $dateCreate,
                        $dateExpire,
                        $responseAuthor,
                        ""
                    );

                    $i++;
                }

                return new JsonResponse($response);
            }
            $showWindow = false;
            return $this->render(
                'AcmeSecureBundle:Client:order_view.html.twig', array('showWindow' => $showWindow)
            );
        }
        elseif ($type == "delete") {
            if ($request->isXmlHttpRequest()) {
                $orderId = $request->request->get('orderId');
                $isDelete = Helper::deleteOrderByClient($orderId, $user);
                if ($isDelete) {
                    return new Response(json_encode(array('action' => 'true')));
                }
                else {
                    return  new Response(json_encode(array('action' => 'false')));
                }
            }
        }
        elseif ($type == "hide") {
            if($request->isXmlHttpRequest()) {
                $orderId = $request->request->get('orderId');
                $isHide = Helper::hideOrderFromAuthor($orderId, $user);
                if ($isHide) {
                    return new Response(json_encode(array('action' => 'true')));
                }
                else {
                    return  new Response(json_encode(array('action' => 'false')));
                }
            }
        }
        elseif ($type == "show") {
            if ($request->isXmlHttpRequest()) {
                $orderId = $request->request->get('orderId');
                $isShow = Helper::showOrderForAuthor($orderId, $user);
                if ($isShow) {
                    return new Response(json_encode(array('action' => 'true')));
                }
                else {
                    return  new Response(json_encode(array('action' => 'false')));
                }
            }
        }
    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function orderAction(Request $request, $num)
    {
        if (is_numeric($num)) {
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 1;
            $user = Helper::getUserById($userId);
            $order = Helper::getOrderByNumForClient($num, $user);

            if ($request->isXmlHttpRequest()) {
                $nd = $request->request->get('nd');
                if (isset($nd)) {
                    $response = new Response();
                    $bids = Helper::getAllAuthorsBids($order);
                    foreach($bids as $index => $bid) {
                        /*$response->rows[$index]['id'] = $bid[0]->getId();
                        $response->rows[$index]['cell'] = array(
                            $bid[0]->getId(),
                            $bid[0]->getUser()->getLogin(),
                            $bid[0]->getSum(),
                            $bid[0]->getDay(),
                            $bid[0]->getIsClientDate(),
                            $bid[0]->getComment(),
                            $bid[0]->getDateBid()->format("d.m.Y H:i"),
                            ""
                        /*$response->rows[$index]['id'] = $bid->getId();
                        $response->rows[$index]['cell'] = array(
                            $bid->getId(),
                            $bid->getUser()->getLogin(),
                            $bid->getSum(),
                            $bid->getDay(),
                            $bid->getIsClientDate(),
                            $bid->getComment(),
                            ""*/

                        $fileName = $bid['avatar'];
                        $userLogin = $bid['login'];
                        $userId = $bid['uid'];
                        $pathAvatar = Helper::getFullPathToAvatar($fileName);
                        $urlClient = $this->generateUrl('secure_client_action', array('type' => 'view_client_profile', 'id' => $userId));
                        $author = "<img src='$pathAvatar' align='middle' alt='$fileName' width='110px' class='thumbnail'><a href='$urlClient' class='label label-primary'>$userLogin</a>";

                        $dateBid =  new \DateTime($bid['date_bid']);
                        $response->rows[$index]['id'] = $bid['id'];
                        $response->rows[$index]['cell'] = array(
                            $bid['id'],
                            $author,
                            $bid['sum'],
                            $bid['day'],
                            $bid['is_client_date'],
                            $bid['comment'],
                            $dateBid->format("d.m.Y H:i"),
                            ""

                        );
                    }
                    return new JsonResponse($response);
                }
            }

            if ($order) {

            }
            else{

            }

            return $this->render(
                'AcmeSecureBundle:Client:order_select.html.twig', array('order' => $order, 'showWindow' => false)
            );
        }
        else {

        }
    }


    /**
     * @Route("/upload", name="upload")
     */
    public function uploadAction()
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


}
