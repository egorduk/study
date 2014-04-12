<?php

namespace Acme\IndexBundle\Controller;

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
use Symfony\Component\PropertyAccess\Exception\AccessException;


class IndexController extends Controller
{

    /**
     * @Template()
     * @return Response
     */
    public function indexAction()
    {
        return array();
    }


    /**
     * @Template()
     * @return Array
     */
    public function rulesAction($type)
    {
        if ($type == "client")
        {
            return $this->render('AcmeIndexBundle:Index:clientRules.html.twig');
        }
        elseif ($type == "author")
        {
            return $this->render('AcmeIndexBundle:Index:authorRules.html.twig');
        }
        else
        {
            throw new AccessException();
        }
    }






    /**
    * @Template()
    * A function for viewing full description selected news by title-link
    * @param integer $id the id of the news
    * @return array
    */
    public function fullAction($id)
    {
        $text = '</br>';

        $news = $this->getDoctrine()
            ->getRepository($this->tableNews)
            ->find($id);

        $text .= '<strong><span id="rssTitle">' . $news->getTitle() . '</span></strong><br/><div id="rssPost">' . $news->getContent() . '</div><br/>';

        return array('text' => $text);
    }


    /**
     * @Template()
     * A function for viewing filtered news by tag from cloud tags
     * @param string $param selected tag from cloud tags
     * @return array
     */
    public function filterAction($param)
    {
        $em = $this->getDoctrine()->getManager();

        $news = $em->getRepository($this->tableNews)->createQueryBuilder('n')
            ->orWhere('n.title LIKE :filter1')
            ->orWhere('n.title LIKE :filter2')
            ->orWhere('n.title LIKE :filter3')
            ->setParameter('filter1', $param . ' %')
            ->setParameter('filter2', '% ' . $param)
            ->setParameter('filter3', '% ' . $param . ' %')
            ->getQuery()
            ->getResult();

        $text = '<br/>';

        foreach($news as $index=>$item)
        {
            $text .= '<strong><span id="rssTitle">' . $item->getTitle() . '</span></strong><br/><div id="rssPost">' . $item->getContent() . '</div><br/>';
        }

        return array('text' => $text);
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
