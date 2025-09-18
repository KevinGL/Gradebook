<?php

namespace App\Controller;

use App\Repository\SubjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class GradeController extends AbstractController
{
    #[Route('/grades', name: 'app_grades')]
    public function index(SubjectRepository $subjectRepo, UserRepository $userRepo): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        $subjects = $subjectRepo->findAll();
        $users = $userRepo->findAll();

        $grades = [];
        $averages = [];
        $teachers = [];

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            foreach($subjects as $s)
            {
                $grades[$s->getId()] = ['sum' => 0.0, 'count' => 0, 'subject' => $s->getName()];
            }
            
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

            //////////////////////////////////////////////////////////////////////////////////////

            foreach($users as $user)
            {
                if(in_array("ROLE_TEACHER", $user->getRoles()))
                {
                    if(in_array($this->getUser()->getClass(), $user->getSchoolClasses()->toArray()))
                    {
                        $teachers[$user->getSubject()->getName()] = $user->getUsername();
                    }
                }
            }
        }
        
        return $this->render('grade/index.html.twig',
        [
            "averages" => $averages,
            "teachers" => $teachers
        ]);
    }
}
