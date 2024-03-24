<?php

namespace App\Controller;

use App\Entity\Employe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\Formation;
use App\Entity\Inscription;
use App\Form\LoginType;


class EmpLambdaController extends AbstractController
{
    #[Route('/emplambda', name: 'app_emplambda')]
    public function index(): Response
    {
        return $this->render('emplambda/index.html.twig', [
            'controller_name' => 'EmplambdaController',
        ]);
    }

    #[Route('/afflambda', name:'app_afflambda')]
    public function afficheLesformations(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }
        
        $nomEmploye = $employe->getNom();
        $prenomEmploye = $employe->getPrenom();

        $formations = $doctrine->getManager()->getRepository(Formation::class)->findFormationsNonInscritesPourEmploye($employe);
        $message = "";

            return $this->render('emplambda/index.html.twig', array('formations' => $formations, 'employe' => $employe, 'message' =>$message));
        
    }


    #[Route('/afflambda_inscription/{idForm}', name:'app_afflambda_inscription')]
    public function inscription($idForm, Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $idEmploye = $session->get('idEmploye');

        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($idForm);

        $inscription = new Inscription();
        $inscription->setStatut("En cours");

        $inscription->setEmploye($employe);
        $inscription->setFormation($formation);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($inscription);
        $entityManager->flush();
        $message = "Nous avons le plaisir de vous confirmer votre inscription à la formation \"".$formation->getNom(). "\" !";
        $session->getFlashBag()->add('success', $message);

        return $this->redirectToRoute('aff_afflambda_les_inscription');
    }

    #[Route('/afflambda_les_inscription', name:'aff_afflambda_les_inscription')]
    public function afficheLesInscriptions(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $idEmploye = $session->get('idEmploye');
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);
        $employe->getPrenom();
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findBy(['employe' => $idEmploye, 'statut' => "En cours"]);
        if(!$inscriptions)
        {
            $message = "Pas d'inscriptions";
        }
        else {
            $message = null;
        }
        
        //return $this->render('formations/index.html.twig', ['controller_name' => 'EmplambdaController',]);
        return $this->render('emplambda/inscription.html.twig', array('inscriptions' => $inscriptions , 'employe' => $employe, 'message' =>$message));
    }

    #[Route('/inscription_suppr/{idInscription}', name:'app_inscription_suppr')]
    public function suppFormationAction($idInscription, Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $employe = $this->verifEmploye($session, $doctrine, $request);
        if (!$employe) {
            return $this->redirectToRoute('app_login_connexion');
        }

        $formation = $doctrine->getManager()->getRepository(Inscription::class)->find($idInscription);
        $message = "Votre désinscription de la formation \"". $formation->getFormation()->getNom() . "\" a été enregistrée.";
        $entityManager = $doctrine->getManager();
        $entityManager->remove($formation);
        $entityManager->flush();
        $session->getFlashBag()->add('success', $message);
        
        return $this->redirectToRoute('aff_afflambda_les_inscription');
    }

    private function verifEmploye(SessionInterface $session, ManagerRegistry $doctrine, Request $request): ?Employe
    {
        $employe = $session->get('employe');

        if (!$employe || $employe->getStatut() == 1) {
            $message = "Veuillez vous connecter !";
            $login = new Employe();
            $form = $this->createForm(LoginType::class, $login);
            $form->handleRequest($request);
            $formations = $doctrine->getManager()->getRepository(Formation::class)->findAll();
            $this->addFlash('error', $message);
            return null;
        }

        return $employe;
    }


}
