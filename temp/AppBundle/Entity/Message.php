<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use \DateTime;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @ORM\Entity
 * @ORM\Table(name="MsgBoard")
 */
class Message
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var User
     * @ORM\Column(type="string", length=100)
     */
    private $userName;

    /**
     * @var Msg
     * @ORM\Column(type="text")
     */
    private $msg;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $publishedAt;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    public function __construct()
    {
        $this->publishedAt = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $this->updatedAt = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
    }

    /**
     * Get Update Time
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt->format('Y-m-d H:i:s');
    }

    /**
     * Set Update Time
     */
    public function setUpdatedAt(\DateTime $updatedAt )
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * Get Published Time
     */
    public function getPublishedAt()
    {
        return $this->publishedAt->format('Y-m-d H:i:s');
    }

    /**
     * Set Publish Time
     */
    public function setPublishedAt(\DateTime $publishedAt)
    {
        $this->publishedAt = $publishedAt;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set userName
     *
     * @param string $userName
     *
     * @return Message
     */
    public function setUserName($userName)
    {
        $this->userName = $userName;

        return $this;
    }

    /**
     * Get userName
     *
     * @return string
     */
    public function getUserName()
    {
        return $this->userName;
    }

    /**
     * Set msg
     *
     * @param string $msg
     *
     * @return Message
     */
    public function setMsg($msg)
    {
        $this->msg = $msg;

        return $this;
    }

    /**
     * Get msg
     *
     * @return string
     */
    public function getMsg()
    {
        return $this->msg;
    }
}
