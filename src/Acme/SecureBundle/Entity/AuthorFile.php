<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="author_file")
 */
class AuthorFile extends EntityRepository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_upload;

    /**
     * @ORM\Column(type="integer")
     */
    private $size;

    /**
     * @ORM\ManyToOne(targetEntity="AuthorStudy", inversedBy="link_author_file", cascade={"all"})
     * @ORM\JoinColumn(name="author_study_id", referencedColumnName="id")
     **/
    private $author_study;


    public function __construct(){

    }

    public function setName($name)
    {
        $this->name = $name;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getDateUpload()
    {
        return $this->date_upload;
    }

    public function setDateUpload($date)
    {
        $this->date_upload = $date;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function setSize($size)
    {
        $this->size = $size;
    }

    public function getAuthorStudy()
    {
        return $this->author_study;
    }

    public function setAuthorStudy($study)
    {
        $this->author_study = $study;
    }

}