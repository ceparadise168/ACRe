<?php

namespace Tests\AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ACControllerTest extends WebTestCase
{
    public function testIndex()
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
                'ac/login',
                $paramArray,
                $uploadFileArray,
                $contentTypeArray,
                json_encode($postData)
                );

        $this->assertEquals(200, $client->getResponse()->getStatusCode());
        $content = $client->getResponse()->getcontent();
        dump($content);
    }

}
