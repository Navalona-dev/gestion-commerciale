<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Entity\FactureComptabilite;
use App\Service\ApplicationManager;
use App\Repository\DepenseRepository;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ComptabiliteService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $application;
    private $depenseRepo;
    private $security;
    private $twig;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        ApplicationManager  $applicationManager,
        DepenseRepository $depenseRepo,
        Security $security,
        Environment $twig

    )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->depenseRepo = $depenseRepo;
        $this->security = $security;
        $this->twig = $twig;

    }

    public function add($methodePaiement = null, $facture = null)
    {
        $methodePaiement->setDateCreation(new \DateTime);
        $methodePaiement->setFacture($facture);
        $methodePaiement->setApplication($this->application);
        $this->entityManager->persist($methodePaiement);
        $this->entityManager->flush();

        return $methodePaiement;
    }

    public function remove($entity)
    {
        $this->entityManager->remove($entity);
        return $entity;
    }

    public function update()
    {
        $this->entityManager->flush();
    }

    public function addComptabilite($comptabilite = null, $folder = null, $request = null, $depenses = null, $benefice = null)
    {   
        $user = $this->security->getUser();

        //créer nouveau benefice
        $comptabilite->setDateCreation(new \DateTime());
        $comptabilite->setApplication($this->application);

        $totalDepense = 0;
        $totalBenefice = $benefice->getTotal();

        if(count($depenses) > 0) {
            foreach($depenses as $depense) {
                $updateDepense = $this->depenseRepo->findOneBy(['id' => $depense['id']]);
                $updateDepense->addComptabilite($comptabilite);
                $comptabilite->addDepense($updateDepense);
                $this->entityManager->persist($updateDepense);
    
                $totalDepense += $depense['total']; 
            }
        }


        $comptabilite->addBenefice($benefice);
        $benefice->addComptabilite($comptabilite);

        $resultat = $totalBenefice - $totalDepense;
        $comptabilite->setReste($resultat);

        $this->entityManager->persist($benefice);
        $this->entityManager->persist($comptabilite);

        //créer la facture benefice
        $factureComptabilite = new FactureComptabilite();
        $date = new \DateTime();

        $numeroFacture = 1;

        $tabNumeroFacture = $this->getLastValideFacture();

        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }

        $factureComptabilite->setNumero($numeroFacture);
        $factureComptabilite->setApplication($this->application);

        $factureComptabilite->setEtat('regle');
        $factureComptabilite->setValid(true);
        $factureComptabilite->setStatut('regle');
        $factureComptabilite->setDateCreation($date);
        $factureComptabilite->setDate($date);
        $factureComptabilite->setType("Facture");

        //$depenses = $affaire->getProducts();
        $filename = 'Comptabilite' . '-' . $factureComptabilite->getNumero() . ".pdf";
       
        $factureComptabilite->setFile($filename);
        $factureComptabilite->setSolde($resultat);
        $factureComptabilite->setPrixHt($resultat);    
        $factureComptabilite->setReglement($resultat);    
        $factureComptabilite->setComptabilite($comptabilite);    
        
        $this->entityManager->persist($factureComptabilite);

        //dd($factureComptabilite->getSolde());

        $this->update();
        
        // Initialize Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $pdf = new Dompdf($options);

        // Load HTML content
        $data = [];
        $data['factureComptabilite'] = $factureComptabilite;
        $data['application'] = $this->application;
        $data['user'] = $user;
        $data['comptabilite'] = $comptabilite;
        $data['benefice'] = $benefice;
        $data['totalDepense'] = $totalDepense;
        
        $html = $this->twig->render('admin/comptabilite/facturePdf.html.twig', $data);

        // Load HTML to Dompdf
        $pdf->loadHtml($html);

        // (Optional) Set paper size and orientation
        $pdf->setPaper('A4', 'portrait');

        // Render PDF
        $pdf->render();

        // Get PDF content
        $pdfContent = $pdf->output();

        // Save PDF to file
        $fileName = $folder . $filename;
        file_put_contents($fileName, $pdfContent);

        return [$pdfContent, $factureComptabilite, $comptabilite]; 
    }

    public function getLastValideFacture()
    {
        return $this->entityManager->getRepository(FactureComptabilite::class)->getLastValideFacture();
    }
}