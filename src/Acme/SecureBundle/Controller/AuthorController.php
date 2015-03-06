<?php

namespace Acme\SecureBundle\Controller;

use Acme\SecureBundle\Entity\Author\MailOptionsFormValidate;
use Acme\SecureBundle\Entity\Author\OutputPsFormValidate;
use Acme\SecureBundle\Entity\CancelRequestFormValidate;
use Acme\SecureBundle\Form\Author\AuthorCreatePsForm;
use Acme\SecureBundle\Entity\Author\CreatePsFormValidate;
use Acme\SecureBundle\Entity\CancelRequest;
use Acme\SecureBundle\Form\Author\AuthorMailOptionsForm;
use Acme\SecureBundle\Form\Author\OutputPsForm;
use Acme\SecureBundle\Form\CancelRequestForm;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\ArrayCache;
use Doctrine\Common\Cache\MemcachedCache;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Csrf\CsrfProvider\CsrfProviderAdapter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\PropertyAccess\Exception\AccessException;
use Symfony\Component\Security\Core\SecurityContext;
use Helper\Helper;
use Acme\SecureBundle\Entity\Author\AuthorProfileFormValidate;
use Acme\SecureBundle\Form\Author\AuthorProfileForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Acme\SecureBundle\Entity\Author\BidFormValidate;
use Acme\SecureBundle\Form\Author\BidForm;
use Symfony\Component\Security\Csrf\CsrfToken;
use Zend\I18n\Validator\DateTime;


class AuthorController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
        $user = $this->getUser();
        $session = $request->getSession();
        $sessionCreated = $session->getMetadataBag()->getCreated();
        $sessionLifeTime = $session->getMetadataBag()->getLifetime();
        $whenLogin = Helper::getDateFromTimestamp($sessionCreated, "d/m/Y H:i");
        $sessionRemaining = $sessionCreated + $sessionLifeTime;
        $nowTimestamp = strtotime("now");
        $sessionRemaining = $sessionRemaining - $nowTimestamp;
        $sessionRemaining = Helper::getDateFromTimestamp($sessionRemaining, "i:s");
        $user = Helper::getUserAvatar($user);
        return array('user' => $user, 'whenLogin' => $whenLogin, 'remainingTime' => $sessionRemaining);
    }


    /**
     * @Template()
     * @return array
     */
    public function profileAction(Request $request, $type)
    {
        if ($type == "view" || $type == "edit") {
            $user = $this->getUser();
            //var_dump($user->getId());
            //$userInfo = $user->getUserInfo();
            //$avatar = Helper::getUserAvatar($user);
            $showWindow = false;
        } else {
            return new RedirectResponse($this->generateUrl('secure_author_index'));
        }
        if ($type == "edit") {
            $isAccessOrder = $user->getIsAccessOrder();
            $profileValidate = new AuthorProfileFormValidate();
            $profileValidate->setIcq($user->getUserInfo()->getIcq());
            $profileValidate->setSkype($user->getUserInfo()->getSkype());
            $profileValidate->setMobilePhone($user->getUserInfo()->getMobilePhone());
            $profileValidate->setStaticPhone($user->getUserInfo()->getStaticPhone());
            $profileValidate->setUsername($user->getUserInfo()->getUsername());
            $profileValidate->setSurname($user->getUserInfo()->getSurname());
            $profileValidate->setLastname($user->getUserInfo()->getLastname());
            $profileValidate->setCountry($user->getUserInfo()->getCountry()->getCode());
            $avatarOption = Helper::getAvatarOption($user);
            $profileValidate->setAvatarOption($avatarOption);
            $formProfile = $this->createForm(new AuthorProfileForm(), $profileValidate);
            $formProfile->handleRequest($request);
            if ($request->isMethod('POST')) {
                if ($formProfile->get('save')->isClicked()) {
                    if ($formProfile->isValid()) {
                        $postData = $request->request->get('formProfile');
                        Helper::updateUserInfo($postData, $user->getUserInfo());
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
        $user = Helper::getUserAvatar($user);
        //var_dump($avatar);die;
        return array('formProfile' => (isset($formProfile)?$formProfile->createView():null), 'user' => $user, 'showWindow' => $showWindow);
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
                    //$this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/orders/' . $orderNum));
                } else {
                    $user = $this->getUser();
                    $session = new Session();
                    $session->set('user', $user);
                    $session->set('order', $orderNum);
                    $session->save();
                    $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'attachments/orders/' . $orderNum . '/author',
                        'mode' => 'order',
                        'num_order' => $orderNum
                        /*'max_number_of_files' => 10/*, 'max_file_size' => 4*/));
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
        $user = $this->getUser();
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
                            $orderId = $request->request->get('orderId');
                            $order = Helper::getOrderById($orderId);
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
                        $actionResponse = Helper::favoriteOrder($orderId, $user, 'unfavorite');
                        return new Response(json_encode(array('action' => $actionResponse)));
                    } else {
                        $curPage = $request->request->get('page');
                        $rowsPerPage = $request->request->get('rows');
                        $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                        $countOrders = Helper::getFavoriteOrdersForAuthorGrid(null, null, $user, 'count');
                        $orders = Helper::getFavoriteOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user, 'orders');
                        $response = new Response();
                        $response->total = ceil($countOrders / $rowsPerPage);
                        $response->records = $countOrders;
                        $response->page = $curPage;
                        foreach($orders as $index => $order) {
                            $userOrder = $order->getUserOrder();
                            $task = Helper::getOrderTask($userOrder->getTask());
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
                            $task = Helper::getOrderTask($order[0]->getTask());
                            $dateBid = Helper::getMonthNameFromDate($orders[$index]['date_bid']->format("d.m.Y"));
                            $dateBid = $dateBid . "<br><span class='gridCellTime'>" . $orders[$index]['date_bid']->format("H:i") . "</span>";
                            $dateExpire = Helper::getMonthNameFromDate($order[0]->getDateExpire()->format("d.m.Y"));
                            $dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order[0]->getDateExpire()->format("H:i") . "</span>";
                            $response->rows[$index]['id'] = $order[0]->getId();
                            $response->rows[$index]['cell'] = array(
                                $order[0]->getId(),
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
            } elseif ($type == "work") {
                if ($request->isXmlHttpRequest()) {
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
                        $dateExpire = Helper::getMonthNameFromDate($order[0]->getDateExpire()->format("d.m.Y H:i"));
                        //$remaining = (strtotime($dateOrderExpire->format("d.m.Y H:i")) - strtotime($dateNow->format("d.m.Y H:i")))/3600;
                        //$remaining = date_create($remaining);
                        //$remaining = (\DateTime::createFromFormat('d.m.Y', $dateOrderExpire->format("d.m.Y"))->diff(new \DateTime($dateNow->format("d.m.Y")))->days);
                        //var_dump($remaining);die;
                        //$dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order[0]->getDateExpire()->format("H:i") . "</span>";
                        $response->rows[$index]['id'] = $order[0]->getId();
                        $codeStatusOrder = $order[0]->getStatusOrder()->getCode();
                        $response->rows[$index]['cell'] = array(
                            $order[0]->getId(),
                            $order[0]->getNum(),
                            $order[0]->getSubjectOrder()->getChildName(),
                            $order[0]->getTypeOrder()->getName(),
                            $order[0]->getTheme(),
                            $task,
                            $dateExpire,
                            ($codeStatusOrder == "w") ? Helper::getRemainingTime($order, 'work') : (($codeStatusOrder == "g") ? Helper::getRemainingTime($order, 'guarantee') : ""),
                            $order[0]->getStatusOrder()->getName(),
                            $orders[$index]['curr_sum']
                        );
                    }
                    return new JsonResponse($response);
                }
                return $this->render(
                    'AcmeSecureBundle:Author:orders_work.html.twig', array('showWindow' => $showWindow)
                );
            } elseif ($type == "finish") {
                if ($request->isXmlHttpRequest()) {
                    $curPage = $request->request->get('page');
                    $rowsPerPage = $request->request->get('rows');
                    $firstRowIndex = $curPage * $rowsPerPage - $rowsPerPage;
                    $orders = Helper::getFinishOrdersForAuthorGrid($firstRowIndex, $rowsPerPage, $user, "getRecords");
                    $countOrders = Helper::getFinishOrdersForAuthorGrid(null, null, $user, "getCountRecords");
                    $response = new Response();
                    $response->total = ceil($countOrders / $rowsPerPage);
                    $response->records = $countOrders;
                    $response->page = $curPage;
                    foreach($orders as $index => $order) {
                        $task = Helper::getOrderTask($order->getTask());
                        $dateComplete = Helper::getMonthNameFromDate($order->getDateComplete()->format("d.m.Y H:i"));
                        $response->rows[$index]['id'] = $order->getNum();
                        $response->rows[$index]['cell'] = array(
                            $order->getNum(),
                            $order->getNum(),
                            $order->getSubjectOrder()->getChildName(),
                            $order->getTypeOrder()->getName(),
                            $order->getTheme(),
                            $task,
                            $dateComplete,
                            $order->getIsDelay(),
                            $order->getClientDegree(),
                            $order->getClientComment(),
                        );
                    }
                    return new JsonResponse($response);
                }
                return $this->render(
                    'AcmeSecureBundle:Author:orders_finish.html.twig'
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
        //$geoip = $this->get('maxmind.geoip')->lookup('86.57.193.27');
        //var_dump($geoip->getCountryCode());die;
        $postDataFormBid = $request->request->get('formBid');
        $postDataFormCancelRequest = $request->request->get('formCancelRequest');

        $user = $this->getUser();
        if (Helper::isCorrectOrder($num) && (!$request->isXmlHttpRequest() || ($request->isXmlHttpRequest() && (isset($postDataFormBid) || isset($postDataFormCancelRequest))))) {
            $order = Helper::getOrderByNumForAuthor($num, $user);
            if (!$order) {
                return new RedirectResponse($this->generateUrl('secure_author_index'));
            }
            $clientLink = Helper::getUserLinkProfile($order, "client", $this->container);
            $codeStatusOrder = $order->getStatusOrder()->getCode();
            if ($codeStatusOrder == 'w' || $codeStatusOrder == 'g' || $codeStatusOrder == 'e' || $codeStatusOrder == 'ca') {
                $cancelRequestValidate = new CancelRequestFormValidate();
                $formCancelRequest = $this->createForm(new CancelRequestForm(), $cancelRequestValidate);
                $cancelRequests = Helper::getCancelRequestsByOrder($order, $user);
                if (count($cancelRequests) > 0) {
                    $dateVerdict = Helper::getDateVerdict($order);
                    $isVerdict = Helper::isVerdictCancelRequest($cancelRequests);
                    if ($isVerdict) {
                        $order = Helper::setOrderStatus($order, 'cancel');
                        $codeStatusOrder = $order->getStatusOrder()->getCode();
                    }
                } else {
                    $cancelRequests = null;
                }
                if ($request->isXmlHttpRequest() && isset($postDataFormCancelRequest)) {
                    $formCancelRequest->handleRequest($request);
                    if ($request->isMethod('POST')) {
                        $action = $request->request->get('action');
                        if ($action == 'removeCancelRequest') {
                            $csrfProvider = new CsrfProviderAdapter($this->get('form.csrf_provider'));
                            $csrfToken = new CsrfToken('formCancelRequest', $postDataFormCancelRequest['_token']);
                            if ($csrfProvider->isTokenValid($csrfToken)) {
                                $response = Helper::removeCancelRequest($user, $cancelRequests);
                                return new Response(json_encode(array('response' => $response)));
                            } else {
                                return new Response(json_encode(array('response' => 'error')));
                            }
                        } elseif ($action == 'createCancelRequest') {
                            if ($formCancelRequest->isValid()) {
                                $preparedData = Helper::prepareCancelRequestData($postDataFormCancelRequest);
                                $cancelRequest = Helper::createCancelOrderRequest($order, $preparedData, $user);
                                $comment = wordwrap($preparedData['comment'], 60, "\n", true);
                                $textPercent = $preparedData['textPercent'];
                                $dateVerdict = Helper::getDateVerdict($order);
                                $btnRemoveCancelRequest = Helper::getBtnRemoveCancelRequest();
                                $obj = array('comment' => $comment, 'percent' => $textPercent, 'dateVerdict' => $dateVerdict , 'dateCreate' => $cancelRequest->getDateCreate()->format('d.m.y H:i:s'));
                                return new Response(json_encode(array('response' => 'valid', 'obj' => $obj, 'btnRemoveCancelRequest' => $btnRemoveCancelRequest)));
                            } else {
                                $arrayResponse = Helper::getFormErrors($formCancelRequest);
                                return new Response(json_encode(array('response' => $arrayResponse)));
                            }
                        }
                    }
                }
                if ($codeStatusOrder == 'e') {
                    $dateExpire = $order->getDateExpire();
                    $order->setDiffExpire(time() - strtotime($dateExpire->format("d.m.Y H:i:s")));
                }
                if ($codeStatusOrder == 'cl') {
                    return $this->render(
                        'AcmeSecureBundle:Author:order_finish.html.twig', array('formCancelRequest' => $formCancelRequest->createView(), 'order' => $order, 'client' => $clientLink, 'user' => $user, 'cancelRequests' => $cancelRequests)
                    );
                }
                $clientFiles = Helper::getOrderFiles($order, 'client', $user);
                //var_dump($clientFiles);die;
                if ($codeStatusOrder == 'w') {
                    $dateExpire = $order->getDateExpire();
                    $diffWork = strtotime($dateExpire->format("d.m.Y H:i:s")) - time();
                    $order->setDiffWork($diffWork);
                }
                $obj = [];
                $obj['client'] = $clientLink;
                $obj['dateVerdict'] = $dateVerdict;
                $obj['clientFiles'] = $clientFiles;
                $obj['cancelRequests'] = $cancelRequests;
                return $this->render(
                    'AcmeSecureBundle:Author:order_work.html.twig', array('formCancelRequest' => $formCancelRequest->createView(), 'order' => $order, 'user' => $user, 'obj' => $obj)
                );
            } elseif ($codeStatusOrder == 'f' || $codeStatusOrder == 'cl') {
                return $this->render(
                    'AcmeSecureBundle:Author:order_finish.html.twig', array('order' => $order, 'client' => $clientLink, 'user' => $user)
                );
            } elseif ($codeStatusOrder == 'sa') {
                $bidValidate = new BidFormValidate();
                $showDialogConfirmSelection = Helper::getClientSelectedBid($user, $order);
                $formBid = $this->createForm(new BidForm(), $bidValidate);
                $clientFiles = Helper::getOrderFiles($order, 'client', $user);
                //var_dump($clientFiles);die;
                if (Helper::isCorrectOrder($num) && $request->isXmlHttpRequest() && isset($postDataFormBid)) {
                    $formBid->handleRequest($request);
                    if ($formBid->isValid()) {
                        $postData = $request->request->get('formBid');
                        Helper::setAuthorBid($postData, $user, $order);
                        return new Response(json_encode(array('response' => 'valid')));
                    }
                    $arrayResponse = Helper::getFormErrors($formBid);
                    return new Response(json_encode(array('response' => $arrayResponse)));
                }
                $bids = Helper::getMaxMinOrderBids($order, $user);
                $obj = [];
                $obj['clientLink'] = $clientLink;
                $obj['clientFiles'] = $clientFiles;
                $obj['confirmSelection'] = $showDialogConfirmSelection;
                $obj['bids'] = $bids[0];
                //var_dump($bids[0]);die;
                $obj['userLogin'] = $user->getLogin();
                $obj['userId'] = $user->getId();
                $obj['client']['login'] = $order->getUser()->getLogin();
                //$obj['client']['status'] = $order->getUser()->getIsActive();
                $obj['client']['status'] = 1;
                $obj['client']['id'] = 1;
                $shortTask = Helper::getCutSentence($order->getTask(), 200, ' подробнее...');
                $order->setShortTask($shortTask);
                return $this->render(
                    'AcmeSecureBundle:Author:order_select.html.twig', array('formBid' => $formBid->createView(), 'order' => $order, 'obj' => $obj)
                );
            }
        }
        elseif (Helper::isCorrectOrder($num) && $request->isXmlHttpRequest()) {
            //$cache = $this->get('winzou_cache.apc');
            $action = $request->request->get('action');
            if (isset($action)) {
                $user = $this->getUser();
                $order = Helper::getOrderByNumForAuthor($num, $user);
                if ($action == 'getAuthorBids') {
                    $response = new Response();
                    $bids = Helper::getAllAuthorsBidsForSelectedOrder($user, $order);
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
                } elseif ($action == 'deleteBid') {
                    $bidId = $request->request->get('bidId');
                    $actionResponse = Helper::deleteSelectedAuthorBid($bidId, $user, $order);
                    return new Response(json_encode(array('action' => $actionResponse)));
                } elseif ($action == 'refreshBid') {
                    $bidId = $request->request->get('bidId');
                    $actionResponse = Helper::refreshSelectedAuthorBid($bidId, $user, $order);
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
                    /*$arrayPercent = array('0', '10', '20', '30', '40', '50', '60', '70', '80', '90', '100');
                    $comment = strip_tags($request->request->get('textarea-comment'), 'br');
                    $togetherApply = $request->request->get('check-together-apply');
                    $togetherApply = $togetherApply == "on" ? 1 : 0;
                    $selectPercent = $togetherApply == 0 ? (int)$request->request->get('select-percent') : 0;
                    $selectPercent = (is_numeric($selectPercent) && in_array($selectPercent, $arrayPercent) && $selectPercent >= 0) ? $selectPercent : 111;
                    $textPercent = ($togetherApply || $selectPercent == 111) ? "По обоюдному согласию с заказчиком." : $selectPercent . '%';
                    $cancelRequest = Helper::createCancelOrderRequest($order, $comment, $selectPercent, $togetherApply, $user);
                    $comment = wordwrap($comment, 60, "\n", true);
                    $cancelRequest->setTextPercent($textPercent);
                    $cancelRequest->setComment($comment);*/
                    //$dateCreate = $cancelRequest->getDateCreate()->format("d.m.Y H:i");
                    //$dateVerdict = Helper::getDateVerdict($order);
                    //return new Response(json_encode(array('response' => 'valid', 'cancelRequest' => $cancelRequest, 'dateVerdict' => $dateVerdict)));
                } elseif ($action == 'completeOrder') {
                    if ($order->getStatusOrder()->getCode() == 'w' || $order->getStatusOrder()->getCode() == 'e') {
                        $checkCompletedOrder = $request->request->get('checkCompletedOrder');
                        if (filter_var($checkCompletedOrder, FILTER_VALIDATE_BOOLEAN)) {
                            $files = Helper::getOrderFiles($order, 'author', $user);
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
        /*return $this->render(
            'AcmeSecureBundle:Author:order_select.html.twig', array('formBid' => "", 'files' => "", 'order' => $order, 'client' => "", 'bids' => $bids, 'showDialogConfirmSelection' => "")
        );*/
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
            $user = $this->getUser();
            $showWindow = $response = false;
            if ($request->isXmlHttpRequest() && $mode = $request->request->get('mode')) {
                if ($mode == 'deletePs') {
                    $psId = $request->request->get('psId');
                    $isUserPs = Helper::getUserPsByPsId($user, $psId);
                    if ($isUserPs) {
                        $response = true;
                        Helper::deleteUserPs($psId);
                    }
                } elseif ($mode == 'outputPs') {
                    $psId = $request->request->get('psId');
                    $isUserPs = Helper::getUserPsByPsId($user, $psId);
                    if ($isUserPs) {
                        $response = true;
                        //Helper::deleteUserPs($psId);
                    }
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
                    } else {
                        $userPs = Helper::getUserPsByUser($user);
                        return $this->render(
                            'AcmeSecureBundle:Author:settings.html.twig', array('formCreatePs' => $formCreatePs->createView(), 'user' => $user, 'userPs' => $userPs, 'formMailOptions' => $formMailOptions->createView(), 'showWindow' => $showWindow)
                        );
                    };
                } elseif ($formMailOptions->get('save')->isClicked()) {
                    if ($formMailOptions->isValid()) {
                        $postData = $request->request->get('formMailOptions');
                        //var_dump($postData);die;
                        $fieldOptions = $postData['fieldOptions'];
                        Helper::updateMailOptions($user, $fieldOptions);
                    }
                }
            }
            $userPs = Helper::getUserPsByUser($user);
            return $this->render(
                'AcmeSecureBundle:Author:settings.html.twig', array('formCreatePs' => $formCreatePsCloned->createView(), 'formMailOptions' => $formMailOptions->createView(), 'user' => $user, 'userPs' => $userPs, 'showWindow' => $showWindow)
            );
        }
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

            $filePath = $basePath . 'attachments/orders/' . $num . '/author/' . $filename;
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

    /**
     * @Template()
     * @return array
     */
    public function viewClientAction(Request $request, $id)
    {
        if (true === $this->get('security.context')->isGranted('ROLE_AUTHOR') && !$request->isXmlHttpRequest() && is_numeric($id)) {
            $user = Helper::getUserById($id);
            if ($user) {
                $orders = Helper::getClientTotalOrders($user, 'totalOrders');
                $countCanceledOrders = 0;
                if ($orders) {
                    foreach($orders as $order) {
                        if ($order->getStatusOrder()->getCode() == 'cl') {
                            $countCanceledOrders++;
                        }
                    }
                }
                $pathAvatar = Helper::getFullPathToAvatar($user);
                $clientAvatar = "<img src='$pathAvatar' align='middle' alt='client_avatar' width='110px' height='auto' class='thumbnail'>";
                $obj = [];
                $obj['countTotalOrders'] = count($orders);
                $obj['countCanceledOrders'] = $countCanceledOrders;
                $obj['clientAvatar'] = $clientAvatar;
                $obj['mode'] = 'clientView';
                $obj['clientId'] = $id;
                return $this->render(
                    'AcmeSecureBundle:Author:view_client.html.twig', array('user' => $user, 'obj' => $obj)
                );
            } else {}
        } elseif ($request->isXmlHttpRequest() && true === $this->get('security.context')->isGranted('ROLE_AUTHOR') && is_numeric($id)) {
            $mode = $request->request->get('mode');
            //var_dump($mode);die;
            if ($mode == 'totalOrders') {
                $user = Helper::getUserById($id);
                if ($user) {
                    $response = new Response();
                    $orders = Helper::getClientTotalOrders($user, $mode);
                    if ($orders) {
                        foreach($orders as $index => $order) {
                            $response->rows[$index]['id'] = $order->getId();
                            $response->rows[$index]['cell'] = array(
                                $order->getId(),
                                $order->getNum(),
                                $order->getSubjectOrder()->getChildName(),
                                $order->getTypeOrder()->getName(),
                                $order->getTheme(),
                                $order->getDateCreate()->format("d.m.Y H:i")
                            );
                        }
                        return new JsonResponse($response);
                    }
                    return new JsonResponse(null);
                } else {}
            } elseif ($mode == 'totalOrdersCompleted') {
                $client = Helper::getUserById($id);
                //var_dump($client);die;
                if ($client) {
                    $response = new Response();
                    $author = $this->getUser();
                    $orders = Helper::getAuthorTotalCompletedOrdersForClient($client, $author);
                    //var_dump(count($orders));die;
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
            } elseif ($mode == 'totalTypeOrders') {
                $client = Helper::getUserById($id);
                if ($client) {
                    $orders = Helper::getClientTotalOrders($client, $mode);
                    //var_dump(count($orders));die;
                    if ($orders) {
                        $arrayTypeOrders = [];
                        foreach($orders as $index => $order) {
                            $arrayTypeOrders[$index] = $order->getTypeOrder()->getCode();
                        }
                        $arrayTypeOrders = Helper::getClientTotalTypeOrdersForChart($arrayTypeOrders);
                        return new Response(json_encode(array('response' => true, 'typeOrders' => $arrayTypeOrders)));
                    }
                    return new Response(json_encode(array('response' => false)));
                } else {}
            }
        }
        return null;

        /*elseif ($mode == "info" && true === $this->get('security.context')->isGranted('ROLE_CLIENT')) {
            $user = Helper::getUserById($id);
            return $this->render(
                'AcmeSecureBundle:Client:action_info.html.twig', array('mode' => 'clientView', 'user' => $user)
            );
        }*/
    }

    /**
     * @Template()
     * @return array
     */
    public function outputMoneyAction(Request $request)
    {
        $user = $this->getUser();
        $outputPsValidate = new OutputPsFormValidate($user);
        $outputPsForm = new OutputPsForm();
        $formOutputPs = $this->createForm($outputPsForm, $outputPsValidate);
        $countUserPs = $outputPsForm->getCountUserPs();
        if ($countUserPs) {
            if ($request->isXmlHttpRequest()) {
                $formOutputPs->handleRequest($request);
                $postData = $request->request->get('formOutputPs');
                if ($formOutputPs->isValid()) {
                    $response = Helper::createMoneyOutput($user, $postData);
                    return new Response(json_encode(array('response' => $response)));
                }
                $arrayResponse = Helper::getFormErrors($formOutputPs);
                return new Response(json_encode(array('formError' => $arrayResponse)));
            }
            return $this->render(
                'AcmeSecureBundle:Author:output_money.html.twig', array('formOutputPs' => $formOutputPs->createView(), 'user' => $user)
            );
        }
        return $this->render(
            'AcmeSecureBundle:Author:output_money.html.twig', array('formOutputPs' => null, 'user' => $user)
        );
    }

}
