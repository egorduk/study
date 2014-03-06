<?php

namespace Acme\RssBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 * @ORM\Table(name="News")
 */
class News
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string", length=200)
     */
    protected $title;

    /**
     * @ORM\ManyToOne(targetEntity="Source", inversedBy="link", cascade={"all"})
     * @ORM\JoinColumn(name="source_id", referencedColumnName="id")
     **/
    protected $source;

    /**
     * @ORM\Column(type="string", length=250)
     */
    protected $content;


    public function setId($id)
    {
        $this->id = $id;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setSource($sourceId)
    {
        $this->source = $sourceId;
    }

    public function setContent($content)
    {
        $this->content = $content;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function getSource()
    {
        return $this->source;
    }

    public function getContent()
    {
        return $this->content;
    }
}


