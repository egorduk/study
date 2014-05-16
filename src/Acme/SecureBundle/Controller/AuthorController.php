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
            $isAuthorFile = $user->getIsAuthorFile();
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
                        if (!$isAuthorFile) {
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
        if ($type == "new") {
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 2;
            $user = Helper::getUserById($userId);
        }
        if ($type == "new") {
            if($request->isXmlHttpRequest()) {
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
                $orders = Helper::getAuthorOrdersForGrid($sOper, $sField, $sData, $firstRowIndex, $rowsPerPage, $sortingField, $sortingOrder);
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
                    $maxBid = 0;
                    $minBid = 0;
                    $myBid = 0;
                    $dateCreate = Helper::getMonthNameFromDate($order->getDateCreate()->format("d.m.Y"));
                    $dateCreate = $dateCreate . "<br><span class='gridCellTime'>" . $order->getDateCreate()->format("H:s") . "</span>";
                    $dateExpire = Helper::getMonthNameFromDate($order->getDateExpire()->format("d.m.Y"));
                    $dateExpire = $dateExpire . "<br><span class='gridCellTime'>" . $order->getDateExpire()->format("H:s") . "</span>";
                    $response->rows[$i]['id'] = $order->getId();
                    $response->rows[$i]['cell'] = array(
                        $order->getId(),
                        $order->getNum(),
                        $order->getSubjectOrder()->getChildName(),
                        $order->getTypeOrder()->getName(),
                        $order->getTheme(),
                        $task,
                        $dateExpire,
                        $maxBid,
                        $minBid,
                        $myBid,
                        $dateCreate,
                        ""
                    );
                    $i++;
                }
                return new JsonResponse($response);
            }
            $showWindow = false;
            return $this->render(
                'AcmeSecureBundle:Author:orders_new.html.twig', array('showWindow' => $showWindow)
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
            $userId = $this->get('security.context')->getToken()->getUser();
            $userId = 2;
            $user = Helper::getUserById($userId);
            $order = Helper::getOrderByNumForAuthor($num);
            $bid = Helper::getAuthorBid($user, $order);
            $filesOrder = Helper::getFilesForOrder($order);
            $bidValidate = new BidFormValidate();
            if ($bid) {
                $bidValidate->setDay($bid->getDay());
                $bidValidate->setSum($bid->getSum());
                $bidValidate->setIsClientDate($bid->getIsClientDate());
                $bidValidate->setComment($bid->getComment());
            }
            $showWindow = false;
            $formBid = $this->createForm(new BidForm(), $bidValidate);

            if($request->isXmlHttpRequest()) {
                $formBid->handleRequest($request);
                if ($formBid->isValid()) {
                    $postData = $request->request->get('formBid');
                    Helper::updateAuthorBid($postData, $user, $order);
                    return new Response(json_encode(array('response' => 'valid')));
                    //$showWindow = true;
                }
                else {
                    $errors = [];
                    $arrayResponse = [];
                    foreach ($formBid as $fieldName => $formField) {
                        $errors[$fieldName] = $formField->getErrors();
                    }
                    foreach ($errors as $index=>$error) {
                        if (isset($error[0])) {
                            $arrayResponse[$index] = $error[0]->getMessage();
                        }
                    }

                    return  new Response(json_encode(array('response' => $arrayResponse)));
                }
            }

            return $this->render(
                'AcmeSecureBundle:Author:order_select.html.twig', array('formBid' => $formBid->createView(), 'files' => $filesOrder, 'order' => $order, 'showWindow' => $showWindow)
            );
        }
        else {

        }
    }

}
