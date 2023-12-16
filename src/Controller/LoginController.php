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

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function index(): Response
    {
        $login = new Employe();
        $form = $this->createForm(LoginType::class, $login);

        return $this->render('login/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/loginEmploye', name: 'app_login_ajouter')]
    public function login(Request $request, ManagerRegistry $doctrine, SessionInterface $session)
    {
        $session->remove('idEmploye');

        $login = new Employe();
        $form = $this->createForm(LoginType::class, $login);
        $form->handleRequest($request);
    
        $message = '';
    
        if ($form->isSubmitted() && $form->isValid()) {
            $login = $form->getData();
            $loginFormulaire = $login->getLogin();
            $passwordFormulaire = $login->getMdp();
    
            $entityManager = $doctrine->getManager();
            $unEmploye = $entityManager->getRepository(Employe::class)->findOneBy(['login' => $loginFormulaire]);
    
            if ($unEmploye && $unEmploye->getMdp() === $passwordFormulaire) {
                $session = new Session();
                $session->set('idEmploye', $unEmploye->getId());
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


// CE QUE JE DOIS FAIRE 


// Ne pas s'inscrire deux fois une personne à la meme formation
// verifier la variable de session avant chaque chargement de la page
// pour le user, voir uniquement les formations en cours et pas ceux acceptée ou refusée


//POUR LA SESSION
// Je récupère l'id de la session
// si s'il existe un id pour une session
// j'accède au contenu ou à la page
// sinon je sors et je retourne à la page de login de connexion