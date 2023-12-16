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


class EmplambdaController extends AbstractController
{
    #[Route('/emplambda', name: 'app_emplambda')]
    public function index(): Response
    {
        return $this->render('emplambda/index.html.twig', [
            'controller_name' => 'EmplambdaController',
        ]);
    }

    #[Route('/app_afflambda', name:'app_afflambda')]
    public function afficheLesformations(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $idEmploye = $session->get('idEmploye');
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);

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

        } else if($employe->getStatut() == 1) {
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
            return $this->render('emplambda/index.html.twig', array('formations' => $formations, 'employe' => $employe, 'message' =>$message));
        }
    }


    #[Route('/app_afflambda_inscription/{idForm}', name:'app_afflambda_inscription')]
    public function inscription($idForm, Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $idEmploye = $session->get('idEmploye');

        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);
        $formation = $doctrine->getManager()->getRepository(Formation::class)->find($idForm);
        /*$inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findBy(['login' => $loginFormulaire]);
        foreach($inscriptions as $uneInscription)
        {
            if($uneInscription->getEmploye() == $idEmploye && $uneInscription->getFormation->getId)
        }*/

        $inscription = new Inscription();
        $inscription->setStatut("En cours");

        $inscription->setEmploye($employe);
        $inscription->setFormation($formation);

        $entityManager = $doctrine->getManager();
        $entityManager->persist($inscription);
        $entityManager->flush();

        return $this->redirectToRoute('aff_afflambda_les_inscription');
    }

    #[Route('/aff_afflambda_les_inscription', name:'aff_afflambda_les_inscription')]
    public function afficheLesInscriptions(ManagerRegistry $doctrine, SessionInterface $session)
    {
        $idEmploye = $session->get('idEmploye');
        $employe = $doctrine->getManager()->getRepository(Employe::class)->find($idEmploye);
        $employe->getPrenom();
        $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->findBy(['employe' => $idEmploye]);
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

    #[Route('/app_inscription_suppr/{idInscription}', name:'app_inscription_suppr')]
    public function suppFormationAction($idInscription, ManagerRegistry $doctrine)
    {
        $formation = $doctrine->getManager()->getRepository(Inscription::class)->find($idInscription);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($formation);
        $entityManager->flush();
        return $this->redirectToRoute('aff_afflambda_les_inscription');
    }


}
