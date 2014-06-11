<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Helper\Helper;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="User_Order")
 */
class UserOrder extends EntityRepository
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="integer")
     */
    private $num;

    /**
     * @ORM\Column(type="string")
     */
    private $theme;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_expire;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_create;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_edit;

    /**
     * @ORM\Column(type="string")
     */
    private $task;

    /**
     * @ORM\Column(type="string")
     */
    private $originality;

    /**
     * @ORM\Column(type="string")
     */
    private $count_sheet;

    /**
     * @ORM\ManyToOne(targetEntity="SubjectOrder", inversedBy="link_subject", cascade={"all"})
     * @ORM\JoinColumn(name="subject_order_id", referencedColumnName="id")
     **/
    private $subject_order;

    /**
     * @ORM\ManyToOne(targetEntity="TypeOrder", inversedBy="link_type_order", cascade={"all"})
     * @ORM\JoinColumn(name="type_order_id", referencedColumnName="id")
     **/
    private $type_order;

    /**
     * @ORM\ManyToOne(targetEntity="StatusOrder", inversedBy="link_status_order", cascade={"all"})
     * @ORM\JoinColumn(name="status_order_id", referencedColumnName="id")
     **/
    private $status_order;

    /**
     * @ORM\ManyToOne(targetEntity="Acme\AuthBundle\Entity\User", inversedBy="link_user_order", cascade={"all"})
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     **/
    protected $user;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_show_author;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_favorite;

    /**
     * @ORM\Column(type="integer")
     */
    private $is_show_client;

    /**
     * @ORM\Column(type="string")
     */
    private $files_folder;

    /**
     * @ORM\OneToMany(targetEntity="UserBid", mappedBy="user_order")
     **/
    private $link_user_order;

    /**
     * @ORM\OneToMany(targetEntity="AuctionBid", mappedBy="user_order")
     **/
    private $link_auction_user_order;

    /**
     * @ORM\OneToMany(targetEntity="AuctionBid", mappedBy="user")
     **/
    private $link_auction_user;

    /**
     * @ORM\OneToMany(targetEntity="OrderFile", mappedBy="user_order")
     **/
    private $link_order_file;

    /**
     * @ORM\OneToMany(targetEntity="FavoriteOrder", mappedBy="user")
     **/
    private $link_favorite_user_order;

    private $count_bids;


    public function __construct($container){
        $this->date_create = new \DateTime();
        $this->date_edit = new \DateTime();
        $this->is_show_author = 0;
        $this->is_show_client = 1;
        $this->is_favorite = 0;
        $this->files_dir = "";
        $em = $container->get('doctrine')->getManager();
        $query = $em->getRepository('AcmeSecureBundle:UserOrder')->createQueryBuilder('s');
        $query->select('MAX(s.num) AS max_num');
        $data = $query->getQuery()->getResult();
        $num = $data[0]['max_num'];
        $num++;
        $this->num = $num;
        $this->link_user_order = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_order_file = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_auction_user_order = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_auction_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_favorite_user_order = new \Doctrine\Common\Collections\ArrayCollection();
        $this->count_bids = 0;
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setFilesFolder($folder)
    {
        $this->files_folder = $folder;
    }

    public function getFilesFolder()
    {
        return $this->files_folder;
    }

    public function setDateExpire($date)
    {
        $format = 'd/m/Y';
        $this->date_expire = Helper::getFormatDateForInsert($date, $format);
    }

    public function getDateExpire()
    {
        return $this->date_expire;
    }

    public function setDateCreate($date)
    {
        $format = 'd/m/Y';
        $this->date_create = Helper::getFormatDateForInsert($date, $format);
    }

    public function setTask($task)
    {
        $this->task = $task;
    }

    public function getTask()
    {
        return $this->task;
    }

    public function setOriginality($originality)
    {
        $this->originality = $originality;
    }

    public function getOriginality()
    {
        return $this->originality;
    }

    public function setCountSheet($count)
    {
        $this->count_sheet = $count;
    }

    public function getCountSheet()
    {
        return $this->count_sheet;
    }

    public function getSubjectOrder()
    {
        return $this->subject_order;
    }

    public function setSubjectOrder($subject)
    {
        $this->subject_order = $subject;
    }

    public function getTypeOrder()
    {
        return $this->type_order;
    }

    public function setTypeOrder($type)
    {
        $this->type_order = $type;
    }

    public function getStatusOrder()
    {
        return $this->status_order;
    }

    public function setStatusOrder($status)
    {
        $this->status_order = $status;
    }

    public function setNum($num)
    {
        $this->num = $num;
    }

    public function getNum()
    {
        return $this->num;
    }

    public function setIsShowAuthor($val)
    {
        $this->is_show_author = $val;
    }

    public function getIsShowAuthor()
    {
        return $this->is_show_author;
    }

    public function setIsShowClient($val)
    {
        $this->is_show_client = $val;
    }

    public function getIsShowClient()
    {
        return $this->is_show_client;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getDateCreate()
    {
        return $this->date_create;
    }

    public function setDateEdit($date)
    {
        $this->date_edit = $date;
    }

    public function getCountAuthorBids() {
        return $this->count_bids;
    }

    public function setCountAuthorBids($val)
    {
        $this->count_bids = $val;
    }

    public function setIsFavorite($val)
    {
        $this->is_favorite = $val;
    }

    public function getIsFavorite()
    {
        return $this->is_favorite;
    }

}