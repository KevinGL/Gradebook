<?php

namespace App\Controller;

use App\Entity\Appreciation;
use App\Entity\Grade;
use App\Entity\User;
use App\Form\GradeType;
use App\Form\StudentType;
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
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class StudentController extends AbstractController
{
    private PasswordHasherFactoryInterface $hasher;

    public function __construct(PasswordHasherFactoryInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
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

            return $this->redirectToRoute("view_student", ["id" => $id, "subjectID" => $this->getUser()->getSubject()->getId(), "trimester" => $trimester]);
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

    #[Route("/student/{page}", name: "app_student", requirements: ['page' => '\d+'])]
    public function index(UserRepository $repo, int $page): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Page réservée aux admins");
            return $this->redirectToRoute("app_grades");
        }

        $students = $repo->findStudents($page);
        $nbPages = $repo->findNbPagesStudents();

        return $this->render("student/index.html.twig",
        [
            "students" => $students,
            "nbPages" => $nbPages,
            "page" => $page
        ]);
    }

    #[Route("/student/add", name: "add_student")]
    public function add(Request $req, EntityManagerInterface $em): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Page réservée aux admins");
            return $this->redirectToRoute("app_grades");
        }
        
        $student = new User();

        $form = $this->createForm(StudentType::class, $student);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $student->setPassword($this->hasher->getPasswordHasher($student)->hash($student->getPassword()));
            $student->setRoles(["ROLE_STUDENT"]);

            $em->persist($student);
            $em->flush();

            $this->addFlash("success", "Elève ajouté");

            return $this->redirectToRoute("app_student", ["page" => 1]);
        }

        return $this->render("student/add.html.twig", ["form" => $form]);
    }

    #[Route("/student/edit/{id}", name: "edit_student")]
    public function edit(Request $req, UserRepository $repo, EntityManagerInterface $em, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Page réservée aux admins");
            return $this->redirectToRoute("app_grades");
        }
        
        $teacher = $repo->find($id);

        $form = $this->createForm(StudentType::class, $teacher, ["is_edit" => true]);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($teacher);
            $em->flush();

            $this->addFlash("success", "Données élève mises à jour");
            return $this->redirectToRoute("app_student", ["page" => 1]);
        }

        return $this->render("student/edit.html.twig", ["form" => $form]);
    }

    #[Route("/student/delete/{id}", name: "delete_student")]
    public function delete(UserRepository $repo, EntityManagerInterface $em, int $id): Response
    {
        if(!$this->getUser())
        {
            return $this->redirectToRoute("app_home");
        }

        if(!in_array("ROLE_ADMIN", $this->getUser()->getRoles()))
        {
            $this->addFlash("error", "Page réservée aux admins");
            return $this->redirectToRoute("app_grades");
        }
        
        $teacher = $repo->find($id);

        $em->remove($teacher);
        $em->flush();

        $this->addFlash("success", "Elève retiré");

        return $this->redirectToRoute("app_student", ["page" => 1]);
    }
}
