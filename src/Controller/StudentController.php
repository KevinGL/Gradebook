<?php

namespace App\Controller;

use App\Entity\Grade;
use App\Form\GradeType;
use App\Repository\GradeRepository;
use App\Repository\SubjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StudentController extends AbstractController
{
    #[Route("/students/view/{id}/{subjectID}", name: "view_student")]
    public function view(Request $req, UserRepository $repo, GradeRepository $gradeRepo, SubjectRepository $subjectRepo, EntityManagerInterface $em, int $id, int $subjectID): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            return $this->redirectToRoute("app_grades");
        }
        
        $student = $repo->find($id);

        $newGrade = new Grade();

        $newGrade->setSubject($this->getUser()->getSubject())
                ->setStudent($student)
                ->setDate(new \DateTime());

        $form = $this->createForm(GradeType::class, $newGrade);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($newGrade);
            $em->flush();

            $this->addFlash("success", "Note ajoutée à " . $student->getUsername());

            return $this->redirectToRoute("view_student", ["id" => $id, "subjectID" => $this->getUser()->getSubject()->getId()]);
        }

        $subject = $subjectRepo->find($subjectID);
        $subjects = $subjectRepo->findAll();

        $grades = $gradeRepo->findBySubjectStudent($subject, $student);

        usort($grades, function($a, $b)
        {
            if ($a->getDate()->getTimestamp() == $b->getDate()->getTimestamp())
            {
                return 0;
            }

            return ($a->getDate()->getTimestamp() < $b->getDate()->getTimestamp()) ? -1 : 1;
        });

        return $this->render('user/view.html.twig',
        [
            "student" => $student,
            "form" => $form,
            "grades" => $grades,
            "subjects" => $subjects,
            "currentSubject" => $subject
        ]);
    }
}
