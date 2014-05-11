<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="author_study")
 */
class AuthorStudy extends EntityRepository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_start;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_finish;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="string")
     */
    protected $files_folder;

    /**
     * @ORM\ManyToOne(targetEntity="AuthorEdu", inversedBy="link_author_study", cascade={"all"})
     * @ORM\JoinColumn(name="author_edu_id", referencedColumnName="id")
     **/
    protected $edu;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_author_study", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\OneToMany(targetEntity="AuthorFile", mappedBy="author_study")
     **/
    private $link_author_file;


    public function __construct()
    {
    }

    public function setDateStart($date)
    {
        $format = 'd/m/Y';
        $this->date_start = Helper::getFormatDateForInsert($date, $format);
        $this->link_author_file = new \Doctrine\Common\Collections\ArrayCollection();
    }

    public function getDateStart()
    {
        return $this->date_start;
    }

    public function setDateFinish($date)
    {
        $format = 'd/m/Y';
        $this->date_finish = Helper::getFormatDateForInsert($date, $format);
    }

    public function getDateFinish()
    {
        return $this->date_finish;
    }

    public function setFilesFolder($folder)
    {
        $this->files_folder = $folder;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setAuthorEdu($edu)
    {
        $this->edu = $edu;
    }

    public function getAuthorEdu()
    {
        return $this->edu;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

}