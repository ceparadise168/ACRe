<?php

namespace Tests\AppBundle\Controller;
namespace AppBundle\Entity;

use Doctrine\ORM\EntityRepository;
use AppBundle\Entity\Message;
use AppBundle\Entity\Bank;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\HttpFoundation\Request;

class BankControllerTest extends WebTestCase
{
    /**
     * The entityManagerMock
     */
    private $entityManagerMock;

    /**
     * Set Mock 
     */
    public function __construct()
    {
        $methodArray = [
            'persist',
            'flush',
            'beginTransaction',
            'commit',
            'getConnection',
            'getRepository',
            'find'
        ];
        $this->entityManagerMock = $this
            ->getMockBuilder('Doctrine\ORM\EntityManager')
            ->setMethods($methodArray)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * Test Register
     * 1.assert statusCode is 200
     * 2.assert username by checking response
     */
    public function testRegisterAction()
    {
        $postData = [
            'username' => 'C8763_' . mt_rand(0,9),
            'password' => '0000'
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];

        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            'bank/register',
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($postData['username'], $responseCheck['username']);
    }

    /**
     * Test Register without username and password
     * 1.assert statusCode is 200
     * 2.assert response ERROR MESSAGE is "ERROR#R1"
     */
    public function testRegisterActionFalseWithIllegalParm()
    {
        $postData = [
            'username' => '',
            'password' => ''
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];

        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            'bank/register',
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals('ERROR#R1', $responseCheck['ERROR']);
    }

    /**
     * Test SerachDepositAction
     * 1.serach the deposit logs and assert statusCode is 200
     * 2.assert the behavior is Deposit
     */
    public function testSerachDepositAction()
    {
        $id = 1;
        $behavior = 'Deposit';
        $from = 0;
        $to = 100;
        $postData = [
            'id' => $id,
            'behavior'=> $behavior,
            'from' => $from,
            'to' => $to
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $query = '?' . http_build_query($postData);
        $url = 'bank/search'."$query";
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($id, $responseCheck['result'][0]['bank_id']);
    }

    /**
     * Test SerachWithdrawalsAction
     * 1.search the withdrawal logs and assert statusCode is 200
     * 2.assert the behavior is withdrawals
     */
    public function testSerachWithdrawalsAction()
    {
        $id = 1;
        $behavior = 'Withdrawals';
        $from = 0;
        $to = 100;
        $postData = [
            'id' => $id,
            'behavior'=> $behavior,
            'from' => $from,
            'to' => $to
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $query = '?' . http_build_query($postData);
        $url = 'bank/search'."$query";
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($id, $responseCheck['result'][0]['bank_id']);
    }

    /**
     * Test Serach ID NOT exist
     * 1.assert statusCode is 200
     * 2.assert the responseCheck ERROR MESSAGE is "ERROR#S1"
     */
    public function testSerachActionIdNotExist()
    {
        $id = 10333333;
        $behavior = 'deposit';
        $from = 0;
        $to = 100;
        $postData = [
            'id' => $id,
            'behavior' => $behavior,
            'from' => $from,
            'to' => $to
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $query = '?' . http_build_query($postData);
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/search'."$query";
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals('ERROR#S1', $responseCheck['ERROR']);
    }

    /**
     * Test Serach parm FROM, TO  NOT are illegal
     * 1.assert statusCode is 200
     * 2.assert the responseCheck ERROR MESSAGE is "ERROR#S2"
     */
    public function testSerachActionFromToNotExist()
    {
        $id = 1;
        $behavior = 'deposit';
        $from = 'asd';
        $to = 'asd';
        $postData = [
            'id' => $id,
            'behavior' => $behavior,
            'from' => $from,
            'to' => $to
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $query = '?' . http_build_query($postData);
        $url = 'bank/search' . "$query";
        $client = static::createClient();
        $crawler = $client->request(
            'GET',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals('ERROR#S2', $responseCheck['ERROR']);
    }

    /**
     * Test Deposit
     * 1.assert statusCode is 200
     * 2.assert responseCheck bankID is equal to post id
     * 3.assert responseCheck amount is equal to post amount
     */
    public function testDepositActionSuccess()
    {
        $id = 1;
        $amount = 1000;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $depositMoney = 1000;
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/deposit';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($postData['id'], $responseCheck['bankID']);
        $this->assertEquals($postData['amount'], $responseCheck['amount']);
    }

    /**
     * Test Deposit ID NOT exist
     * 1.assert statusCode is 200
     * 2.assert the responseCheck ERROR MESSAGE is "ERROR#D1"
     */
    public function testDepositActionIdNotExist()
    {

        $id = 103333333333;
        $amount = 1000;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $depositMoney = 1000;
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/deposit';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals('ERROR#D1', $responseCheck['ERROR']);
    }

    /**
     * Test Deposit Amount NOT allowed
     * 1.assert statusCode is 200
     * 2.assert the responseCheck ERROR MESSAGE is "ERROR#D2"
     */
    public function testDepositActionAmountNotAllowed()
    {

        $id = 1;
        $amount = 'asdas';
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $depositMoney = 1000;
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/deposit';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals('ERROR#D2', $responseCheck['ERROR']);
    }

    /**
     * Test Withdrawals
     * 1.assert the statusCode is 200
     * 2.assert the responseCheck bankID is equal to post id
     * 3.assert the responseCheck amount is equal to post amount
     */
    public function testWithdrawalsAction()
    {
        $id = 1;
        $amount = 1;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/withdrawals';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($postData['id'], $responseCheck["bankID"]);
        $this->assertEquals($postData['amount'], $responseCheck['amount']);
    }

    /**
     * Test Withdrawals when balance is not enough
     * 1.assert the statusCode is 200
     * 2.assert the responseCheck bank id is equal to the post id
     * 3.assert the responseCheck ERROR MESSAGE is "ERROR#W1"
     */
    public function testWithdrawalsActionNotEnough()
    {
        $id = 1;
        $amount = 10000000000000000;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/withdrawals';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($postData['id'], $responseCheck['bankID']);
        $this->assertEquals($responseCheck['ERROR'],'ERROR#W1');
    }

    /**
     * Test Withdrawals when id is not exist
     * 1.assert the statusCode is 200
     * 2.assert the responseCheck ERROR MESSAGE is "ERROR#W2"
     */
    public function testWithdrawalsActionNotExist()
    {
        $id = 103333333333333;
        $amount = 1000;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/withdrawals';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($responseCheck['ERROR'],'ERROR#W2');
    }

    /**
     * Test Withdrawals when amount is not illegal
     * 1.assert the statusCode is 200
     * 2.assert the responseCheck ERROR MESSAGE is "ERROR#W3"
     */
    public function testWithdrawalsActionAmountNotAllowed()
    {
        $id = 1;
        $amount = 'asdasd';
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/withdrawals';
        $client = static::createClient();
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        $responseCheck = json_decode($content, true);
        $this->assertEquals($responseCheck['ERROR'],'ERROR#W3');
    }

    /**
     * Test Transation fail and rollback in RegisterAction.
     */
    public function testRegisterTransationException()
    {
        $this->entityManagerMock->expects($this->any())
            ->method('getConnection')->will($this->throwException(new \Exception('err')));

        $client = $this->createClient();
        $client->getContainer()->set('doctrine.orm.entity_manager', $this->entityManagerMock);
        $postData = [
            'username' => 'C8763_' . mt_rand(0,9),
            'password' => '0000'
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];

        $crawler = $client->request(
            'POST',
            'bank/register',
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );
    }

    /**
     * Test Transation fail and rollback in DepositAction.
     */
    public function testDepositExceptiono()
    {
        $id = 1;
        $amount = 1000;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $depositMoney = 1000;
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/deposit';

        $this->entityManagerMock->expects($this->any())
            ->method('getConnection')->will($this->throwException(new \Exception('err')));

        $client = $this->createClient();
        $client->getContainer()->set('doctrine.orm.entity_manager', $this->entityManagerMock);
        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );
    }

    /**
     * Test Transation fail and rollback in WithdrawalAction.
     */
    public function testWithdrawalException()
    {
        $id = 1;
        $amount = 1;
        $postData = [
            'amount' => $amount,
            'id' => $id
        ];
        $paramArray = [];
        $uploadFileArray = [];
        $contentTypeArray = ['CONTENT_TYPE' => 'application/json'];
        $url = 'bank/withdrawals';

        $this->entityManagerMock->expects($this->any())
            ->method('getConnection')->will($this->throwException(new \Exception('err')));

        $client = $this->createClient();
        $client->getContainer()->set('doctrine.orm.entity_manager', $this->entityManagerMock);

        $crawler = $client->request(
            'POST',
            $url,
            $paramArray,
            $uploadFileArray,
            $contentTypeArray,
            json_encode($postData)
        );
    }
}
