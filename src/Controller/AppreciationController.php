<?php

namespace App\Controller;

use App\Entity\Appreciation;
use App\Entity\Grade;
use App\Form\AppreciatonType;
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

final class AppreciationController extends AbstractController
{
    #[Route("/appreciation/edit/{id}", name: "edit_app")]
    public function edit(Request $req, EntityManagerInterface $em, AppreciationRepository $repo, int $id): Response
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

        $appreciation = $repo->find($id);

        if($appreciation->getSubject()->getId() != $this->getUser()->getSubject()->getId())
        {
            $this->addFlash("error", "Vous ne pouvez pas modifier cette apréciation");
            return $this->redirectToRoute("view_student", ["id" => $appreciation->getStudent()->getId(), "subjectID" => $this->getUser()->getSubject()->getId()]);
        }

        $form = $this->createForm(AppreciatonType::class, $appreciation);
        $form->handleRequest($req);

        if($form->isSubmitted() && $form->isValid())
        {
            $em->persist($appreciation);
            $em->flush();

            $this->addFlash("success", "Appréciation mise à jour");

            return $this->redirectToRoute("view_student", ["id" => $appreciation->getStudent()->getId(), "subjectID" => $this->getUser()->getSubject()->getId()]);
        }

        return $this->render("appreciation/edit.html.twig",
            [
                "form" => $form,
                "student" => $appreciation->getStudent()->getId(),
                "subject" => $appreciation->getSubject()->getId(),
                "trimester" => $appreciation->getTrimester()->getId()
            ]);
    }
}