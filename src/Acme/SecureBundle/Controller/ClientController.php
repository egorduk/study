<?php

namespace Acme\SecureBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\ExpressionLanguage\Parser;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
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
    /**
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
        //throw new NotFoundHttpException('Sorry not existing!');
        $user = $this->getUser();
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
        $userLogin = $user->getLogin();
        $userRole = $user->getUserRole()->getName();
        //$avatar = Helper::getUserAvatar($user);
        $user = Helper::getUserAvatar($user);
        //var_dump($user->getHashCompare());
        $obj = [];
        $obj['token'] = $user->getToken();
        return array('user' => $user, 'whenLogin' => $whenLogin, 'remainingTime' => $sessionRemaining, 'obj' => $obj);
    }


    /**
     * @Template()
     * @return array
     */
    public function profileAction(Request $request, $type)
    {
        if ($type == "view" || $type == "edit") {
            $userId = $this->get('security.context')->getToken()->getUser();
            //$userId = 1;
            $user = Helper::getUserById($userId);
            $userInfo = $user->getUserInfo();
            $showWindow = false;
        }
        if ($type == "view") {
            return array('formProfile' =>'', 'user' => $user, 'userInfo' => $userInfo, 'showWindow' => $showWindow);
        } elseif ($type == "edit") {
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
        } else {
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
            $user = $this->getUser();
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
            } else {
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
                'AcmeSecureBundle:Client:order_create.html.twig', array('formOrder' => $formOrder->createView(), 'showWindow' => $showWindow, 'folderFiles' => $folderFiles)
            );
        }
        elseif ($type == "view") {
            if($request->isXmlHttpRequest()) {
                //$response = new Response(json_encode(array('response' => 'hello')));
                //$response->headers->set('Content-Type', 'application/json');
                //return $response;
                //$response->setStatusCode(Response::HTTP_NOT_FOUND);
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
                $countOrders = Helper::getCountOrdersForClientGrid($user);
                $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                $orders = Helper::getClientOrdersForGrid($sOper, $sField, $sData, $firstRowIndex, $rowsPerPage, $user, $sortingField, $sortingOrder);
                $response = new Response();
                $response->total = ceil($countOrders / $rowsPerPage);
                $response->records = $countOrders;
                $response->page = $curPage;
                $countBidsForEveryOrder = Helper::getCountBidsForEveryOrder($user);
                foreach($orders as $index => $order) {
                    $countBids = 0;
                    $task = strip_tags($order->getTask());
                    $task = stripcslashes($task);
                    $task = preg_replace("/&nbsp;/", "", $task);
                    if (strlen($task) >= 20) {
                        $task = Helper::getCutSentence($task, 35);
                    }
                    $orderId = $order->getId();
                    foreach($countBidsForEveryOrder as $elem) {
                        if ($elem['user_order_id'] == $orderId) {
                            $countBids = $elem['count_bids'];
                            break;
                        }
                    }
                    $dateCreate = Helper::getMonthNameFromDate($order->getDateCreate()->format("d.m.Y"));
                    $dateCreate = $dateCreate . "<br><span class='grid-cell-time'>" . $order->getDateCreate()->format("H:s") . "</span>";
                    $dateExpire = Helper::getMonthNameFromDate($order->getDateExpire()->format("d.m.Y"));
                    $dateExpire = $dateExpire . "<br><span class='grid-cell-time'>" . $order->getDateExpire()->format("H:s") . "</span>";
                    $countBids = "<span class='grid-cell-author-response'>" . $countBids . "</span>";
                    $response->rows[$index]['id'] = $orderId;
                    $response->rows[$index]['cell'] = array(
                        $orderId,
                        $order->getNum(),
                        $order->getTypeOrder()->getName(),
                        $order->getTheme(),
                        $order->getSubjectOrder()->getChildName(),
                        $task,
                        $order->getStatusOrder()->getName(),
                        $dateExpire,
                        $countBids,
                        $dateCreate,
                        "",
                        $order->getIsShowAuthor()
                    );
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
                    return new Response(json_encode(array('action' => true)));
                }
                return  new Response(json_encode(array('action' => false)));
            }
        }
        elseif ($type == "show") {
            if ($request->isXmlHttpRequest()) {
                $orderId = $request->request->get('orderId');
                $isShow = Helper::showOrderForAuthor($orderId, $user);
                if ($isShow) {
                    return new Response(json_encode(array('action' => true)));
                }
                return new Response(json_encode(array('action' => false)));
            }
        }
        elseif ($type == "load_config") {
            if($request->isXmlHttpRequest()) {
                $orderId = $request->request->get('orderId');
                $order = Helper::getOrderById($orderId);
                if ($order) {
                    $task = $order->getTask();
                    $dateExpire = $order->getDateExpire()->format("d/m/Y");
                    return  new Response(json_encode(array('action' => 'true', 'task' => $task, 'dateExpire' => $dateExpire)));
                }
                return new Response(json_encode(array('action' => 'false')));
            }
        }
        elseif ($type == "save_config") {
            if($request->isXmlHttpRequest()) {
                $orderId = $request->request->get('orderId');
                $newTask = $request->request->get('newTask');
                $newDateExpire = $request->request->get('newDateExpire');
                $order = Helper::getOrderById($orderId);
                if ($order) {
                    Helper::saveEditedTaskAndDateExpireForOrder($order, $newTask, $newDateExpire);
                    return new Response(json_encode(array('action' => 'true')));
                }
                return new Response(json_encode(array('action' => 'false')));
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
            $user = $this->getUser();
            $order = Helper::getOrderByNumForClient($num, $user);
            if (!$request->isXmlHttpRequest()) {
                // $filesOrder = Helper::getFilesForOrder($order);
                //$filesOrder = '';
                //$folder = 'http://localhost/study/web/uploads/attachments/' . $order->getFilesFolder() . '/originals/';
                //$authorLink = Helper::getUserLinkProfile($order, "author", $this->container);
                if (!$order) {
                    return new RedirectResponse($this->generateUrl('secure_client_orders',array('type' => 'view')));
                }
                $codeStatusOrder = $order->getStatusOrder()->getCode();
                if ($codeStatusOrder == 'w') {
                    if ($request->isXmlHttpRequest()) {
                        $action = $request->request->get('action');
                        $lastId = $request->request->get('lastId');
                        if ($action == 'getChats') {
                            $messages = Helper::getChatMessages($user, $order, $lastId);
                            $arr = [];
                            foreach($messages as $index => $msg) {
                                $arr[$index]['id'] = $msg->getId();
                                $arr[$index]['msg'] = $msg->getMessage();
                                $arr[$index]['login'] = $msg->getUser()->getLogin();
                                $arr[$index]['date'] = $msg->getDateWrite()->format("d.m.Y");
                                $arr[$index]['time'] = $msg->getDateWrite()->format("H:i:s");
                                $arr[$index]['role'] = $user->getRole()->getId();
                                $arr[$index]['role_sender'] = $msg->getUser()->getRole()->getId();
                            }
                            return new Response(json_encode(array('messages' => $arr)));
                        }
                        elseif ($action == 'sendMessage') {
                            $message = $request->request->get('text');
                            $insertId = Helper::addNewWebchatMessage($user, $order, $message);
                            return new Response(json_encode(array('insertID' => $insertId)));
                        }
                    }
                    return $this->render(
                        'AcmeSecureBundle:Client:order_work.html.twig', array('files' => $filesOrder, 'order' => $order, 'author' => $authorLink)
                    );
                } elseif ($codeStatusOrder == 'sa') {
                    $obj = [];
                    $obj['userLogin'] = $user->getLogin();
                    $obj['userId'] = $user->getId();
                    $obj['token'] = $user->getToken();
                    //$obj['author']['login'] = $order->getUser()->getLogin();
                    //$obj['author']['status'] = $order->getUser()->getIsActive();
                    $obj['author']['login'] = 'author';
                    $obj['author']['status'] = 1;
                    $obj['order'] = $order;
                    $obj['files'] = $filesOrder;
                    $obj['folder'] = $folder;
                    return $this->render(
                        'AcmeSecureBundle:Client:order_select.html.twig', array('obj' => $obj)
                    );
                }
            } elseif ($request->isXmlHttpRequest()) {
                $nd = $request->request->get('nd');
                $action = $request->request->get('action');
                if (isset($nd)) {
                    $response = new Response();
                    $bids = Helper::getAllAuthorsBidsForOrder($order);
                    foreach($bids as $index => $bid) {
                        $fileName = $bid['avatar'];
                        $userLogin = $bid['login'];
                        $userId = $bid['uid'];
                        $pathAvatar = Helper::getFullPathToAvatar(null, $userId);
                        //$urlClient = $this->generateUrl('secure_client_action', array('type' => 'view_client_profile', 'id' => $userId));
                        $author = "<img src='$pathAvatar' align='middle' alt='$fileName' width='110px' height='auto' class='thumbnail'><a href='$urlClient' class='label label-primary'>$userLogin</a>";
                        $dateBid =  new \DateTime($bid['date_bid']);
                        $dateBid = $dateBid->format("d.m.Y") . "<br><span class='grid-cell-time'>" . $dateBid->format("H:i") . "</span>";
                        $comment = "<span class='grid-cell-comment'>" . $bid['comment'] . "</span>";
                        $response->rows[$index]['id'] = $bid['id'];
                        $response->rows[$index]['cell'] = array(
                            $bid['id'],
                            $author,
                            $bid['sum'],
                            $bid['day'],
                            $bid['is_client_date'],
                            $comment,
                            $dateBid,
                            "",
                            $bid['is_select_client'],
                            //$userLogin
                            $bid['uid']
                        );
                    }
                    return new JsonResponse($response);
                } elseif (isset($action)) {
                    /*if ($action == 'selectBid') {
                        $bidId = $request->request->get('bidId');
                        $actionResponse = Helper::selectAuthorBid($user, $bidId, $order, $this->container);
                        return new Response(json_encode(array('action' => $actionResponse)));
                    } elseif ($action == 'cancelBid') {
                        $bidId = $request->request->get('bidId');
                        $actionResponse = Helper::cancelSelectedAuthorBid($bidId, $order);
                        return new Response(json_encode(array('action' => $actionResponse)));
                    } elseif ($action == 'auctionBid') {
                        $bidId = $request->request->get('bidId');
                        $auctionPrice = $request->request->get('auctionPrice');
                        $auctionDay = $request->request->get('auctionDay');
                        $actionResponse = Helper::createAuctionByAuthorBid($bidId, $order, $auctionPrice, $auctionDay, $this->container);
                        return new Response(json_encode(array('action' => $actionResponse)));
                    } elseif ($action == 'hideBid') {
                        $bidId = $request->request->get('bidId');
                        $isHide = Helper::hideBidForClient($bidId);
                        if ($isHide) {
                            return new Response(json_encode(array('action' => true)));
                        }
                        return  new Response(json_encode(array('action' => false)));
                    }*/
                }
            }
            //$author = Helper::getAuthorByOrder($order);
        } else {
            return new RedirectResponse($this->generateUrl('secure_client_orders',array('type' => 'view')));
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


    /**
     * @Template()
     * @return array
     */
    public function actionAction(Request $request, $mode, $id)
    {
        if ($mode == "info" && true === $this->get('security.context')->isGranted('ROLE_AUTHOR') && !$request->isXmlHttpRequest() && is_numeric($id)) {
            $user = Helper::getUserById($id);
            if ($user) {
                $orders = Helper::getClientTotalOrders($user);
                $countCanceledOrders = 0;
                foreach($orders as $order) {
                    if ($order->getStatusOrder()->getCode() == 'cl') {
                        $countCanceledOrders++;
                    }
                }
                $user->setClientIdInfo($id);
                $pathAvatar = Helper::getFullPathToAvatar($user->getAvatar());
                $avatar = "<img src='$pathAvatar' align='middle' alt='client_avatar' width='110px' height='auto' class='thumbnail'>";
                return $this->render(
                    'AcmeSecureBundle:Client:action_info.html.twig', array('mode' => 'authorView', 'user' => $user, 'countTotalOrders' => count($orders), 'countCanceledOrders' => $countCanceledOrders , 'avatar' => $avatar)
                );
            } else {}
        } elseif ($request->isXmlHttpRequest() && true === $this->get('security.context')->isGranted('ROLE_AUTHOR') && is_numeric($id) && $mode == "info_total_orders") {
            $user = Helper::getUserById($id);
            if ($user) {
                $response = new Response();
                $orders = Helper::getClientTotalOrders($user);
                foreach($orders as $index => $order) {
                    $response->rows[$index]['id'] = $order->getId();
                    $response->rows[$index]['cell'] = array(
                        $order->getId(),
                        $order->getNum(),
                        $order->getSubjectOrder()->getChildName(),
                        $order->getTypeOrder()->getName(),
                        $order->getTheme()
                    );
                }
                return new JsonResponse($response);
            } else {}
        } elseif ($request->isXmlHttpRequest() && true === $this->get('security.context')->isGranted('ROLE_AUTHOR') && is_numeric($id) && $mode == "info_author_completed_total_orders") {
            $client = Helper::getUserById($id);
            if ($client) {
                $response = new Response();
                $author = $this->get('security.context')->getToken()->getUser();
                $orders = Helper::getAuthorTotalCompletedOrdersForClient($client, $author);
                foreach($orders as $index => $order) {
                    $response->rows[$index]['id'] = $order[0]->getNum();
                    $response->rows[$index]['cell'] = array(
                        $order[0]->getNum(),
                        $order[0]->getNum(),
                        $order[0]->getSubjectOrder()->getChildName(),
                        $order[0]->getTypeOrder()->getName(),
                        $order[0]->getTheme(),
                        $order[0]->getDateComplete()->format("d.m.Y H:i"),
                        $order['curr_sum'],
                        $order[0]->getClientDegree(),
                        $order[0]->getClientComment()
                    );
                }
                return new JsonResponse($response);
            } else {}
        } 
        return NULL;

        /*elseif ($mode == "info" && true === $this->get('security.context')->isGranted('ROLE_CLIENT')) {
            $user = Helper::getUserById($id);
            return $this->render(
                'AcmeSecureBundle:Client:action_info.html.twig', array('mode' => 'clientView', 'user' => $user)
            );
        }*/
    }


    /**
     * Serves a file
     */
    public function downloadFileAction($type, $num, $filename = null)
    {
        $basePath = $_SERVER['DOCUMENT_ROOT'] . '/study/web/uploads/';
        if ($type == 'pdf') {
            $filePath = $basePath . 'pdf/' . $num . '/' . $filename;
            $user = $this->getUser();
            $order = Helper::getOrderByNumForAuthor($num, $user);
            if ($order) {
                Helper::createPdfOrder($order);
            } else {
                throw $this->createNotFoundException();
            }
        } elseif ($type == 'attachments') {

            $filePath = $basePath . 'attachments/orders/' . $num . '/client/' . $filename;
        }
        if (!file_exists($filePath)) {
            throw $this->createNotFoundException();
        }
        $response = new BinaryFileResponse($filePath);
        $response->trustXSendfileTypeHeader();
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $filename,
            iconv('UTF-8', 'ASCII//TRANSLIT', $filename)
        );
        return $response;
    }


}
