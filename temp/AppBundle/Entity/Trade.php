<?php

namespace AppBundle\Entity;

use AppBundle\Entity\Bank;
use Doctrine\ORM\Mapping as ORM;
use \DateTime;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TradeRepository") 
 * @ORM\Table(name="Trade")
 */
class Trade
{
    /**
     * @var Bank
     * @ORM\ManyToOne(targetEntity="Bank", inversedBy="trades")
     * @ORM\JoinColumn(name="bank_id", referencedColumnName="id")
     */
    private $bank;

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank()
     */
    private $behavior;

    /**
     * this is the balance before trading
     * @ORM\Column(type="integer") 
     */
    private $prebalance;

    /**
     * this is the balance after trading
     * @ORM\Column(type="integer") 
     */
    private $postbalance;

    /**
     * @ORM\Column(type="integer") 
     */
    private $amount;

    /**
     * @var \DateTime
     * @ORM\Column(type="datetime")
     */
    private $tradingDate;

    public function __construct()
    {
        $this->tradedAt = new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $this->tradingDate =  new \DateTime('now', new \DateTimeZone('Asia/Taipei'));
        $this->amount = 0;
        $this->behavior = 'init';
        $this->tradingFrom = 'init';
        $this->tradingTo = 'init';
        $this->prebalance = 0;
        $this->postbalance = 0;
    }

    public function getTradeArray()
    {
        $tradeArray = [
            "bank_id" => $this->bank->getId(),
            "trade_id" => $this->id,
            "amount" => $this->amount,
            "behavior" => $this->behavior,
            "prebalance" => $this->prebalance,
            "postbalance" => $this->postbalance,
            "trading_date" => $this->tradingDate->format('Y-m-d H:i:s')
        ];
        return $tradeArray;
    }

    /**
     * Get Bank
     */
    public function getBank()
    {
        return $this->bank;
    }

    /**
     * Set Bank
     */
    public function setBank(Bank $bank)
    {
        $this->bank = $bank;
        return $this;
    }

    public function getTradingDate()
    {
        return $this->tradingDate->format('Y-m-d H:i:s');
    }

    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Trading Beahavior
     * @return string
     */
    public function getBehavior()
    {
        return $this->behavior;
    }

    /**
     * Set Trading Beahavior
     * @return Trade
     */
    public function setBehavior($behavior)
    {
        $this->behavior = $behavior;
        return $this;
    }

    /**
     * Get Amount
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set Amount
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * Get Prebalance
     */
    public function getPrebalance()
    {
        return $this->prebalance;
    }

    /**
     * Set Prebalance
     */
    public function setPrebalance($prebalance)
    {
        $this->prebalance = $prebalance;
        return $prebalance;
    }

    /**
     * Get Postbalance
     */
    public function getPostbalance()
    {
        return $this->postbalance;
    }

    /**
     * Set Postbalance
     */
    public function setPostbalance($postbalance)
    {
        $this->postbalance = $postbalance;
        return $postbalance;
    }
}
