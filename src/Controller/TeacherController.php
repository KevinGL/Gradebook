<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\TeacherType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\PasswordHasherFactoryInterface;

final class TeacherController extends AbstractController
{
    private PasswordHasherFactoryInterface $hasher;

    public function __construct(PasswordHasherFactoryInterface $hasher)
    {
        $this->hasher = $hasher;
    }
    
    #[Route('/teacher/{page}', name: 'app_teacher', requirements: ['page' => '\d+'])]
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
        
        $teachers = $repo->findTeachers($page);

        $nbPages = $repo->findNbPagesTeachers();
        
        return $this->render('teacher/index.html.twig',
        [
            'teachers' => $teachers,
            'nbPages' => $nbPages,
            "page" => $page
        ]);
    }

    #[Route("/teacher/add", name: "add_teacher")]
    public function add(EntityManagerInterface $em, Request $req): Response
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
        
        $teacher = new User();

        $teacher->setRoles(["ROLE_TEACHER"]);

        $form = $this->createForm(TeacherType::class, $teacher);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $teacher->setPassword($this->hasher->getPasswordHasher($teacher)->hash($teacher->getPassword()));

            $em->persist($teacher);
            $em->flush();

            $this->addFlash("success", "Enseignant ajouté");

            return $this->redirectToRoute("app_teacher", ["page" => 1]);
        }

        return $this->render("teacher/add.html.twig",
        [
            "form" => $form
        ]);
    }

    #[Route("/teacher/edit/{id}", name: "edit_teacher")]
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

        $form = $this->createForm(TeacherType::class, $teacher, ["is_edit" => true]);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($teacher);
            $em->flush();

            $this->addFlash("success", "Données enseignant mises à jour");
            return $this->redirectToRoute("app_teacher", ["page" => 1]);
        }

        return $this->render("teacher/edit.html.twig", ["form" => $form]);
    }

    #[Route("/teacher/delete/{id}", name: "delete_teacher")]
    public function delete(Request $req, UserRepository $repo, EntityManagerInterface $em, int $id): Response
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

        $this->addFlash("success", "Enseignant retiré");

        return $this->redirectToRoute("app_teacher", ["page" => 1]);
    }
}
