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
}
