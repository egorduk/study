<?php

namespace Acme\AuthBundle\Controller;

use Acme\AuthBundle\Form\Client\LoginForm;
use Acme\AuthBundle\Form\Client\RegForm;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\ExpressionLanguage\Parser;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Finder\Iterator\SortableIterator;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\Query;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Cache;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


class ClientController extends Controller
{
    private $tableSource = 'AcmeRssBundle:Source';
    private $tableNews = 'AcmeRssBundle:News';


    /**
     * @Template()
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $formLogin = $this->createForm(new LoginForm());
        $formLogin->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($formLogin->get('enter')->isClicked())
            {
                return new RedirectResponse($this->generateUrl('client_login'));
            }
        }

        return array('formLogin' => $formLogin->createView());
    }

    /**
     * @Template()
     * @return Response
     */
    public function loginAction(Request $request)
    {
        //$formLogin = $this->createForm(new LoginForm());
        //$formLogin->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            //if ($formLogin->get('enter')->isClicked())
            {
                $postData = $request->request->get('enter');
                $userLogin = $postData['fieldLogin'];
                $userPass = $postData['fieldPass'];

                //return new RedirectResponse($this->generateUrl('client_index'));
            }
            /*elseif($formLogin->get('reg')->isClicked())
            {
                return new RedirectResponse($this->generateUrl('client_reg'));
            }*/
        }

        //return array('formLogin' => $formLogin->createView());
    }


    /**
     * @Template()
     * @return array
     */
    public function regAction(Request $request)
    {
        $formReg = $this->createForm(new RegForm());
        $formReg->handleRequest($request);

        if ($request->isMethod('POST'))
        {
            if ($formReg->get('reg')->isClicked())
            {
                /*$postData = $request->request->get('reg');
                $userLogin = $postData['fieldLogin'];
                $userPass = $postData['fieldPass'];

                return new RedirectResponse($this->generateUrl('client_index'));*/
            }
        }

        return array('formReg' => $formReg->createView());
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
