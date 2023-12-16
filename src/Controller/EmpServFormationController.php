<?php

namespace App\Controller;

use App\Entity\Employe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\Formation;
use App\Entity\Inscription;
use App\Form\EmpServFormationType;
use Symfony\Component\HttpFoundation\Request;
use App\Form\LoginType;

class EmpServFormationController extends AbstractController
{
    #[Route('/emp/serv/formation', name: 'app_emp_serv_formation')]
    public function index(): Response
    {
        return $this->render('emp_serv_formation/index.html.twig', [
            'controller_name' => 'EmpServFormationController',
        ]);
    }

    #[Route('/app_aff_serv', name:'app_aff_serv')]
    public function afficheLesformations(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $idEmploye = $session->get('idEmploye');
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);

        //Je vérifie qu'il existe bien un user
        if(!$session->get('idEmploye')) {
            $message = "Veuillez vous connecter !";
            $login = new Employe();
            $form = $this->createForm(LoginType::class, $login);
            $form->handleRequest($request);
            $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();
            return $this->render('login/index.html.twig', [
                'form' => $form->createView(),
                'message' => $message,
            ]);
        } else if($employe->getStatut() == 0) {
            //Je vérifie que le statut correspond bien
            $message = "Veuillez vous connecter à votre compte";
            $login = new Employe();
            $form = $this->createForm(LoginType::class, $login);
            $form->handleRequest($request);
            $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();
            return $this->render('login/index.html.twig', [
                'form' => $form->createView(),
                'message' => $message,
            ]);
        } else {
            $employe->getNom();
            $employe->getPrenom();
            $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();
            foreach($formations as $uneFormation)
            {
                $uneFormation->getId();
                $uneFormation->getDateDebut()->format('Y-m-d');
                $uneFormation->getNbreHeures();
                $uneFormation->getDepartement();
                $uneFormation->getProduit();
            }
            if(!$formations)
            {
                $message = "Pas de formations";
            }
            else {
                $message = null;
            }
            //return $this->render('formations/index.html.twig', ['controller_name' => 'EmplambdaController',]);
            return $this->render('emp_serv_formation/index.html.twig', array('formations' => $formations, 'employe' => $employe, 'message' =>$message));
        }
    }

    #[Route('/aff_creer_form', name:'aff_creer_form')]
    public function creerFormulaire(Request $request, ManagerRegistry $doctrine)
    {
        $login = new Formation();
        $form = $this->createForm(EmpServFormationType::class, $login);
        $form->handleRequest($request);
    
        return $this->render('emp_serv_formation/creer_formation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/app_ajouter_formation', name:'app_ajouter_formation')]
    public function ajoutFormulaire(Request $request, ManagerRegistry $doctrine)
    {
        $formation = new Formation();
        $form = $this->createForm(EmpServFormationType::class, $formation);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $entityManager = $doctrine->getManager();
            $entityManager->persist($formation);
            $entityManager->flush();
    
            return $this->redirectToRoute('app_aff_serv');
        }
    
        return $this->render('emp_serv_formation/creer_formation.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/app_formation_suppr/{id}', name:'app_formation_suppr')]
    public function suppFormationAction($id, ManagerRegistry $doctrine)
    {
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($id);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($formation);
        $entityManager->flush();
        return $this->redirectToRoute('app_aff_serv');
    }

    #[Route('/aff_serv_les_inscription', name:'aff_serv_les_inscription')]
    public function afficheLesInscriptions(ManagerRegistry $doctrine, SessionInterface $session)
    {
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

    #[Route('/app_acceptation_inscription/{idInscription}', name:'app_acceptation_inscription')]
    public function statutAccepteeAction($idInscription, ManagerRegistry $doctrine)
    {
        $formation = $doctrine->getManager()->getRepository(Inscription::class)->find($idInscription);
        $formation->setStatut("Acceptée");
        $entityManager = $doctrine->getManager();
        $entityManager->persist($formation);
        $entityManager->flush();
        return $this->redirectToRoute('aff_serv_les_inscription');
    }

    #[Route('/app_refusee_inscription/{idInscription}', name:'app_refusee_inscription')]
    public function statutRefuseeAction($idInscription, ManagerRegistry $doctrine)
    {
        $formation = $doctrine->getManager()->getRepository(Inscription::class)->find($idInscription);
        $formation->setStatut("Refusée");
        $entityManager = $doctrine->getManager();
        $entityManager->persist($formation);
        $entityManager->flush();
        return $this->redirectToRoute('aff_serv_les_inscription');
    }
    
}
