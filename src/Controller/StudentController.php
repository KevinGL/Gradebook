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
use Knp\Snappy\Pdf;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class StudentController extends AbstractController
{
    #[Route("/students/view/{id}/{subjectID}/{trimester}", name: "view_student")]
    public function view(Request $req, UserRepository $repo, GradeRepository $gradeRepo, SubjectRepository $subjectRepo, AppreciationRepository $appRepo, EntityManagerInterface $em, int $id, int $subjectID, int $trimester): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()) && $id != $this->getUser()->getId())
        {
            $this->addFlash("error", "Cette page ne vous est pas accessible");
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
            if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
            {
                $this->addFlash("error", "Cette page ne vous est pas accessible");
                return $this->redirectToRoute("app_grades");
            }
            
            $em->persist($newGrade);
            $em->flush();

            $this->addFlash("success", "Note ajoutée à " . $student->getUsername());

            return $this->redirectToRoute("view_student", ["id" => $id, "subjectID" => $this->getUser()->getSubject()->getId()]);
        }

        ////////////////////////////////////////////////////////

        $subject = $subjectRepo->find($subjectID);
        $subjects = $subjectRepo->findAll();

        $grades = $gradeRepo->findBySubjectStudent($subject, $student, $trimester);

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

        $appreciations = $appRepo->findBySubjectStudent($subjectRepo->find($subjectID), $student);

        return $this->render('student/view.html.twig',
        [
            "student" => $student,
            "gradeForm" => $gradeForm,
            "grades" => $grades,
            "subjects" => $subjects,
            "currentSubject" => $subject,
            "average" => round($average, 2),
            "appreciations" => $appreciations,
            "trimester" => $trimester
        ]);
    }

    #[Route("/students/export/{id}/{trimester}", name: "export_student")]
    public function export(UserRepository $repo, GradeRepository $gradeRepo, AppreciationRepository $appRepo, Pdf $pdf, int $id, int $trimester): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette action est reéservée aux enseignants");
            return $this->redirectToRoute("app_grades");
        }
        
        $student = $repo->find($id);

        $grades = $gradeRepo->findForStudentByTrimester($student, $trimester);
        $appreciations = $appRepo->findByStudentTrimester($student, $trimester);

        $average = 0.0;
        foreach($grades as $g)
        {
            $average += $g;
        }

        if(count($grades) > 0)
        {
            $average /= count($grades);
        }

        $html = $this->renderView('student/pdf.html.twig',
        [
            'grades' => $grades,
            'appreciations' => $appreciations,
            "trimester" => $trimester,
            "name" => $student->getUsername(),
            "average" => round($average, 2)
        ]);

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $student->getUsername() . '.pdf"'
            ]
        );
    }

    /////////////////////////////////////////////////////////////////////////////////
}
