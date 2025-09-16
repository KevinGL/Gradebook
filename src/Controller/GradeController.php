<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GradeController extends AbstractController
{
    #[Route('/grades', name: 'app_grades')]
    public function index(): Response
    {
        return $this->render('grade/index.html.twig',
        [
            //
        ]);
    }
}
