<?php

namespace App\Controller;

use App\Repository\SchoolClassRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SchoolClassController extends AbstractController
{
    #[Route('/schoolclass', name: 'app_schoolclass')]
    public function index(): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        return $this->render('school_class/index.html.twig', [
            'controller_name' => 'SchoolClassController',
        ]);
    }

    #[Route("/schoolclass/{id}", name: "view_schoolclass")]
    public function view(SchoolClassRepository $repo, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }
        
        $schoolClass = $repo->find($id);

        return $this->render('school_class/view.html.twig',
        [
            'schoolClass' => $schoolClass
        ]);
    }
}
