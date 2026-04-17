<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/')]
class HelloWorldController
{
    #[Route('/hello', name: 'hello_world', methods: ['GET'])]
    public function claimCreate(Request $request, EntityManagerInterface $em): Response
    {
        $response = new JsonResponse();
        $response->setContent(json_encode(['message' => 'hello fish']));
        return $response;
    }


}
