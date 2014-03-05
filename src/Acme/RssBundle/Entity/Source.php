<?php

namespace Acme\RssBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="Source")
 */
class Source
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $name;

    /**
     * @ORM\Column(type="string", length=50)
     */
    protected $url;

    /**
     * @ORM\Column(type="integer")
     */
    protected $active;


    /**
     * @ORM\OneToMany(targetEntity="News", mappedBy="source")
     **/
    protected $link;



    public function __construct()
    {
        $this->link = new ArrayCollection();
    }

    public function getLink()
    {
        return $this->link;
    }




    public function setId($id)
    {
        $this->id = $id;
    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function setUrl($url)
    {
        $this->url = $url;
    }

    public function setActive($active)
    {
        $this->active = $active;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getActive()
    {
        return $this->active;
    }
}