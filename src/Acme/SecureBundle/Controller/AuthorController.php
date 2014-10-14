<?php

namespace Acme\SecureBundle\Controller;

use Acme\SecureBundle\Entity\Author\MailOptionsFormValidate;
use Acme\SecureBundle\Form\Author\AuthorCreateMailOptionsForm;
use Acme\SecureBundle\Form\Author\AuthorCreatePsForm;
use Acme\SecureBundle\Entity\Author\CreatePsFormValidate;
use Acme\SecureBundle\Entity\CancelRequest;
use Acme\SecureBundle\Form\Author\AuthorMailOptionsForm;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\Exception\AccessException;
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
        //$userId = 2;
        $session = $request->getSession();
        $sessionCreated = $session->getMetadataBag()->getCreated();
        $sessionLifeTime = $session->getMetadataBag()->getLifetime();
        $whenLogin = Helper::getDateFromTimestamp($sessionCreated, "d/m/Y H:i");
        $sessionRemaining = $sessionCreated + $sessionLifeTime;
        $nowTimestamp = strtotime("now");
        $sessionRemaining = $sessionRemaining - $nowTimestamp;
        $sessionRemaining = Helper::getDateFromTimestamp($sessionRemaining, "i:s");
        $user = Helper::getUserById($userId);
        $avatar = Helper::getUserAvatar($user);
        return array('user' => $user, 'whenLogin' => $whenLogin, 'remainingTime' => $sessionRemaining, 'avatar' => $avatar);
    }


    /**
     * @Template()
     * @return array
     */
    public function profileAction(Request $request, $type)
    {
        if ($type == "view" || $type == "edit") {
            $user = $this->get('security.context')->getToken()->getUser();
            $userInfo = $user->getUserInfo();
            //$avatar = Helper::getUserAvatar($user);
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
            $avatarOption = Helper::getAvatarOption($user);
            $profileValidate->setAvatarOption($avatarOption);
            $formProfile = $this->createForm(new AuthorProfileForm(), $profileValidate);
            $formProfile->handleRequest($request);
            if ($request->isMethod('POST')) {
                if ($formProfile->get('save')->isClicked()) {
                    if ($formProfile->isValid()) {
                        $postData = $request->request->get('formProfile');
                        Helper::updateUserInfo($postData, $userInfo);
                        $avatarOption = $postData['selectorAvatarOptions'];
                        if ($avatarOption == 'man' || $avatarOption == 'woman') {
                            $arrAvatarOptions = ['man' => 'default_m.jpg', 'woman' => 'default_w.jpg'];
                            $fileName = $arrAvatarOptions[$avatarOption];
                            Helper::updateUserAvatar($fileName, $user, $mode = 'controller');
                            $folder = Helper::getAvatarFolder($user);
                            Helper::deleteAllFilesFromFolder($folder);
                            $user->setAvatar($fileName);
                        }
                        if (!$isAccessOrder) {
                            Helper::uploadAuthorFileInfo($user);
                        }
                        $showWindow = true;
                    }
                }
            }
        }
        $avatar = Helper::getUserAvatar($user);
        return array('formProfile' => (isset($formProfile)?$formProfile->createView():null), 'user' => $user, 'userInfo' => $userInfo, 'showWindow' => $showWindow, 'avatar' => $avatar);
    }


    /**
     * @Route("/upload", name="upload")
     */
    public function uploadAction(Request $request)
    {
        $orderNum = $this->getRequest()->get('editId');
        $fileName = $this->getRequest()->get('file');
        $action = $this->getRequest()->get('action');
        if (preg_match('/^\d+$/', $orderNum)) {
            if ($action == "profile") {
                if ($fileName) {
                   // $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'avatars/author/' . $orderNum, 'action' => 'delete'));
                } else {
                    $user = $this->get('security.context')->getToken()->getUser();
                    $session = new Session();
                    //$session->set('user', $user->getId());
                    $session->set('user', $user);
                    $session->save();
                    $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'avatars/author/' . $orderNum,
                        'mode' => 'profile',
                        'allowed_extensions' => array('gif', 'png', 'jpeg', 'jpg'),
                        //'max_number_of_files' => 1,
                        'max_file_size' => 10485760 // 10MB
                    ));
                    $session->remove('user');
                }
            } elseif ($action == "order") {
                if ($fileName) {
                    $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/orders/' . $orderNum));
                } else {
                    $user = $this->get('security.context')->getToken()->getUser();
                    $session = new Session();
                    $session->set('user', $user->getId());
                    $session->set('order', $orderNum);
                    $session->save();
                    $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/orders/' . $orderNum . '/author', 'max_number_of_files' => 10/*, 'max_file_size' => 4*/));
                    $session->remove('user');
                    $session->remove('order');
                }
            }
            return new Response(json_encode(array('action' => 'success')));
        }
        return new Response(json_encode(array('action' => 'error')));
    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function ordersAction(Request $request, $type)
    {
        $user = $this->get('security.context')->getToken()->getUser();
        $showWindow = false;
        if ($user->getIsAccessOrder()) {
            if ($type == "new") {
                $bidValidate = new BidFormValidate();
                $formBid = $this->createForm(new BidForm(), $bidValidate);
                if ($request->isXmlHttpRequest()) {
                    $action = $request->request->get('action');
                    if ($action == 'favoriteOrder') {
                        $orderId = $request->request->get('orderId');
                        $actionResponse = Helper::favoriteOrder($orderId, $user, "favorite");
                        return new Response(json_encode(array('action' => $actionResponse)));
                    }
                    elseif ($action == 'unfavoriteOrder') {
                        $orderId = $request->request->get('orderId');
                        $type = "unfavorite";
                        $actionResponse = Helper::favoriteOrder($orderId, $user, $type);
                        return new Response(json_encode(array('action' => $actionResponse)));
                    }
                    elseif ($action == 'newBid') {
                            $formBid->handleRequest($request);
                            if ($formBid->isValid()) {
                                $postData = $request->request->get('formBid');
                                //Helper::setAuthorBid($postData, $user, $order);
                                return new Response(json_encode(array('response' => 'valid')));
                            } else {
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

                        /*$orderId = $request->request->get('orderId');
                        $order = Helper::getOrderById($orderId);
                        $postData = [];
                        $postData['fieldSum'] = $request->request->get('bidSum');
                        $postData['fieldDay'] = $request->request->get('bidDay');
                        $postData['fieldComment'] = $request->request->get('bidComment');
                        $actionResponse = Helper::setAuthorBid($postData, $user, $order);
                        return new Response(json_encode(array('action' => $actionResponse)));*/
                    } elseif ($action = 'new') {
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
                        $countOrders = Helper::getCountNewOrdersForAuthorGrid();
                        $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                        $orders = Helper::getNewOrdersForAuthorGrid($sOper, $sField, $sData, $firstRowIndex, $rowsPerPage, $sortingField, $sortingOrder, $user);
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
                                $order->getMaxSum(),
                                $order->getMinSum(),
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
                    'AcmeSecureBundle:Author:orders_new.html.twig', array('showWindow' => $showWindow, 'formBid' => $formBid->createView())
                );
            } elseif ($type == "favorite") {
                if ($request->isXmlHttpRequest()) {
                    $action = $request->request->get('action');
                    if ($action == 'unfavoriteOrder') {
                        $orderId = $request->request->get('orderId');
                        $type = "unfavorite";
                        $actionResponse = Helper::favoriteOrder($orderId, $user, $type);
                        return new Response(json_encode(array('action' => $actionResponse)));
                    } else {
                        $curPage = $request->request->get('page');
                        $rowsPerPage = $request->request->get('rows');
                        $countOrders = Helper::getCountOrdersForAuthorGrid();
                        $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                        $orders = Helper::getFavoriteOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user);
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
                    'AcmeSecureBundle:Author:orders_favorite.html.twig', array('showWindow' => $showWindow)
                );
            }
            elseif ($type == "bid") {
                if ($request->isXmlHttpRequest()) {
                    $action = $request->request->get('action');
                    if ($action == 'deleteBid') {
                        $numOrder = $request->request->get('numOrder');
                        $actionResponse = Helper::deleteAuthorBid($user, $numOrder);
                        return new Response(json_encode(array('action' => $actionResponse)));
                    } else {
                        $curPage = $request->request->get('page');
                        $rowsPerPage = $request->request->get('rows');
                        $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                        $user = Helper::getUserById($user->getId());
                        $orders = Helper::getBidOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user);
                        $countOrders = Helper::getCountBidOrdersForAuthorGrid($user);
                        $response = new Response();
                        $response->total = ceil($countOrders / $rowsPerPage);
                        $response->records = $countOrders;
                        $response->page = $curPage;
                        foreach($orders as $index => $order) {
                            $task = strip_tags($order[0]->getTask());
                            $task = stripcslashes($task);
                            $task = preg_replace("/&nbsp;/", "", $task);
                            if (strlen($task) >= 20) {
                                $task = Helper::getCutSentence($task, 45);
                            }
                            $dateBid = Helper::getMonthNameFromDate($orders[$index]['date_bid']->format("d.m.Y"));
                            $dateBid = $dateBid . "<br><span class='gridCellTime'>" . $orders[$index]['date_bid']->format("H:i") . "</span>";
                            $dateExpire = Helper::getMonthNameFromDate($order[0]->getDateExpire()->format("d.m.Y"));
                            $dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order[0]->getDateExpire()->format("H:i") . "</span>";
                            $response->rows[$index]['id'] = $order[0]->getNum();
                            $response->rows[$index]['cell'] = array(
                                $order[0]->getNum(),
                                $order[0]->getNum(),
                                $order[0]->getSubjectOrder()->getChildName(),
                                $order[0]->getTypeOrder()->getName(),
                                $order[0]->getTheme(),
                                $task,
                                $dateExpire,
                                $orders[$index]['curr_sum'],
                                $dateBid,
                                "",
                            );
                        }
                        return new JsonResponse($response);
                    }
                }
                return $this->render(
                    'AcmeSecureBundle:Author:orders_bid.html.twig', array('showWindow' => $showWindow)
                );
            }
            elseif ($type == "work") {
                if ($request->isXmlHttpRequest()) {
                    $user = Helper::getUserById($user->getId());
                    $curPage = $request->request->get('page');
                    $rowsPerPage = $request->request->get('rows');
                    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                    $orders = Helper::getWorkOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user, "getRecords");
                    $countOrders = Helper::getWorkOrdersForAuthorGrid(null, null, $user, "getCountRecords");
                    $response = new Response();
                    $response->total = ceil($countOrders / $rowsPerPage);
                    $response->records = $countOrders;
                    $response->page = $curPage;
                    foreach($orders as $index => $order) {
                        $task = strip_tags($order[0]->getTask());
                        $task = stripcslashes($task);
                        $task = preg_replace("/&nbsp;/", "", $task);
                        if (strlen($task) >= 20) {
                            $task = Helper::getCutSentence($task, 45);
                        }
                        $dateOrderExpire = $order[0]->getDateExpire();
                        $remaining = Helper::getDiffBetweenDates($dateOrderExpire);
                        $dateExpire = Helper::getMonthNameFromDate($order[0]->getDateExpire()->format("d.m.Y"));
                        //$remaining = (strtotime($dateOrderExpire->format("d.m.Y H:i")) - strtotime($dateNow->format("d.m.Y H:i")))/3600;
                        //$remaining = date_create($remaining);
                        //$remaining = (\DateTime::createFromFormat('d.m.Y', $dateOrderExpire->format("d.m.Y"))->diff(new \DateTime($dateNow->format("d.m.Y")))->days);
                        //var_dump($remaining);die;
                        //$dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order[0]->getDateExpire()->format("H:i") . "</span>";
                        $response->rows[$index]['id'] = $order[0]->getNum();
                        $codeStatusOrder = $order[0]->getStatusOrder()->getCode();
                        $response->rows[$index]['cell'] = array(
                            $order[0]->getNum(),
                            $order[0]->getNum(),
                            $order[0]->getSubjectOrder()->getChildName(),
                            $order[0]->getTypeOrder()->getName(),
                            $order[0]->getTheme(),
                            $task,
                            $dateExpire,
                            ($codeStatusOrder != "g" && $codeStatusOrder != "e") ? $remaining->format('%d дн. %h ч. %i мин.') : "",
                            $order[0]->getStatusOrder()->getName(),
                            $orders[$index]['curr_sum']
                        );
                    }
                    return new JsonResponse($response);
                }
                return $this->render(
                    'AcmeSecureBundle:Author:orders_work.html.twig', array('showWindow' => $showWindow)
                );
            }
        }
        else {
            return $this->render(
                'AcmeSecureBundle:Author:access_denied_orders.html.twig'
            );
        }
    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function orderAction(Request $request, $num)
    {
        if (false === $this->get('security.context')->isGranted('ROLE_AUTHOR')) {
            return new RedirectResponse($this->generateUrl('secure_author_index'));
            //throw new \Symfony\Component\Security\Core\Exception\AccessDeniedException [403];
            //throw new AccessException [500];
        }
        $postDataFormBid = $request->request->get('formBid');
        if ((is_numeric($num) && $num > 0 && !$request->isXmlHttpRequest()) || (is_numeric($num) && $num > 0 && $request->isXmlHttpRequest() && isset($postDataFormBid))) {
            $order = Helper::getOrderByNumForAuthor($num);
            if (!$order) {
                return new RedirectResponse($this->generateUrl('secure_author_index'));
            }
            //$userId = $this->get('security.context')->getToken()->getUser();
            //$user = Helper::getUserById($userId);
            $user = $this->get('security.context')->getToken()->getUser();
            $clientLink = Helper::getUserLinkProfile($order, "client", $this->container);
            $codeStatusOrder = $order->getStatusOrder()->getCode();
            if ($codeStatusOrder == 'w' || $codeStatusOrder == 'g' || $codeStatusOrder == 'e') {
                $filesOrder = Helper::getFilesForOrder($order);
                //$session = $request->getSession();
                //$session->set('curr_order', $order);
                //$session->set('curr_user', $user);
                //$session->save();
                $cancelRequests = Helper::getCancelRequestsByOrderForAuthor($order);
                $dateVerdict = Helper::getDateVerdict($order);
                if ($codeStatusOrder == 'e') {
                    $diffExpired = Helper::getDiffBetweenDates($order->getDateExpire());
                    $order->setDiffExpired($diffExpired);
                }
                $clientFiles = Helper::getFilesForOrder($order, 'client', $user);
                $arrayClientFiles = [];
                foreach($clientFiles as $file) {
                    $dir = dirname($_SERVER['SCRIPT_FILENAME']) . Helper::getAttachmentsUrl('client', $num) . $file->getName();
                    if (file_exists($dir)) {
                        $file->setUrl(Helper::getFullUrl() . Helper::getAttachmentsUrl('client', $num) . $file->getName());
                        $file->setThumbnailUrl(Helper::getThumbnailUrlFile($file->getName(), $order));
                        $arrayClientFiles[] = $file;
                    }
                }
                return $this->render(
                    'AcmeSecureBundle:Author:order_work.html.twig', array('order' => $order, 'client' => $clientLink, 'user' => $user, 'cancelRequests' => $cancelRequests, 'dateVerdict' => $dateVerdict, 'clientFiles' => $arrayClientFiles)
                );
            } elseif ($codeStatusOrder == 'f') {
                return $this->render(
                    'AcmeSecureBundle:Author:order_finish.html.twig', array('order' => $order, 'client' => $clientLink, 'user' => $user)
                );
            } else {
                $bidValidate = new BidFormValidate();
                $showDialogConfirmSelection = Helper::getClientSelectedBid($user, $order);
                $formBid = $this->createForm(new BidForm(), $bidValidate);
                if (is_numeric($num) && $num > 0 && $request->isXmlHttpRequest() && isset($postDataFormBid)) {
                    $formBid->handleRequest($request);
                    if ($formBid->isValid()) {
                        $postData = $request->request->get('formBid');
                        Helper::setAuthorBid($postData, $user, $order);
                        return new Response(json_encode(array('response' => 'valid')));
                    } else {
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
                return $this->render(
                    'AcmeSecureBundle:Author:order_select.html.twig', array('formBid' => $formBid->createView(), 'files' => $filesOrder, 'order' => $order, 'client' => $clientLink, 'bids' => "", 'showDialogConfirmSelection' => $showDialogConfirmSelection)
                );
            }
        }
        elseif (is_numeric($num) && $request->isXmlHttpRequest() && $num > 0) {
            //$cache = $this->get('winzou_cache.apc');
            $order = Helper::getOrderByNumForAuthor($num);
            $user = $this->get('security.context')->getToken()->getUser();
            //$lastId = $request->request->get('lastId');
            $bids = Helper::getAllAuthorsBidsForSelectedOrder($user, $order);
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
            if (isset($action)) {
                if ($action == 'deleteBid') {
                    $bidId = $request->request->get('bidId');
                    $actionResponse = Helper::deleteSelectedAuthorBid($bidId, $user, $order);
                    return new Response(json_encode(array('action' => $actionResponse)));
                } elseif ($action == 'confirmSelection' || $action == 'failSelection') {
                    $bidId = $request->request->get('bidId');
                    $mode = null;
                    if ($action == 'confirmSelection') {
                        $mode = 'confirm';
                    } elseif ($action == 'failSelection') {
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
                elseif ($action == 'getChats') {
                    /*if ($cache->contains('bar')) {
                        $messages = $cache->fetch('bar');
                        //var_dump($cache->getStats());die;
                    } else {
                        $messages = Helper::getChatMessages($user, $order, $lastId);
                        $cache->save('bar', $messages);
                    }*/
                    /*$a = new ApcCache();
                    if ($a->contains('bar')) {
                        $messages = $a->fetch('bar');
                        //var_dump($a->getStats());die;
                    } else {
                        $messages = Helper::getChatMessages($user, $order, $lastId);
                        $a->save('bar', $messages);
                    }*/
                    /*$messages = Helper::getChatMessages($user, $order, $lastId);
                    $messageArray = [];
                    foreach($messages as $index => $msg) {
                        $messageArray[$index]['id'] = $msg->getId();
                        $messageArray[$index]['msg'] = $msg->getMessage();
                        $messageArray[$index]['login'] = $msg->getUser()->getLogin();
                        $messageArray[$index]['date'] = $msg->getDateWrite()->format("d.m.Y");
                        $messageArray[$index]['time'] = $msg->getDateWrite()->format("H:i:s");
                        $messageArray[$index]['role_sender'] = $msg->getUser()->getUserRole()->getId();
                    }
                    return new Response(json_encode(array('messages' => $messageArray)));*/
                } elseif ($action == 'sendMessage') {
                    $message = $request->request->get('message');
                    //$order = Helper::getOrderByNumForAuthor($num);
                    //$session = $request->getSession();
                    //$user = $session->get('curr_user');
                    //$user = $this->get('security.context')->getToken()->getUser();
                    //$cache->deleteAll();
                    //var_dump($user);die;
                    $insertId = Helper::addNewWebchatMessage($user, $order, $message);
                    return new Response(json_encode(array('insertID' => $insertId)));
                } elseif ($action == 'getUsers') {
                    $users = Helper::getUsersForWebchat();
                    return new Response(json_encode(array('users' => $users)));
                } elseif ($action == 'createCancelRequest') {
                    //$user = $this->get('security.context')->getToken()->getUser();
                   // $order = Helper::getOrderByNumForAuthor($num);
                    $arrayPercent = array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100');
                    $comment = strip_tags($request->request->get('textarea-comment'), 'br');
                    $togetherApply = $request->request->get('check-together-apply');
                    $togetherApply = $togetherApply == "on" ? 1 : 0;
                    $selectPercent = $togetherApply == 0 ? (int)$request->request->get('select-percent') : 0;
                    $selectPercent = (is_numeric($selectPercent) && in_array($selectPercent, $arrayPercent) && $selectPercent >= 0) ? $selectPercent : 111;
                    $percent = ($togetherApply || $selectPercent == 111) ? "По обоюдному согласию с заказчиком." : $selectPercent . '%';
                    $cancelRequest = new CancelRequest();
                    $cancelRequest->setUserOrder($order);
                    $cancelRequest->setComment($comment);
                    $cancelRequest->setPercent($selectPercent);
                    $cancelRequest->setIsTogetherApply($togetherApply);
                    $cancelRequest->setCreator($user->getId());
                    Helper::createCancelOrderRequest($cancelRequest);
                    $dateCreate = $cancelRequest->getDateCreate()->format("d.m.Y H:i");
                    $dateVerdict = Helper::getDateVerdict($order);
                    return new Response(json_encode(array('response' => 'valid', 'dateCreate' => $dateCreate, 'percent' => $percent, 'dateVerdict' => $dateVerdict, 'comment' => wordwrap($comment, 60, "\n", true))));
                } elseif ($action == 'completeOrder') {
                    if ($order->getStatusOrder()->getCode() == 'w' || $order->getStatusOrder()->getCode() == 'e') {
                        $checkCompletedOrder = $request->request->get('checkCompletedOrder');
                        if ($checkCompletedOrder == 'true') {
                            $files = Helper::getFilesForOrder($order, 'author' , $user);
                            if (count($files) > 0) {
                                Helper::setOrderStatus($order, 'guarantee');
                                $statusOrder = $order->getStatusOrder()->getName();
                                $dateGuarantee = $order->getDateGuarantee()->format("d.m.Y H:i");
                                return new Response(json_encode(array('response' => 'valid', 'statusOrder' => $statusOrder, 'dateGuarantee' => $dateGuarantee)));
                            }
                        }
                    }
                    return new Response(json_encode(array('response' => null)));
                }
            }
        }
        else {
            return new RedirectResponse($this->generateUrl('secure_author_index'));
        }
        return $this->render(
            'AcmeSecureBundle:Author:order_select.html.twig', array('formBid' => "", 'files' => "", 'order' => $order, 'client' => "", 'bids' => $bids, 'showDialogConfirmSelection' => "")
        );
    }


    /**
     * @Template()
     * @return array
     */
    public function settingsAction(Request $request, $type)
    {
        if ($type == 'view') {
            /*if (false === $this->get('security.context')->isGranted('ROLE_AUTHOR')) {
                return new RedirectResponse($this->generateUrl('secure_author_index'));
            }*/
            $user = $this->get('security.context')->getToken()->getUser();
            $showWindow = false;
            if ($request->isXmlHttpRequest()) {
                $psId = $request->request->get('psId');
                $isUserPs = Helper::getUserPsByPsId($user, $psId);
                $response = 'null';
                if ($isUserPs) {
                    $response = 'valid';
                    Helper::deleteUserPs($psId);
                }
                return new Response(json_encode(array('response' => $response)));
            }
            $psValidate = new CreatePsFormValidate();
            $formCreatePs = $this->createForm(new AuthorCreatePsForm(), $psValidate);
            $formCreatePsCloned = clone $formCreatePs;
            $formCreatePs->handleRequest($request);
            $mailOptions = Helper::getMailOptions($user);
            $formMailOptions = $this->createForm(new AuthorMailOptionsForm(), $mailOptions);
            $formMailOptions->handleRequest($request);
            if ($request->isMethod('POST')) {
                if ($formCreatePs->get('add')->isClicked() || $formCreatePs->get('change')->isClicked()) {
                    if ($formCreatePs->isValid()) {
                        $postData = $request->request->get('formCreatePs');
                        if (isset($postData['fieldHiddenPsId']) && $postData['fieldHiddenPsId'] > 0 && $formCreatePs->get('change')->isClicked()) {
                            $psId = $postData['fieldHiddenPsId'];
                            $isUserPs = Helper::getUserPsByPsId($user, $psId);
                            if ($isUserPs) {
                                Helper::updateUserPs($psId, $postData);
                            }
                        } elseif ($formCreatePs->get('add')->isClicked()) {
                            Helper::addNewUserPs($user, $postData);
                        }
                        //var_dump($postData);die;
                        /*Helper::updateUserInfo($postData, $userInfo);
                        if (!$isAccessOrder) {
                            Helper::uploadAuthorFileInfo($user);
                        }*/
                        $showWindow = true;
                    } else{
                        $userPs = Helper::getUserPsByUser($user);
                        return $this->render(
                            'AcmeSecureBundle:Author:settings.html.twig', array('formCreatePs' => $formCreatePs->createView(), 'user' => $user, 'userPs' => $userPs, 'showWindow' => $showWindow)
                        );
                    };
                } elseif ($formMailOptions->get('save')->isClicked()) {
                    if ($formMailOptions->isValid()) {
                        $postData = $request->request->get('formMailOptions');
                        //var_dump($postData);die;
                        $fieldOptions = $postData['fieldOptions'];
                        Helper::updateMailOptions($user, $fieldOptions);
                    } else {}
                }
            }
            $userPs = Helper::getUserPsByUser($user);
            return $this->render(
                'AcmeSecureBundle:Author:settings.html.twig', array('formCreatePs' => $formCreatePsCloned->createView(), 'formMailOptions' => $formMailOptions->createView(), 'user' => $user, 'userPs' => $userPs, 'showWindow' => $showWindow)
            );
        }
    }

}
