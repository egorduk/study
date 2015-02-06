<?php

namespace Acme\AdminBundle\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Helper\Helper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\JsonResponse;


class AdminController extends Controller
{
    /**
     * @Template()
     * @return array
     */
    public function indexAction(Request $request)
    {
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
    }


    /**
     * @Template()
     * @return array|RedirectResponse
     */
    public function orderAction(Request $request)
    {
        if ($request->isXmlHttpRequest()) {
            die('dfgdfgd');
            $mode = $request->request->get('mode');
            if (isset($mode)) {
                if ($mode == 'attemptWriteEmail') {
                    die('dfgdfgd');
                } elseif ($mode == '') {
                    die('none mode');
                }
            }
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
