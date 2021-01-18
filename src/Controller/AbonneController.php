<?php

namespace App\Controller;

use App\Entity\Abonne;
use App\Form\AbonneType;
use App\Repository\AbonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface as Encoder;

/**
 * @Route("/admin/abonne")
 * 
 * Toutes les routes de ce controleur vont commencer par "/abonne"
 */
class AbonneController extends AbstractController
{
    /**
     * @Route("/", name="abonne_index", methods={"GET"})
     */
    public function index(AbonneRepository $abonneRepository): Response
    {
        return $this->render('abonne/index.html.twig', [
            'abonnes' => $abonneRepository->findAll(),
        ]);
    }

    /**
     * @Route("/ajouter", name="abonne_new", methods={"GET","POST"})
     */
    public function new(Request $request, Encoder $encoder): Response
    {
        $abonne = new Abonne();
        $form = $this->createForm(AbonneType::class, $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $abonne->setPassword($encoder->encodePassword($abonne, $abonne->getPassword() ));
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($abonne);
            $entityManager->flush();

            return $this->redirectToRoute('abonne_index');
        }

        return $this->render('abonne/new.html.twig', [
            'abonne' => $abonne,
            'form' => $form->createView(),
        ]);
    }

    /**
     * REGEX : \d veut dire le caractère doit être un chiffre
     *          + veut dire que le caractère qui précède peut être présent une ou plusieurs fois
     * 
     * @Route("/{id}", name="abonne_show", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function show(Abonne $abonne): Response
    {
        return $this->render('abonne/show.html.twig', [
            'abonne' => $abonne,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="abonne_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Encoder $encoder, Abonne $abonne): Response
    {
        $form = $this->createForm(AbonneType::class, $abonne);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $mdp =$abonne->getPassword();
            $mdp= $encoder->encodePassword($abonne, $mdp);
            $abonne->setPassword($mdp);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('abonne_index');
        }

        return $this->render('abonne/edit.html.twig', [
            'abonne' => $abonne,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="abonne_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Abonne $abonne): Response
    {
        if ($this->isCsrfTokenValid('delete'.$abonne->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($abonne);
            $entityManager->flush();
        }

        return $this->redirectToRoute('abonne_index');
    }

    /**
     * @Route("/nouveau", name="abonne_nouveau")
     */

    public function nouveau(Request $request, EntityManagerInterface $em, Encoder $encoder){
        $abonne = new Abonne;

        $form= $this->createForm(AbonneType::class, $abonne);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){
            $mdp= $abonne->getPassword();
            $mdp= $encoder->encodePassword($abonne, $mdp);
            $abonne->setPassword($mdp);

            $em->persist($abonne);
            $em->flush();
            $this->addFlash("success", "Le nouveau abonné a bien été enregistré");
            return $this->redirectToRoute("abonne_index");
        }

        return $this->render("abonne/new.html.twig", ["form" => $form->createView()]);

    }
}
