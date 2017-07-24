<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ACController extends Controller
{

    /**
     * @Route("/ac/login")
     * @Method("POST")
     */
    public function loginAction(Request $request)
    {
        $content = $request->getContent();
        $requestData = json_decode($content, true); 
        $response = new Response();
        $response->setContent($content);
        dump($requestData);
        // $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        return $response;
    }

    public function logoutAction()
    {
        $response = new Respose;
    }
}
