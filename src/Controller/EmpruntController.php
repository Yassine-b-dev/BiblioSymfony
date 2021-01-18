<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\EmpruntRepository;
use App\Entity\Emprunt;
use App\Form\EmpruntType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin")
 * 
 */

class EmpruntController extends AbstractController
{
    /**
     * @Route("/emprunt", name="emprunt")
     */
    public function index(EmpruntRepository $empruntRepository): Response
    {
        $liste = $empruntRepository->findAll();
        return $this->render('emprunt/index.html.twig', [
            'emprunts' => $liste
        ]);
    }

    /**
     * @Route("/emprunt/ajouter", name="emprunt_ajouter")
     */

    public function ajouter(Request $request, EntityManagerInterface $em)
    {
        $emprunt = new Emprunt;
        $formEmprunt = $this->createForm(EmpruntType::class, $emprunt);
        $formEmprunt->handleRequest($request);
        if($formEmprunt->isSubmitted() && $formEmprunt->isValid()){
            $em->persist($emprunt);
            $em->flush();
            $this->addFlash("success", "Nouvel emprunt enregistré");
            return $this->redirectToRoute("emprunt");
        }
        return $this->render("emprunt/ajouter.html.twig", ["formEmprunt" => $formEmprunt]);
    }
}
