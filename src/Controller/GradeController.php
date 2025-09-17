<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GradeController extends AbstractController
{
    #[Route('/grades', name: 'app_grades')]
    public function index(SubjectRepository $repo): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        $subjects = $repo->findAll();

        $grades = [];
        $averages = [];

        foreach($subjects as $s)
        {
            $grades[$s->getId()] = ['sum' => 0.0, 'count' => 0, 'subject' => $s->getName()];
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $allGrades = $this->getUser()->getGrades()->toArray();
            
            foreach($allGrades as $g)
            {
                foreach($subjects as $s)
                {
                    if($g->getSubject()->getId() == $s->getId())
                    {
                        $grades[$s->getId()]["sum"] += $g->getValue();
                        $grades[$s->getId()]["count"]++;
                        $grades[$s->getId()]["subject"] = $s->getName();
                    }
                }
            }

            foreach($grades as $g)
            {
                if($g["count"] > 0)
                {
                    $averages[$g["subject"]] = round($g["sum"] / $g["count"], 2);
                }
            }
        }
        
        return $this->render('grade/index.html.twig',
        [
            "averages" => $averages
        ]);
    }
}
