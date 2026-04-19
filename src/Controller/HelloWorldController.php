<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class HelloWorldController
{
    #[Route('/hello', name: 'hello_world', methods: ['GET'])]
    public function helloWorld(): Response
    {
        $response = new JsonResponse();
        $response->setContent(json_encode(['message' => 'hello fish']));
        return $response;
    }


}
