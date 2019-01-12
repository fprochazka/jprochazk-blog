<?php
// src/Controller/LuckyController.php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class LuckyController extends AbstractController
{

    /**
      * @Route("/lucky/number/{num<\d+>?}", name="app_lucky_number")
      */
    public function number($num)
    {
    	$btn_url = $this->generateurl('app_lucky_number');
    	if(is_null($num)){
    		$number = random_int(0, 100);
    		return $this->render('lucky/number.html.twig', [
    			'number' => $number,
    			'btn_url' => $btn_url,
    			'lucky' => "lucky"
    		]);
    	} else {
    		return $this->render('lucky/number.html.twig', [
    			'number' => $num,
    			'btn_url' => $btn_url,
    			'lucky' => "unlucky"
    		]);
    	}
    }

    
}