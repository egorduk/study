<?php

namespace Acme\SecureBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Doctrine\ORM\EntityRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="User_order")
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
     * @ORM\Column(type="date")
     */
    private $date_expire;

    /**
     * @ORM\Column(type="date")
     */
    private $date_create;

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
     * @ORM\ManyToOne(targetEntity="subject", inversedBy="link_subject", cascade={"all"})
     * @ORM\JoinColumn(name="subject_id", referencedColumnName="id")
     **/
    private $subject;

    /**
     * @ORM\ManyToOne(targetEntity="TypeOrder", inversedBy="link_type_order", cascade={"all"})
     * @ORM\JoinColumn(name="type_order_id", referencedColumnName="id")
     **/
    private $type_order;


    public function __construct($container){
        $this->date_expire = new \DateTime();
        $this->date_create = new \DateTime();
        $this->num = 1;

        $query = $this->createQueryBuilder('s');
        $query->select('s, MAX(s.num) AS max_num');
        $query->where('s.challenge = :challenge')->setParameter('challenge', $challenge);
        $query->groupBy('s.user');
        $query->orderBy('max_score', 'DESC');


        $em = $this->getDoctrine()->getManager();
        $obj = $em->getRepository($this->tableNews)->createQueryBuilder('n')
            ->orWhere('n.title LIKE :filter1')
            ->orWhere('n.title LIKE :filter2')
            ->orWhere('n.title LIKE :filter3')
            ->setParameter('filter1', $param . ' %')
            ->setParameter('filter2', '% ' . $param)
            ->setParameter('filter3', '% ' . $param . ' %')
            ->getQuery()
            ->getResult();
        print_r($obj); die;

        /*$em = $container->get('doctrine')->getManager();
        $order = $em->getRepository('AcmeSecureBundle:UserOrder')
            ->findOneById(1);
        $num = $order->getNum();
        $num++;
        $this->num = $num;*/
    }

    public function setTheme($theme)
    {
        $this->theme = $theme;
    }

    public function getTheme()
    {
        return $this->theme;
    }

    public function setDateExpire($date)
    {
        $this->date_expire = $date;
    }

    public function getDateExpire()
    {
        return $this->date_expire;
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

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function getTypeOrder()
    {
        return $this->type_order;
    }

    public function setTypeOrder($type)
    {
        $this->type_order = $type;
    }

    public function setNum($num)
    {
        $this->num = $num;
    }

    public function getNum()
    {
        return $this->num;
    }
}