<?php

namespace App\Controller;

use App\Form\GradeType;
use App\Repository\GradeRepository;
use App\Repository\SubjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
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
            "teachers" => $teachers,
            "subjects" => $subjects
        ]);
    }

    #[Route('/grades/edit/{id}/{trimester}', name: 'edit_grade')]
    public function edit(Request $req, EntityManagerInterface $em, GradeRepository $repo, int $id, int $trimester): response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Action réservée aux enseignants");
            
            return $this->redirectToRoute("app_grades");
        }

        $grade = $repo->find($id);

        if($this->getUser()->getSubject() != $grade->getSubject() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Vous ne pouvez pas modifier la note d'une matière ne vous concernant pas");

            return $this->redirectToRoute("view_student", ["id" => $grade->getStudent()->getId(), "subjectID" => $grade->getSubject()->getId(), "trimester" => $trimester]);
        }

        $form = $this->createForm(GradeType::class, $grade);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($grade);
            $em->flush();

            $this->addFlash("success", "Note mise à jour");

            return $this->redirectToRoute("view_student", ["id" => $grade->getStudent()->getId(), "subjectID" => $grade->getSubject()->getId(), "trimester" => $trimester]);
        }

        return $this->render("grade/edit.html.twig", ["form" => $form]);
    }

    #[Route('/grades/delete/{id}/{trimester}', name: 'delete_grade')]
    public function delete(EntityManagerInterface $em, GradeRepository $repo, int $id, int $trimester): response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Action réservée aux enseignants");
            
            return $this->redirectToRoute("app_grades");
        }

        $grade = $repo->find($id);

        if($this->getUser()->getSubject() != $grade->getSubject() && !in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Vous ne pouvez pas supprimer la note d'une matière ne vous concernant pas");

            return $this->redirectToRoute("view_student", ["id" => $grade->getStudent()->getId(), "subjectID" => $grade->getSubject()->getId(), "trimester" => $trimester]);
        }

        $em->remove($grade);
        $em->flush();

        $this->addFlash("success", "Note supprimée");

        return $this->redirectToRoute("view_student", ["id" => $grade->getStudent()->getId(), "subjectID" => $grade->getSubject()->getId(), "trimester" => $trimester]);
    }
}
