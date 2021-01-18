<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Livre;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\LivreRepository;
use App\Form\LivreType;

/**
 * @Route("/admin")
 */

class LivreController extends AbstractController
{
    /**
     * @Route("/livre", name="livre")
     * 
     * Pour interroger une table de la bdd (= requête SELECT), on va utiliser la classe Repository correspondante
     * (donc pour la table 'livre', on va utiliser 'LivreRepository')
     */
    public function index(LivreRepository $livreRepository): Response
    {
        $liste_livres = $livreRepository->findAll(); // SELECT * FROM livre
        return $this->render('livre/index.html.twig', [
            'livres' => $liste_livres,
        ]);
    }

    /**
     * @Route("/livre/ajouter", name="livre_ajouter")
     */

    public function nouveau(Request $request, EntityManagerInterface $em){
        $livre = new Livre;
        $formLivre = $this->createForm(LivreType::class, $livre); // on relie l'obj de la class livre au donnée du form
        $formLivre->handleRequest($request);
        if( $formLivre->isSubmitted() && $formLivre->isValid()){


            if($fichier = $formLivre->get("couverture")->getData()){
                $destination = $this->getParameter("dossier_images");
                $nomCouverture = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $nouveauNom = str_replace(" ", "_", $nomCouverture);
                $nouveauNom .= "-" . uniqid() . "." . $fichier->guessExtension();
                /* le fichier uploadé est enregistré dans un dossier temporaire. On va le déplacer vers le dossier images avec le nouveau nom de fichier */
                $fichier->move($destination, $nouveauNom);
                $livre->setCouverture($nouveauNom);
            }
                $em->persist($livre);
                $em->flush();
    
                $this->addFlash("success", "Le livre a bien été enregistrée");

                return $this->redirectToRoute("livre");
            
        }
        return $this->render('livre/ajouter.html.twig', [
            'formLivre' => $formLivre->createView()
        ]);
    }

    /**
     * @Route("/livre/modifier/{id}", name="livre_modifier")
     */
    public function maj (EntityManagerInterface $em, Request $request, LivreRepository $livreRepository, $id){
        $livreAmodifier = $livreRepository->find($id);
        $formLivre = $this->createForm(LivreType::class, $livreAmodifier); // on relie l'obj de la class livre au donnée du form
        $formLivre->handleRequest($request);
        if( $formLivre->isSubmitted() && $formLivre->isValid()){
            
            if($fichier = $formLivre->get("couverture")->getData()){
                $destination = $this->getParameter("dossier_images");
                $nomCouverture = pathinfo($fichier->getClientOriginalName(), PATHINFO_FILENAME);
                $nouveauNom = str_replace(" ", "_", $nomCouverture);
                $nouveauNom .= "-" . uniqid() . "." . $fichier->guessExtension();
                /* le fichier uploadé est enregistré dans un dossier temporaire. On va le déplacer vers le dossier images avec le nouveau nom de fichier */
                $fichier->move($destination, $nouveauNom);
                $livreAmodifier->setCouverture($nouveauNom);
            }
            $em->flush();
            $this->addFlash("success", "Le livre n°$id a bien été modifié");
            return $this->redirectToRoute("livre");

        }
        return $this->render('livre/ajouter.html.twig', [
            'formLivre' => $formLivre->createView(),
            'livre' => $livreAmodifier
        ]);
    }





    /**
     * @Route("/livre/ajouter", name="livre_ajouter_V1")
     */
    public function ajouter(Request $request, EntityManagerInterface $em){
        /* La classe Request a des propriétés qui correspondent à toutes les variables superglobales de PHP
            ex : $_SERVER, $_POST, $_COOKIE, $_GET, $_SESSION, $_FILES

            Pour utiliser certaines classes (nommées des services), on va utiliser l'injection de dépendance : 
            on va placer un objet de cette classe dans les paramètres d'une méthode, et l'objet sera instancié automatiquement
            (par ex: l'objet de la classe Request contiendra toutes les valeurs des variables superglobales)

            Pour récupérer le contenu de $_POST, on utilise la propriété 'request' de cet objet
            Pour récupérer le contenu de $_GET, on utilise la propriété 'query" de cet objet
            ----------------------------------------------------------------------------------------------------------
            La classe EntityManagerInterface va nous servir à modifier (insérer, mettre à jour, supprimer) des données
            dans les tables de la BDD
        */
        if( $request->request->has("titre") ){
            $titre = $request->request->get("titre");
        }
        if( $request->request->has("auteur")) {
            $auteur = $request->request->get("auteur");
        }

        if( !empty($titre) && !empty($auteur) ){
            $nouveauLivre = new Livre;
            $nouveauLivre->setTitre($titre);
            $nouveauLivre->setAuteur($auteur);

            /* La méthode 'persist' prépare la requête 'insert into' et la met en attente */
            $em->persist($nouveauLivre);
            /* La méthode 'flush' exécuter les requêtes en attente et donc modifie la bdd */
            $em->flush();

            /* La méthode 'addFlash' permet d'enregistrer dans la session, un message à afficher. Le 1er paramètre est le type du message
                (par ex: success, danger, warning,...), le 2iè paramètre est le message à afficher.
                Ces messages resteront dans la session tant qu'ils n'ont pas été affichés
            */
            $this->addFlash("success", "Le nouveau livre a bien été enregistré");
            return $this->redirectToRoute("livre");
        }
        

        return $this->render("livre/formulaire.html.twig");
    }

    

    /**
     * @Route("/livre/modifier/{id}", name="livre_modifier_V1")
     */
    public function modifier(EntityManagerInterface $em, Request $request, LivreRepository $livreRepository, $id)
    {
        /* la méthode 'find' récupère le livre dont l'identifiant est passé en paramètre.
            $livreAmodifier sera donc un objet de la classe Entity\Livre
        */
        $livreAmodifier = $livreRepository->find($id);

        if( $request->isMethod("POST") ){
            $titre = $request->request->get("titre");   // je récupère la valeur de l'input 'titre' du formulaire
            $auteur = $request->request->get("auteur");
            if(!empty($titre) && !empty($auteur)){
                $livreAmodifier->setTitre($titre);
                $livreAmodifier->setAuteur($auteur);
                /* Tous les objets de la classe Entity qui ont un id non nul vont permettre à l'EntityManager  
                    d'exécuter une requete UPDATE pour mettre à jour la bdd selon les modifications des propriétés
                    de ces objets quand on va lancer la méthode 'flush' de l'EntityManager
                */
                $em->flush();
                $this->addFlash("success", "Le livre n°$id a bien été modifié");
                return $this->redirectToRoute("livre");
            } else {
                $this->addFlash("danger", "Le titre et/ou l'auteur ne peuvent pas être vide");
            }
        }

        return $this->render("livre/formulaire.html.twig", [ "livre" => $livreAmodifier ]);
    }

    /**
     * @Route("/livre/supprimer/{id}", name="livre_supprimer")
     */
    public function supprimer(EntityManagerInterface $em, Request $request, LivreRepository $livreRepository, $id)
    {
        $livreAsupprimer = $livreRepository->find($id);
        if( $request->isMethod("POST") ){
            $em->remove($livreAsupprimer);   // La méthode 'remove' prépare la requête "DELETE" et la met en attente
            $em->flush();
            $this->addFlash("success", "Le livre n°$id a bien été supprimé");
            return $this->redirectToRoute("livre");
        }
        return $this->render("livre/supprimer.html.twig", [ "livre" => $livreAsupprimer]);
    }

    /**
     * 
     * @Route("voir/{id}", name="livre_voir", methods={"GET"}, requirements={"id"="\d+"})
     */
    public function voir(Livre $livre): Response{

        return $this->render('livre/voir.html.twig', ['livre' => $livre]);
    
    }


}
