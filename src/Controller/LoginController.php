<?php

namespace App\Controller;

use App\Entity\Employe;
use App\Form\LoginType;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Psr\Log\LoggerInterface;

class LoginController extends AbstractController
{
    #[Route('/', name: 'app_login')]
    public function index(): Response
    {
        $login = new Employe();
        $message = "";
        $form = $this->createForm(LoginType::class, $login);

        return $this->render('login/index.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }

    #[Route('/loginEmploye', name: 'app_login_connexion')]
    public function login(Request $request, ManagerRegistry $doctrine, SessionInterface $session, LoggerInterface $logger)
    {
        
        $session->remove('idEmploye');
        $session->remove('employe');

        $login = new Employe();
        $form = $this->createForm(LoginType::class, $login);
        $form->handleRequest($request);
    
        $message = '';
    
        if ($form->isSubmitted() && $form->isValid()) {
            $login = $form->getData();
            $loginForm = $login->getLogin();
            $mdpForm = $login->getMdp();
            $mdph = MD5($mdpForm .'15');
    
            $entityManager = $doctrine->getManager();
            $unEmploye = $entityManager->getRepository(Employe::class)->findOneBy(['login' => $loginForm, 'mdp' => $mdph]);
    
            if ($unEmploye) {
                $session->set('idEmploye', $unEmploye->getId());
                $session->set('employe', $unEmploye);
                $logger->info('L\'employé ' . $unEmploye->getLogin() . ' s\'est connecté ! Id : '.$unEmploye->getId().' Nom : '. $unEmploye->getNom(). " Prénom : " . $unEmploye->getPrenom(). " Statut : ". $unEmploye->getStatut());
                if ($unEmploye->getStatut() == 0) {
                    return $this->redirectToRoute('app_afflambda');
                } else if($unEmploye->getStatut() == 1){
                    return $this->redirectToRoute('app_aff_serv');
                }
            } else {
                $message = 'Login ou mot de passe incorrect.';
            }
            
        }
        return $this->render('login/index.html.twig', [
            'form' => $form->createView(),
            'message' => $message,
        ]);
    }
}
