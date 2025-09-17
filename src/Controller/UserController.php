<?php

namespace App\Controller;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController extends AbstractController
{
    #[Route('/user', name: 'app_user')]
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
            'controller_name' => 'UserController',
        ]);
    }

    #[Route("/users/view/{id}", name: "view_user")]
    public function view(UserRepository $repo, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            return $this->redirectToRoute("app_grades");
        }
        
        $user = $repo->find($id);

        return $this->render('user/view.html.twig',
        [
            "user" => $user
        ]);
    }
}
