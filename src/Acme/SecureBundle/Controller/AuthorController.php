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
use Acme\SecureBundle\Entity\Author\AuthorProfileFormValidate;
use Acme\SecureBundle\Form\Author\AuthorProfileForm;


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
            $userId = 2;
            $user = Helper::getUserById($userId);
            $userInfo = $user->getUserInfo();
            $showWindow = false;
        }

        if ($type == "view") {
            return array('formProfile' =>'', 'user' => $user, 'userInfo' => $userInfo, 'showWindow' => $showWindow);
        }
        elseif ($type == "edit") {
            $session = $request->getSession();
            $sessionFolderFiles = $session->get("folderFiles");
            if (isset($sessionFolderFiles)) {
                $folderFiles = $sessionFolderFiles;
            }
            else {
                $folderFiles = $userId;
                $session->set("folderFiles", $folderFiles);
                $session->save();
            }
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
                        $showWindow = true;
                    }
                }
            }

            return array('formProfile' => $formProfile->createView(), 'user' => '', 'userInfo' => $userInfo, 'showWindow' => $showWindow, 'folderFiles' => $folderFiles);
        }
        else
        {
            return new RedirectResponse($this->generateUrl('secure_author_index'));
        }
    }


    public function uploadAction()
    {
        $editId = $this->getRequest()->get('editId');
        $fileName = $this->getRequest()->get('file');

        //var_dump(Helper::getUploadMaxFile());

        if (preg_match('/^\d+$/', $editId))
        {
            if ($fileName){
                $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'author/' . $editId, 'action' => 'delete'));
            }
            else{
                $this->get('punk_ave.file_uploader')->handleFileUpload(array('folder' => 'author/' . $editId));
            }
        }

        return new Response(json_encode(array('action' => 'false')));
    }

}
