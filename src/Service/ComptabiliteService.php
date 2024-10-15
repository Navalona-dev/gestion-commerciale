<?php
namespace App\Service;

use App\Entity\FactureComptabilite;
use App\Service\ApplicationManager;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ComptabiliteService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $application;


    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        ApplicationManager  $applicationManager
    )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();

    }

    public function add($methodePaiement = null, $facture = null)
    {
        $methodePaiement->setDateCreation(new \DateTime);
        $methodePaiement->setFacture($facture);
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
        $comptabilite->setBenefice($benefice);
        $comptabilite->setApplication($this->application);

        $totalDepense = 0;
        $totalBenefice = $benefice->getTotal();

        foreach($depenses as $depense) {
            $depense->setComptabilite($comptabilite);
            $comptabilite->addDepense($depense);
            $this->entityManager->persist($depense);

            $totalDepense += $depense->getTotal(); 
        }

        $resultat = $totalBenefice - $totalDepense;
        $comptabilite->setReste($resultat);
        $comptabilite->setStatus();

        $this->entityManager->persist($comptabilite);

        //créer la facture benefice
        $factureBenefice = new FactureBenefice();
        $date = new \DateTime();

        $numeroFacture = 1;
        //dd('ici');

        $tabNumeroFacture = $this->getLastValideFacture();

        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }

        $factureBenefice->setNumero($numeroFacture);
        $factureBenefice->setApplication($this->application);

        $factureBenefice->setEtat('regle');
        $factureBenefice->setValid(true);
        $factureBenefice->setStatut('regle');
        $factureBenefice->setDateCreation($date);
        $factureBenefice->setDate($date);
        $factureBenefice->setType("Facture");

        //$depenses = $affaire->getProducts();
        $filename = 'Benefice' . '-' . $factureBenefice->getNumero() . ".pdf";
       
        $factureBenefice->setFile($filename);
        $factureBenefice->setSolde($total);
        $factureBenefice->setPrixHt($total);    
        $factureBenefice->setReglement($total);    
        
        $this->entityManager->persist($factureBenefice);

        //dd($factureBenefice->getSolde());

        $this->update();
        
        // Initialize Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $pdf = new Dompdf($options);

        // Load HTML content
        $data = [];
        $data['factureBenefice'] = $factureBenefice;
        $data['application'] = $this->application;
        $data['user'] = $user;
        $data['facturesToday'] = $facturesToday;
        $data['benefice'] = $comptabilite;
        
        $html = $this->twig->render('admin/benefice/facturePdf.html.twig', $data);

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

        return [$pdfContent, $factureBenefice, $comptabilite]; 
    }

    public function getLastValideFacture()
    {
        return $this->entityManager->getRepository(FactureComptabilite::class)->getLastValideFacture();
    }
}