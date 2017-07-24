<?php

// src/AppBundle/Controller/LakiController.php
namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class LakiController extends Controller
{
    /**
     * @Route("/laki/number/{max}")
     */
    public function numberAction($max)
    {
        $number = mt_rand(0, $max);

        return $this->render('lucky/number.html.twig', array(
            'number' => $number,
        ));
    }
}


