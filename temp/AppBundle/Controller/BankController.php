<?php

namespace AppBundle\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use AppBundle\Form\PostType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use \DateTime;
use AppBundle\Entity\Bank;
use AppBundle\Entity\Trade;
use AppBundle\Entity\Account;
use appbundle\repository\traderepository;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class BankController extends Controller
{
   /**
     * Register
     * SUCCESS:
     * 1.Response id, username, password as json.
     * ERROR:
     * 1.ERROR#R1 occurs when parameters are not illegal.
     * @Route("bank/register", name = "bankRegister")
     * @Method("POST")
     */
    public function registerAction(Request $request)
    {
        $content = $request->getContent();
        $requestData = json_decode($content, true);
        $registerUsername = $requestData['username'];
        $registerPassword = $requestData['password'];
        $checkUsername = strlen($registerUsername);
        $checkPassword = strlen($registerPassword);
        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new objectNormalizer()];
        $encoders = $encodersArray;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);

        if ($checkUsername > 0 && $checkPassword > 0) {
            $bank = new Bank();
            $bank->setUsername($registerUsername);
            $bank->setPassword($registerPassword);
            $bank->setBalance("0");
            $trade = new Trade();
            $trade->setBank($bank);

            try {
                $em = $this->container->get('doctrine.orm.entity_manager');
                $em->getConnection()->beginTransaction();
                $em->persist($bank);
                $em->persist($trade);
                $em->flush();
                $em->getConnection()->commit();

                $registerID =  $bank->getId();
                $jsonArray = [
                    'id' => $registerID,
                    'username' => $registerUsername,
                    'password' => $registerPassword
                ];

                $redis = $this->container->get('snc_redis.default');
                $redis->SET("bank:bankid:$registerID:balance",'0');
            } catch (\Exception $e) {
                $em->getConnection()->rollBack();
                throw $e->getMessage();
            }
        } else {
            $jsonArray = ['ERROR' => 'ERROR#R1'];
        }
        $json = $serializer->serialize($jsonArray, 'json');
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * Find id and search logs of behavior
     * SUCCESS:
     * 1.Response the results of behavior as json.
     * ERROR:
     * 1.ERROR#S1 occurs when bankId was not found.
     * 2.ERROR#S2 occurs when parameters are not illegal.
     * @Route("bank/search", name = "bankSearch")
     * @Method("GET")
     */
    public function searchAction(Request $request)
    {
        $id = $request->query->get('id');
        $from = $request->query->get('from');
        $to = $request->query->get('to');
        $behavior = $request->query->get('behavior');
        $numCheck = $from . $to . $id;

        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new objectNormalizer()];
        $encoders = $encodersArray;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);

        if (ctype_digit("$numCheck") && intval("$from") >= 0 && intval("$to") >= 0) {
            $em = $this->getDoctrine()->getManager();
            $bank = $em->getRepository('AppBundle:Bank')->find($id);

            $encodersArray = [
                new XmlEncoder(),
                new JsonEncoder()
            ];
            $normalizersArray = [new objectNormalizer()];
            $encoders = $encodersArray;
            $normalizers = $normalizersArray;
            $serializer = new Serializer($normalizers, $encoders);

            if (!is_null($bank)) {
                $Trades = $em->getRepository('AppBundle:Trade')
                    ->findTradesByBankID($behavior, $id, $from, $to);
                $returnTrades = [];
                $resultCount = count($Trades);

                for ($i = 0; $i < $resultCount; $i++) {
                    array_push($returnTrades, $Trades[$i]->getTradeArray());
                }

                $redis = $this->container->get('snc_redis.default');
                $redisBalance = $redis->GET("bank:bankid:$id:balance");
                $jsonArray = [
                    'result' => $returnTrades,
                    'balance' => $redisBalance
                ];
            } else {
                $jsonArray = ['ERROR' => 'ERROR#S1'];
            }
        } else {
            $jsonArray = ['ERROR' => 'ERROR#S2'];
        }
        $json = $serializer->serialize($jsonArray, 'json');
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * Deposit Money
     * SUCCESS:
     * 1.Response bankID, amount.
     * ERROR:
     * 1.ERROR#D1 occurs when bankId was not found.
     * 2.ERROR#D2 occurs when parameters are not illegal.
     * @Route("bank/deposit", name = "bankDeposit")
     * @Method("POST")
     */
    public function depositAction(Request $request)
    {
        $content = $request->getContent();
        $requestData = json_decode($content, true);
        $depositAmount = $requestData['amount'];
        $depositId = $requestData['id'];
        $em = $this->getDoctrine()->getManager();
        $bank = $em->getRepository('AppBundle:Bank')->find($depositId);

        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new objectNormalizer()];
        $encoders = $encodersArray;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);

        if (ctype_digit("$depositId") && ctype_digit("$depositAmount") && intval("$depositAmount") >= 0) {

            if (!is_null($bank)) {
                $prebalance = $bank->getBalance();
                $balance = $prebalance + $depositAmount;
                $postbalance = $balance;
                $bank->setBalance($balance);

                $trade = new Trade();
                $trade->setBehavior('Deposit');
                $trade->setPrebalance($prebalance);
                $trade->setPostbalance($postbalance);
                $trade->setAmount($depositAmount);
                $trade->setBank($bank);

                try {
                    $em = $this->container->get('doctrine.orm.entity_manager');
                    $em->getConnection()->beginTransaction();
                    $em->getConnection()->commit();
                    $em->persist($trade);
                    $em->flush();

                    $bankID = $depositId;
                    $tradeID = $trade->getId();
                    $date = $trade->getTradingDate();
                    $redis = $this->container->get('snc_redis.default');
                    $redis->SET("bank:bankid:$bankID:balance","$balance");

                    $jsonArray = [
                        'bankID' => $bank->getId(),
                        'amount' => $trade->getAmount()
                    ];
                } catch (\Exception $e) {
                    $em->getConnection()->rollback();
                    throw $e->getMessage();
                }
            } else {
                $jsonArray = ['ERROR' => 'ERROR#D1'];
            }
        } else {
            $jsonArray = ['ERROR' => 'ERROR#D2'];
        }
        $json = $serializer->serialize($jsonArray, 'json');
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }

    /**
     * Withdrawals Money
     * SUCCESS:
     * 1.Response bankID , amount.
     * ERROR:
     * 1.ERROR#W1 occurs when balance not enouth.
     * 2.ERROR#W2 occurs when BankId was not found.
     * 3.ERROR#W3 occurs when parameters are not illegal.
     * @Route("bank/withdrawals", name = "bankWithdrawals")
     * @Method("POST")
     */
    public function withdrawalsAction(Request $request)
    {
        $content = $request->getContent();
        $requestData = json_decode($content, true);
        $withdrawalsAmount = $requestData['amount'];
        $withdrawalsId = $requestData['id'];

        $em = $this->getDoctrine()->getManager();
        $bank = $em->getRepository('AppBundle:Bank')->find($withdrawalsId);

        $encodersArray = [
            new XmlEncoder(),
            new JsonEncoder()
        ];
        $normalizersArray = [new objectNormalizer()];
        $encoders = $encodersArray;;
        $normalizers = $normalizersArray;
        $serializer = new Serializer($normalizers, $encoders);

        if (ctype_digit("$withdrawalsId") && ctype_digit("$withdrawalsAmount") && intval("$withdrawalsAmount") >= 0) {

            if (!is_null($bank)) {
                $prebalance = $bank->getBalance();
                $balance = $prebalance - $withdrawalsAmount;
                $postbalance = $balance;

                if ($prebalance > 0 && $balance >= 0) {
                    $bank->setBalance($balance);

                    $trade = new Trade();
                    $trade->setBehavior("Withdrawals");
                    $trade->setAmount($withdrawalsAmount);
                    $trade->setPrebalance($prebalance);
                    $trade->setPostbalance($postbalance);
                    $trade->setBank($bank);

                    try {
                        $em = $this->container->get('doctrine.orm.entity_manager');
                        $em->getConnection()->beginTransaction();
                        $em->getConnection()->commit();
                        $em->persist($trade);
                        $em->flush();

                        $bankID = $withdrawalsId;
                        $tradeID = $trade->getId();
                        $date = $trade->getTradingDate();
                        $redis = $this->container->get('snc_redis.default');
                        $redis->SET("bank:bankid:$bankID:balance","$balance");

                        $jsonArray = [
                            'bankID' => $bank->getId(),
                            'amount' => $trade->getAmount()
                        ];
                    } catch (\Exception $e) {
                        $em->getConnection()->rollback();
                        throw $e->getMessage();
                    }
                } else {
                    $jsonArray = [
                        'bankID' => $bank->getId(),
                        'ERROR' => 'ERROR#W1'
                    ];
                }
            } else {
                $jsonArray = [
                    'bankID' => $withdrawalsId,
                    'ERROR' => 'ERROR#W2'
                ];
            }
        } else {
            $jsonArray = [
                'bankID' => $withdrawalsId,
                'ERROR' => 'ERROR#W3'
            ];
        }
        $json = $serializer->serialize($jsonArray, 'json');
        $response = new Response();
        $response->setContent($json);
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');

        return $response;
    }
}
