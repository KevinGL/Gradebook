<?php

namespace App\Controller;

use App\Entity\Appreciation;
use App\Entity\Grade;
use App\Form\GradeType;
use App\Repository\AppreciationRepository;
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
    public function view(Request $req, UserRepository $repo, GradeRepository $gradeRepo, SubjectRepository $subjectRepo, AppreciationRepository $appRepo, EntityManagerInterface $em, int $id, int $subjectID): Response
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

        $gradeForm = $this->createForm(GradeType::class, $newGrade);
        $gradeForm->handleRequest($req);

        if($gradeForm->isSubmitted() && $gradeForm->isValid())
        {
            $em->persist($newGrade);
            $em->flush();

            $this->addFlash("success", "Note ajoutée à " . $student->getUsername());

            return $this->redirectToRoute("view_student", ["id" => $id, "subjectID" => $this->getUser()->getSubject()->getId()]);
        }

        ////////////////////////////////////////////////////////

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

        $average = 0.0;

        foreach($grades as $g)
        {
            $average += $g->getValue();
        }

        if(count($grades))
        {
            $average /= count($grades);
        }

        ////////////////////////////////////////////////////////

        $appreciations = $appRepo->findBySubjectStudent($this->getUser()->getSubject(), $student);

        return $this->render('student/view.html.twig',
        [
            "student" => $student,
            "gradeForm" => $gradeForm,
            "grades" => $grades,
            "subjects" => $subjects,
            "currentSubject" => $subject,
            "average" => round($average, 2),
            "appreciations" => $appreciations
        ]);
    }
}
