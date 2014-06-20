<?php

namespace Acme\SecureBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\SecurityContext;
use Helper\Helper;
use Acme\SecureBundle\Entity\Author\AuthorProfileFormValidate;
use Acme\SecureBundle\Form\Author\AuthorProfileForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\SecureBundle\Entity\Author\BidFormValidate;
use Acme\SecureBundle\Form\Author\BidForm;


class AuthorController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
        $userId = $this->get('security.context')->getToken()->getUser();
        $userId = 2;
        $session = $request->getSession();
        $sessionCreated = $session->getMetadataBag()->getCreated();
        $sessionLifeTime = $session->getMetadataBag()->getLifetime();
        $whenLogin = Helper::getDateFromTimestamp($sessionCreated, "d/m/Y H:i");
        $sessionRemaining = $sessionCreated + $sessionLifeTime;
        $nowTimestamp = strtotime("now");
        $sessionRemaining = $sessionRemaining - $nowTimestamp;
        $sessionRemaining = Helper::getDateFromTimestamp($sessionRemaining, "i:s");
        $user = Helper::getUserById($userId);

        return array('user' => $user, 'whenLogin' => $whenLogin, 'remainingTime' => $sessionRemaining);
    }


    /**
     * @Template()
     * @return array
     */
    public function profileAction(Request $request, $type)
    {
        if ($type == "view" || $type == "edit") {
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 2;
            $user = Helper::getUserById($userId);
            $userInfo = $user->getUserInfo();
            $showWindow = false;
        }
        else {
            return new RedirectResponse($this->generateUrl('secure_author_index'));
        }
        if ($type == "edit") {
            $isAccessOrder = $user->getIsAccessOrder();
            $profileValidate = new AuthorProfileFormValidate();
            $profileValidate->setIcq($userInfo->getIcq());
            $profileValidate->setSkype($userInfo->getSkype());
            $profileValidate->setMobilePhone($userInfo->getMobilePhone());
            $profileValidate->setStaticPhone($userInfo->getStaticPhone());
            $profileValidate->setUsername($userInfo->getUsername());
            $profileValidate->setSurname($userInfo->getSurname());
            $profileValidate->setLastname($userInfo->getLastname());
            $profileValidate->setCountry($userInfo->getCountry()->getCode());
            $formProfile = $this->createForm(new AuthorProfileForm(), $profileValidate);
            $formProfile->handleRequest($request);
            if ($request->isMethod('POST')) {
                if ($formProfile->get('save')->isClicked()) {
                    if ($formProfile->isValid()) {
                        $postData = $request->request->get('formProfile');
                        Helper::updateUserInfo($postData, $userInfo);
                        if (!$isAccessOrder) {
                            Helper::uploadAuthorFileInfo($user);
                        }
                        $showWindow = true;
                    }
                }
            }
        }
        return array('formProfile' => (isset($formProfile)?$formProfile->createView():null), 'user' => $user, 'userInfo' => $userInfo, 'showWindow' => $showWindow);
    }


    public function uploadAction()
    {
        $editId = $this->getRequest()->get('editId');
        $fileName = $this->getRequest()->get('file');
        $action = $this->getRequest()->get('action');

        if (preg_match('/^\d+$/', $editId))
        {
            if ($action == "profile") {
                if ($fileName){
                    $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'author/' . $editId, 'action' => 'delete'));
                }
                else{
                    $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'author/' . $editId));
                }
            }
            elseif ($action == "order") {

            }
        }
        return new Response(json_encode(array('action' => 'false')));
    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function ordersAction(Request $request, $type)
    {
        //$userId = $this->get('security.context')->getToken()->getUser();
        $userId = 1;
        $user = Helper::getUserById($userId);
        $showWindow = false;
        if ($type == "new") {
            if($request->isXmlHttpRequest()) {
                $action = $request->request->get('action');
                if ($action == 'favoriteOrder') {
                    $orderId = $request->request->get('orderId');
                    $type = "favorite";
                    $actionResponse = Helper::favoriteOrder($orderId, $user, $type);
                    return new Response(json_encode(array('action' => $actionResponse)));
                }
                elseif ($action == 'unfavoriteOrder') {
                    $orderId = $request->request->get('orderId');
                    $type = "unfavorite";
                    $actionResponse = Helper::favoriteOrder($orderId, $user, $type);
                    return new Response(json_encode(array('action' => $actionResponse)));
                }
                elseif ($action == 'newBid') {
                    $orderId = $request->request->get('orderId');
                    $order = Helper::getOrderById($orderId);
                    $postData = [];
                    $postData['fieldSum'] = $request->request->get('bidSum');
                    $postData['fieldDay'] = $request->request->get('bidDay');
                    $postData['fieldComment'] = "";
                    $actionResponse = Helper::setAuthorBid($postData, $user, $order);
                    return new Response(json_encode(array('action' => $actionResponse)));
                }
                else {
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
                    $countOrders = Helper::getCountOrdersForAuthorGrid();
                    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                    $orders = Helper::getClientOrdersForAuthorGrid($sOper, $sField, $sData, $firstRowIndex, $rowsPerPage, $sortingField, $sortingOrder, $user);
                    $response = new Response();
                    $response->total = ceil($countOrders / $rowsPerPage);
                    $response->records = $countOrders;
                    $response->page = $curPage;
                    foreach($orders as $index => $order) {
                        $task = strip_tags($order->getTask());
                        $task = stripcslashes($task);
                        $task = preg_replace("/&nbsp;/", "", $task);
                        if (strlen($task) >= 20) {
                            $task = Helper::getCutSentence($task, 45);
                        }
                        $maxBid = 0;
                        $minBid = 0;
                        $myBid = 0;
                        $dateCreate = Helper::getMonthNameFromDate($order->getDateCreate()->format("d.m.Y"));
                        $dateCreate = $dateCreate . "<br><span class='gridCellTime'>" . $order->getDateCreate()->format("H:s") . "</span>";
                        $dateExpire = Helper::getMonthNameFromDate($order->getDateExpire()->format("d.m.Y"));
                        $dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order->getDateExpire()->format("H:s") . "</span>";
                        $response->rows[$index]['id'] = $order->getId();
                        $response->rows[$index]['cell'] = array(
                            $order->getId(),
                            $order->getNum(),
                            $order->getSubjectOrder()->getChildName(),
                            $order->getTypeOrder()->getName(),
                            $order->getTheme(),
                            $task,
                            $dateExpire,
                            $maxBid,
                            $minBid,
                            $order->getAuthorLastSumBid(),
                            $dateCreate,
                            "",
                            $order->getIsFavorite()
                        );
                    }
                    return new JsonResponse($response);
                }
            }
            return $this->render(
                'AcmeSecureBundle:Author:order_new.html.twig', array('showWindow' => $showWindow)
            );
        }
        elseif ($type == "favorite") {
            if ($request->isXmlHttpRequest()) {
                $action = $request->request->get('action');
                if ($action == 'unfavoriteOrder') {
                    $orderId = $request->request->get('orderId');
                    $type = "unfavorite";
                    $actionResponse = Helper::favoriteOrder($orderId, $user, $type);
                    return new Response(json_encode(array('action' => $actionResponse)));
                }
                else {
                    $postData = $request->request->all();
                    $curPage = $postData['page'];
                    $rowsPerPage = $postData['rows'];
                    $countOrders = Helper::getCountOrdersForAuthorGrid();
                    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                    $orders = Helper::getClientFavoriteOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user);
                    $response = new Response();
                    $response->total = ceil($countOrders / $rowsPerPage);
                    $response->records = $countOrders;
                    $response->page = $curPage;
                    foreach($orders as $index => $order) {
                        $userOrder = $order->getUserOrder();
                        $task = strip_tags($userOrder->getTask());
                        $task = stripcslashes($task);
                        $task = preg_replace("/&nbsp;/", "", $task);
                        if (strlen($task) >= 20) {
                            $task = Helper::getCutSentence($task, 45);
                        }
                        $dateFavorite = Helper::getMonthNameFromDate($order->getDateFavorite()->format("d.m.Y"));
                        $dateFavorite = $dateFavorite . "<br><span class='gridCellTime'>" . $order->getDateFavorite()->format("H:s") . "</span>";
                        $dateExpire = Helper::getMonthNameFromDate($userOrder->getDateExpire()->format("d.m.Y"));
                        $dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $userOrder->getDateExpire()->format("H:s") . "</span>";
                        $response->rows[$index]['id'] = $userOrder->getId();
                        $response->rows[$index]['cell'] = array(
                            $userOrder->getId(),
                            $userOrder->getNum(),
                            $userOrder->getSubjectOrder()->getChildName(),
                            $userOrder->getTypeOrder()->getName(),
                            $userOrder->getTheme(),
                            $task,
                            $dateExpire,
                            $userOrder->getAuthorLastSumBid(),
                            $dateFavorite,
                            "",
                        );
                    }
                    return new JsonResponse($response);
                }
            }
            return $this->render(
                'AcmeSecureBundle:Author:order_favorite.html.twig', array('showWindow' => $showWindow)
            );
        }
    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function orderAction(Request $request, $num)
    {
        if (is_numeric($num)) {
            $order = Helper::getOrderByNumForAuthor($num);
            if (!$order) {
                return new RedirectResponse($this->generateUrl('secure_author_index'));
            }
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 1;
            $user = Helper::getUserById($userId);
            $access = Helper::checkUserAccessForOrder($user, $order);
            if (!$access) {
                return new RedirectResponse($this->generateUrl('secure_author_index'));
            }
            $clientLink = Helper::getUserLinkProfile($order, "client", $this->container);
            $filesOrder = Helper::getFilesForOrder($order);
            $statusOrder = $order->getStatusOrder()->getCode();
            if ($statusOrder == 'w') {
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
                    'AcmeSecureBundle:Author:order_work.html.twig', array('files' => $filesOrder, 'order' => $order, 'client' => $clientLink)
                );
            }
            else {
                $bidValidate = new BidFormValidate();
                $bids = Helper::getAllAuthorsBidsForSelectedOrder($user, $order);
                $showDialogConfirmSelection = Helper::getClientSelectedBid($user, $order);
                $formBid = $this->createForm(new BidForm(), $bidValidate);
                if ($request->isXmlHttpRequest()) {
                    $nd = $request->request->get('nd');
                    $action = $request->request->get('action');
                    if (isset($nd)) {
                        $response = new Response();
                        foreach($bids as $index => $bid) {
                            $dateBid =  $bid->getDateBid();
                            $dateBid = $dateBid->format("d.m.Y") . "<br><span class='grid-cell-time'>" . $dateBid->format("H:i") . "</span>";
                            $response->rows[$index]['id'] = $bid->getId();
                            $response->rows[$index]['cell'] = array(
                                $bid->getId(),
                                $bid->getSum(),
                                $bid->getDay(),
                                $bid->getIsClientDate(),
                                $dateBid,
                                $bid->getComment(),
                                ""
                            );
                        }
                        return new JsonResponse($response);
                    }
                    elseif (isset($action)) {
                        if ($action == 'deleteBid') {
                            $bidId = $request->request->get('bidId');
                            $actionResponse = Helper::deleteSelectedAuthorBid($bidId, $user, $order);
                            return new Response(json_encode(array('action' => $actionResponse)));
                        }
                        elseif ($action == 'confirmSelection' || $action == 'failSelection') {
                            $bidId = $request->request->get('bidId');
                            $mode = null;
                            if ($action == 'confirmSelection') {
                                $mode = 'confirm';
                            }
                            elseif ($action == 'failSelection') {
                                $mode = 'fail';
                            }
                            $actionResponse = Helper::authorConfirmSelection($order, $user, $bidId, $mode, $this->container);
                            return new Response(json_encode(array('action' => $actionResponse)));
                        }
                        /*elseif ($action == 'cancelBid') {
                            $bidId = $request->request->get('bidId');
                            $actionResponse = Helper::cancelSelectedClientBid($bidId);
                            return new Response(json_encode(array('action' => $actionResponse)));
                        }*/
                    }
                    else {
                        $formBid->handleRequest($request);
                        if ($formBid->isValid()) {
                            $postData = $request->request->get('formBid');
                            Helper::setAuthorBid($postData, $user, $order);
                            return new Response(json_encode(array('response' => 'valid')));
                        }
                        else {
                            $errors = [];
                            $arrayResponse = [];
                            foreach ($formBid as $fieldName => $formField) {
                                $errors[$fieldName] = $formField->getErrors();
                            }
                            foreach ($errors as $index => $error) {
                                if (isset($error[0])) {
                                    $arrayResponse[$index] = $error[0]->getMessage();
                                }
                            }
                            return  new Response(json_encode(array('response' => $arrayResponse)));
                        }
                    }
                }
                return $this->render(
                    'AcmeSecureBundle:Author:order_select.html.twig', array('formBid' => $formBid->createView(), 'files' => $filesOrder, 'order' => $order, 'client' => $clientLink, 'bids' => $bids, 'showDialogConfirmSelection' => $showDialogConfirmSelection)
                );
            }
        }
        else {
            return new RedirectResponse($this->generateUrl('secure_author_index'));
        }
    }

}
