<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Entity\Facture;
use App\Entity\Benefice;
use App\Entity\FactureBenefice;
use App\Service\ApplicationManager;
use App\Repository\FactureRepository;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class BeneficeService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $application;
    private $security;
    private $twig;
    private $factureRepo;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        ApplicationManager  $applicationManager,
        Security $security,
        Environment $twig,
        FactureRepository $factureRepo

    )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->security = $security;
        $this->twig = $twig;
        $this->factureRepo = $factureRepo;

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

    public function add($benefice = null, $folder = null, $request = null,$espece = null, $total = null, $mobileMoney = null, $factures = null)
    {   
        $user = $this->security->getUser();

        //créer nouveau benefice
        $benefice->setEspece($espece);
        $benefice->setDateCreation(new \DateTime());
        $benefice->setMobileMoney($mobileMoney);
        $benefice->setTotal($total);
        $benefice->setApplication($this->application);

        foreach($factures as $facture) {
            $facture = $this->factureRepo->findOneBy(['id' => $facture['id']]);
            $facture->setBenefice($benefice);
            $facture->setIsBenefice(true);
            $benefice->addFacture($facture);
            $this->entityManager->persist($facture);
        }

        $this->entityManager->persist($benefice);

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
        $filename = 'Encaissement' . '-' . $factureBenefice->getNumero() . ".pdf";
       
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
        $data['factures'] = $factures;
        $data['benefice'] = $benefice;
        
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

        return [$pdfContent, $factureBenefice, $benefice]; 
    }

    public function getLastValideFacture()
    {
        return $this->entityManager->getRepository(FactureBenefice::class)->getLastValideFacture();
    }
}