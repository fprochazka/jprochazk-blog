<?php


namespace App\Controller;


use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ErrorController extends AbstractController
{
    /**
     * @Route("/error", name="app_blog_error")
     */
    public function showError(Request $request): Response {
        $msg = $request->query->get('msg');

        switch($msg) {
            case 'auth':
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Login to access this page',
                ]);
            case '403':
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Insufficient permissions',
                ]);
            case '404':
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Page not found.',
                ]);
            default:
                return $this->render('blog/error.html.twig', [
                    'msg' => 'Undefined error',
                ]);
        }
    }
}