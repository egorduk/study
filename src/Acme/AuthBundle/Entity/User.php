<?php

namespace Acme\AuthBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Helper\Helper;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Serializer;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
//class User extends EntityRepository implements AdvancedUserInterface
class User extends EntityRepository implements UserInterface, \Serializable
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
    protected $login;

    /**
     * @ORM\Column(type="string")
     */
    protected $password;

    /**
     * @ORM\Column(type="string")
     */
    protected $email;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_reg;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_confirm_reg;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $date_confirm_recovery;

    /**
     * @ORM\Column(type="string")
     */
    protected $salt;

    /**
     * @ORM\Column(type="string")
     */
    protected $hash_code;

    /**
     * @ORM\Column(type="string")
     */
    protected $avatar;

    /**
     * @ORM\Column(type="string")
     */
    protected $recovery_password;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_active;

    /**
     * @ORM\Column(type="integer")
     */
    protected $ip_reg;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_ban;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_confirm;

    /**
     * @ORM\Column(type="integer")
     */
    protected $is_access_order;

    /**
     * @ORM\Column(type="integer")
     */
    protected $account;

    /**
     * @ORM\OneToMany(targetEntity="Acme\SecureBundle\Entity\UserOrder", mappedBy="user")
     **/
    protected $link_user_order;

    /**
     * @ORM\OneToMany(targetEntity="Openid", mappedBy="user")
     **/
    protected $link_openid;

    /**
     * @ORM\OneToMany(targetEntity="Acme\SecureBundle\Entity\Author\AuthorFile", mappedBy="user")
     **/
    protected $link_author_file;

    /**
     * @ORM\OneToMany(targetEntity="Acme\SecureBundle\Entity\UserBid", mappedBy="user")
     **/
    protected $link_user_bid;

    /**
     * @ORM\ManyToOne(targetEntity="UserRole", inversedBy="link_role", cascade={"all"})
     * @ORM\JoinColumn(name="user_role_id", referencedColumnName="id")
     **/
    protected $role;

    /**
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="link_user_info", cascade={"all"})
     * @ORM\JoinColumn(name="user_info_id", referencedColumnName="id")
     **/
    protected $userInfo;

    /**
     * @ORM\OneToMany(targetEntity="Acme\SecureBundle\Entity\SelectBid", mappedBy="user")
     **/
    protected $link_select_user;

    /**
     * @ORM\OneToMany(targetEntity="Acme\SecureBundle\Entity\FavoriteOrder", mappedBy="user")
     **/
    protected $link_favorite_user;


    /**
     * @ORM\OneToMany(targetEntity="Acme\SecureBundle\Entity\WebchatMessage", mappedBy="user")
     **/
    protected $link_webchat_user;

    private $unEncodePass = "";


    public function __construct()
    {
        $this->date_reg = new \DateTime();
        $this->is_active = 0;
        $this->ip_reg = ip2long($_SERVER['REMOTE_ADDR']);
        $this->is_confirm = 0;
        $this->recovery_password = '';
        $this->hash_code = '';
        $this->account = 0;
        $this->avatar = "default.jpg";
        $this->is_access_order = 0;
        $this->link_user_order = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_openid = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_author_file = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_user_bid = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_select_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_webchat_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->link_favorite_user = new \Doctrine\Common\Collections\ArrayCollection();
        $this->is_ban = 0;
        $this->date_confirm_recovery = Helper::getFormatDateForInsert("0000-00-00 00:00:00", "Y-m-d H:i:s");
        $this->date_confirm_reg = Helper::getFormatDateForInsert("0000-00-00 00:00:00", "Y-m-d H:i:s");
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function getUsername()
    {
    }

    public function getIpReg()
    {
        return $this->ip_reg;
    }

    public function getHash()
    {
        return $this->hash_code;
    }

    public function setHash($hash)
    {
        $this->hash_code = $hash;
    }

    public function getIsBan()
    {
        return $this->is_ban;
    }

    public function setIsBan($ban)
    {
        $this->is_ban = $ban;
    }

    public function getSalt()
    {
        return $this->salt;
    }

    public function setSalt($value)
    {
        $this->salt = $value;
    }

    public function setRecoveryPassword($password)
    {
        $this->recovery_password = $password;
    }

    public function getRecoveryPassword()
    {
        return $this->recovery_password;
    }

    public function getOpenId()
    {
        return $this->openid_id;
    }

    public function setOpenId($openId)
    {
        $this->openid_id = $openId;
    }

    public function setIsConfirm($value)
    {
        $this->is_confirm = $value;
    }

    public function getIsConfirm()
    {
        return $this->is_confirm;
    }

    public function getRole()
    {
        return $this->role;
    }

    public function setRole($role)
    {
        $this->role = $role;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getLogin()
    {
        return $this->login;
    }

    public function setLogin($login)
    {
        $this->login = $login;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }

    public function setDateReg($dateReg)
    {
        $this->date_reg = $dateReg;
    }

    public function getDateReg()
    {
        return $this->date_reg;
    }

    public function setDateConfirmReg($dateReg)
    {
        $this->date_confirm_reg = $dateReg;
    }

    public function getDateConfirmReg() {
        return $this->date_confirm_reg;
    }

    public function getRoles() {
    }

    public function eraseCredentials() {
    }

    public function isAccountNonExpired() {
        return true;
    }

    public function isAccountNonLocked() {
        return true;
    }

    public function isCredentialsNonExpired() {
        return true;
    }

    public function isEnabled() {
        return $this->is_active;
    }

    public function setIsEnabled($active) {
        $this->is_active = $active;
    }

    public function getDateConfirmRecovery() {
        return $this->date_confirm_recovery;
    }

    public function setDateConfirmRecovery($date) {
        $this->date_confirm_recovery = $date;
    }

    public function setUserInfo($userInfo) {
        $this->userInfo = $userInfo;
    }

    public function getUserInfo() {
        return $this->userInfo;
    }

    public function setAccount($account) {
        $this->account = $account;
    }

    public function getAccount() {
        return $this->account;
    }

    public function setIsAccessOrder($val) {
        $this->is_access_order = $val;
    }

    public function getIsAccessOrder() {
        return $this->is_access_order;
    }

    public function getAvatar() {
        return $this->avatar;
    }

    /**
     * @see \Serializable::serialize()
     */
    public function serialize() {
        return serialize(array(
            $this->id,
            $this->password,
            $this->email
        ));
    }

    /**
     * @see \Serializable::unserialize()
     */
    public function unserialize($serialized) {
        list (
            $this->id,
            $this->password,
            $this->email
            ) = unserialize($serialized);
    }

    public function setUnEncodePass($pass) {
        $this->unEncodePass = $pass;
    }

    public function getUnEncodePass() {
        return $this->unEncodePass;
    }
}