<?php

namespace App\Controller;

use App\Repository\GradeRepository;
use App\Repository\SchoolClassRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Knp\Snappy\Pdf;

final class SchoolClassController extends AbstractController
{
    #[Route('/schoolclass', name: 'app_schoolclass')]
    public function index(): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette page est réservée aux enseignants");
            return $this->redirectToRoute("app_grades");
        }
        
        return $this->render('school_class/index.html.twig', [
            'controller_name' => 'SchoolClassController',
        ]);
    }

    #[Route("/schoolclass/{id}", name: "view_schoolclass")]
    public function view(SchoolClassRepository $repo, UserRepository $userRepo, GradeRepository $gradeRepo, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette page est réservée aux enseignants");
            return $this->redirectToRoute("app_grades");
        }
        
        $schoolClass = $repo->find($id);

        $students = $userRepo->findBySchoolClass($schoolClass);

        $average = 0.0;
        $minAverage = 20.0;
        $maxAverage = 0.0;
        $averageByStudent = [];

        foreach($students as $s)
        {
            $studentAverage = 0.0;

            $grades = $gradeRepo->findBySubjectStudent($this->getUser()->getSubject(), $s);

            foreach($grades as $g)
            {
                $studentAverage += $g->getValue();
            }

            if(count($grades) > 0)
            {
                $studentAverage /= count($grades);
            }
            
            $average += $studentAverage;

            $averageByStudent [] = round($studentAverage, 2);

            /////////////////////////////////////

            if($studentAverage < $minAverage)
            {
                $minAverage = round($studentAverage, 2);
            }

            if($studentAverage > $maxAverage)
            {
                $maxAverage = round($studentAverage, 2);
            }
        }

        if(count($students) > 0)
        {
            $average /= count($students);
        }

        return $this->render('school_class/view.html.twig',
        [
            'schoolClass' => $schoolClass,
            "average" => round($average, 2),
            "minAverage" => $minAverage,
            "maxAverage" => $maxAverage,
            "averageByStudent" => $averageByStudent
        ]);
    }

    #[Route("/schoolclass/export/{id}", name: "export_schoolclass")]
    public function exportPDF(SchoolClassRepository $repo, Pdf $pdf, int $id) : Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(in_array("ROLE_STUDENT", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Cette action est réservée aux enseignants");
            return $this->redirectToRoute("app_grades");
        }

        $schoolClass = $repo->find($id);

        $html = $this->renderView('school_class/pdf.html.twig',
        [
            'schoolClass' => $schoolClass,
        ]);

        return new Response(
            $pdf->getOutputFromHtml($html),
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="class.pdf"'
            ]
        );

        //return $this->redirectToRoute("view_schoolclass", ["id" => $id]);
    }
}
