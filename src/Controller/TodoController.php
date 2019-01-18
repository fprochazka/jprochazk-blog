<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;

use App\Entity\Task;

class TodoController extends AbstractController
{
	private function getTaskByID($id) {
		return $this->getDoctrine()->getRepository(Task::class)->find($id);
	}

	private function getTasks() {
		$tasks = array();
        for($i = 0; $i < 10; ++$i) {
        	if(!$task = $this->getTaskByID($i)) continue;
        	$tasks[$i] = [ 
        		'id' => $task->getId(),
        		'name' => $task->getTitle() 
        	];
    	}
    	return $tasks;
	}

    /**
     * @Route("/todo", name="app_todo")
     */
    public function index(Request $request)
    {
    	$task = new Task();
    	$task->setTitle('');
    	$task->setDate(date_create_from_format('H:i:s Y-m-d', date('H:i:s Y-m-d')));

        $form = $this->createFormBuilder($task)
        ->add('title', TextType::class)
        ->add('save', ButtonType::class, ['label' => '🡆'])
        ->getForm();

        return $this->render('todo/index.html.twig', [
            'form' => $form->createView(),
            'tasks' => $this->getTasks(),
        ]);
    }


    /**
      * @Route("/todo/new", name="app_todo_new")
      */
    public function newTask(Request $request) {
    	if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
    		$title = $request->request->get('title');
    		if(is_string($title)) {
    			$date = date_create_from_format('H:i:s Y-m-d', date('H:i:s Y-m-d'));
    			$task = new Task();

    			$task->setDate($date);
    			$task->setTitle($title);

           	 	$entityManager = $this->getDoctrine()->getManager();
            	$entityManager->persist($task);
            	$entityManager->flush();

            	$responseData = [ 
            		'id' => $task->getId(),
            		'title' => $title 
            	];

            	return new JsonResponse(array(
            	'status' => 'OK',
            	'message' => $responseData),
            	200);
    		} else {
    			return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'Name is not of type string'),
            	400);
    		}
    	} else {
    		return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Not AJAX request'),
            400);
    	}
    }

	/**
      * @Route("/todo/del", name="app_todo_del")
      */
    public function deleteTask(Request $request) {
    	if($request->isXmlHttpRequest() || $request->query->get('showJson') == 1) {
    		$id = (int) $request->request->get('id');
    		if(is_int($id)) {
    			$task = $this->getTaskByID($id);
    			if(!$task) {
    				return new JsonResponse(array(
            		'status' => 'Could not find task by id'),
            		400);
    			}

    			$entityManager = $this->getDoctrine()->getManager();
            	$entityManager->remove($task);
            	$entityManager->flush();

            	return new JsonResponse(array(
            	'status' => 'OK',
            	'message' => 'OK'), 
            	200);
    		} else {
    			return new JsonResponse(array(
                'status' => 'Error',
                'message' => 'id is not of type int'),
            	400);
    		}
    	} else {
    		return new JsonResponse(array(
            'status' => 'Error',
            'message' => 'Not AJAX request'),
            400);
    	}
    }
}
