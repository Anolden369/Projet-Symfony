<?php

namespace App\Controller;

use App\Entity\Employe;
use Proxies\__CG__\App\Entity\Produit;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Formation;
use App\Entity\Inscription;
use App\Form\EmpServFormationType;
use App\Form\EmpServProduitType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\LoginType;

class EmpServFormationController extends AbstractController
{
    #[Route('/emp', name: 'app_emp_serv_formation')]
    public function index(): Response
    {
        return $this->render('emp_serv_formation/index.html.twig', [
            'controller_name' => 'EmpServFormationController',
        ]);
    }

    #[Route('/aff_serv', name:'app_aff_serv')]
    public function afficheLesformations(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = "";
        if($session->get('employe')) {
            $employe = $session->get('employe');
        }

        //Je vérifie qu'il existe bien un user
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }
        
        $employe->getNom();
        $employe->getPrenom();
        $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();

        if(!$formations)
        {
            $message = "Pas de formations";
        }
        else {
            $message = null;
        }
            
        return $this->render('emp_serv_formation/index.html.twig', 
        array('formations' => $formations, 
              'employe' => $employe, 
              'message' =>$message
        ));
    }

    #[Route('/creer_form', name:'app_creer_form')]
    public function creerFormulaire(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $login = new Formation();
        $form = $this->createForm(EmpServFormationType::class, $login);
        $form->handleRequest($request);

        return $this->render('emp_serv_formation/creer_formation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ajouter_formation', name:'app_ajouter_formation')]
    public function ajoutFormulaire(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $formation = new Formation();
        $form = $this->createForm(EmpServFormationType::class, $formation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $entityManager = $doctrine->getManager();
            $entityManager->persist($formation);
            $entityManager->flush();
    
            $message = "La formation a été créée avec succès !";
            $session->getFlashBag()->add('info', $message);
            return $this->redirectToRoute('app_aff_serv');
        }
    
        return $this->render('emp_serv_formation/creer_formation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/formation_suppr/{id}', name:'app_formation_suppr')]
    public function suppFormationAction($id, Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $message = "Des employés sont inscrits à la formation, elle ne peut pas être supprimée !";
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);
        $nbFormations = $doctrine->getManager()->getRepository(Inscription::class)->count(['formation' => $id]);

        if ($nbFormations > 0) 
        {
            $session->getFlashBag()->add('danger', $message);
            return $this->redirectToRoute('app_aff_serv');
        } else {
            $message = "La formation a été supprimée !";
            $session->getFlashBag()->add('danger', $message);
            $entityManager = $doctrine->getManager();
            $entityManager->remove($formation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_aff_serv');
    }

    #[Route('/serv_les_inscriptions', name:'app_serv_les_inscriptions')]
    public function afficheLesInscriptions(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $idEmploye = $session->get('idEmploye');
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);
        $employe->getPrenom();
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findAll();
        if(!$inscriptions)
        {
            $message = "Pas d'inscriptions";
        }
        else {
            $message = null;
        }
        
        //return $this->render('formations/index.html.twig', ['controller_name' => 'EmplambdaController',]);
        return $this->render('emp_serv_formation/inscription_serv.html.twig', array('inscriptions' => $inscriptions , 'employe' => $employe, 'message' =>$message));
    }

    #[Route('/acceptation_inscription/{idInscription}', name:'app_acceptation_inscription')]
    public function statutAccepteeAction($idInscription, Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $formation = $doctrine->getManager()->getRepository(Inscription::class)->find($idInscription);
        $formation->setStatut("Acceptée");
        $entityManager = $doctrine->getManager();
        $entityManager->persist($formation);
        $entityManager->flush();
        $message = "L'inscription a été acceptée !";
        $session->getFlashBag()->add('success', $message);
        
        return $this->redirectToRoute('app_serv_les_inscriptions');
    }

    #[Route('/refusee_inscription/{idInscription}', name:'app_refusee_inscription')]
    public function statutRefuseeAction($idInscription, Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $formation = $doctrine->getManager()->getRepository(Inscription::class)->find($idInscription);
        $formation->setStatut("Refusée");
        $entityManager = $doctrine->getManager();
        $entityManager->persist($formation);
        $entityManager->flush();
        $message = "L'inscription a été refusée !";
        $session->getFlashBag()->add('info', $message);
        
        return $this->redirectToRoute('app_serv_les_inscriptions');
    }

    #[Route('/ajouter_produit', name:'app_ajouter_produit')]
    public function ajoutProduit(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $formation = new Produit();
        $form = $this->createForm(EmpServProduitType::class, $formation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $entityManager = $doctrine->getManager();
            $entityManager->persist($formation);
            $entityManager->flush();
    
            $message = "Le produit a été créé avec succès !";
            $session->getFlashBag()->add('success', $message);
            return $this->redirectToRoute('app_aff_serv');
        }
    
        return $this->render('emp_serv_formation/creer_produit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    private function verifEmploye(SessionInterface $session, ManagerRegistry $doctrine, Request $request): ?Employe
    {
        $employe = $session->get('employe');

        if (!$employe || $employe->getStatut() == 0) {
            $message = "Veuillez vous connecter !";
            $login = new Employe();
            $form = $this->createForm(LoginType::class, $login);
            $form->handleRequest($request);
            $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();
            $this->addFlash('danger', $message);
            return null;
        }

        return $employe;
    }
    
}
