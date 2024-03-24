<?php

namespace App\Controller;

use App\Entity\Inscription;
use App\Entity\Employe;
use App\Form\RechercheType;
use App\Form\Recherche2Type;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;

class RechercheController extends AbstractController
{
    #[Route('/recherche', name: 'app_recherche')]
    public function index(): Response
    {
        return $this->render('recherche/index.html.twig', [
            'controller_name' => 'RechercheController',
        ]);
    }

    #[Route('/rechercheFindBy', name: 'app_recherche_findBy')]
    public function rechercheFindByAction(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(RechercheType::class);
        $form->handleRequest($request);
    
        $employes = [];
        $inscriptions = [];
    
        if ($form->isSubmitted() && $form->isValid()) {
            $employe = $form->getData();
    
            $nom = $employe->getNom();
            $prenom = $employe->getPrenom();
    
            $employes = $doctrine->getRepository(Employe::class)->findBy(['nom' => $nom, 'prenom' => $prenom, 'statut' => '0']);
    
            $inscriptions = $doctrine->getManager()->getRepository(Inscription::class)->rechInscriptionsEmploye($nom, $prenom);
            
        }
    
        return $this->render('recherche/index.html.twig', [
            'form' => $form->createView(),
            'employes' => $employes,
            'inscriptions' => $inscriptions
        ]);
    }

    #[Route('/rechercheFindByProduit', name: 'app_recherche_findBy_produit')]
    public function rechercheFindByProduitAction(Request $request, ManagerRegistry $doctrine): Response
    {
        $form = $this->createForm(Recherche2Type::class);
        $form->handleRequest($request);
    
        $inscriptions = [];
    
        if ($form->isSubmitted() && $form->isValid()) {
            $produit = $form->getData()->getProduit();
    
            if ($produit) {
                $inscriptions = $doctrine->getRepository(Inscription::class)->rechInscriptionsParProduit($produit->getLibelle());
            }
        }
    
        return $this->render('recherche/index2.html.twig', [
            'form' => $form->createView(),
            'inscriptions' => $inscriptions
        ]);
    }
    
    
    

}
