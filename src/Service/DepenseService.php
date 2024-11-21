<?php
namespace App\Service;

use Dompdf\Dompdf;
use Dompdf\Options;
use Twig\Environment;
use App\Entity\Facture;
use App\Entity\FactureDepense;
use App\Entity\Comptabilite;
use App\Service\ApplicationManager;
use App\Repository\DepenseRepository;
use App\Service\AuthorizationManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DepenseService
{
    private $tokenStorage;
    private $authorization;
    private $entityManager;
    private $application;
    private $security;
    private $twig;
    private $depenseRepo;

    public function __construct(
        AuthorizationManager $authorization, 
        TokenStorageInterface  $TokenStorageInterface, 
        EntityManagerInterface $entityManager,
        ApplicationManager  $applicationManager,
        Security $security,
        Environment $twig,
        DepenseRepository $depenseRepo

    )
    {
        $this->tokenStorage = $TokenStorageInterface;
        $this->authorization = $authorization;
        $this->entityManager = $entityManager;
        $this->application = $applicationManager->getApplicationActive();
        $this->security = $security;
        $this->twig = $twig;
        $this->depenseRepo = $depenseRepo;

    }

    public function add($depense = null, $existeCompta = null)
    {
        $depense->setDateCreation(new \DateTime);
        $depense->setApplication($this->application);
        //dd($existeCompta);
        if ($existeCompta) {
            // Charger `Comptabilite` depuis le repository pour s'assurer qu'il est géré
            $comptaRepo = $this->entityManager->getRepository(Comptabilite::class);
            $existingCompta = $comptaRepo->find($existeCompta->getId());
    
            if (!$existingCompta) {
                throw new \Exception("L'entité Comptabilite n'existe pas ou n'est pas trouvée.");
            }
    
            // Ajouter la dépense sans persister une nouvelle entité
            $existingCompta->addDepense($depense);
            $depense->addComptabilite($existingCompta);
        }
        
        $this->entityManager->persist($depense);
        $this->entityManager->flush();

        return $depense;
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

    public function addFacture($depenses = null, $folder = null, $request = null,)
    {   
        $user = $this->security->getUser();

        $factureDepense = new FactureDepense();
        $date = new \DateTime();

        $numeroFacture = 1;
        $tabNumeroFacture = $this->getLastValideFacture();
        if (count($tabNumeroFacture) > 0) {
            $numeroFacture = $tabNumeroFacture[0] + 1;
        }

        $factureDepense->setNumero($numeroFacture);
        $factureDepense->setApplication($this->application);

        $factureDepense->setEtat('regle');
        $factureDepense->setValid(true);
        $factureDepense->setStatut('regle');
        $factureDepense->setDateCreation($date);
        $factureDepense->setDate($date);
        $factureDepense->setType("Facture");

        //$depenses = $affaire->getProducts();
        $filename = 'Depense' . '-' . $factureDepense->getNumero() . ".pdf";
       
        $prix = 0;
        $sumQtt = 0;
        $sumQttVendu = 0;
        $qttVendu = 0;
        $montantHtTotal = 0;

        foreach($depenses as $key => $depense) {
            $updateDepense = $this->depenseRepo->findOneBy(['id' => $depense['id']]);
            $montantHtTotal += $depense['total'];
            $updateDepense->addFactureDepense($factureDepense);
            $this->entityManager->persist($updateDepense);
            $factureDepense->addDepense($updateDepense);

        }

        $factureDepense->setFile($filename);
        $factureDepense->setSolde($montantHtTotal);
        $factureDepense->setPrixHt($montantHtTotal);    
        $factureDepense->setReglement($montantHtTotal);    
        
        $this->entityManager->persist($factureDepense);

        //dd($factureDepense->getSolde());

        $this->update();
        
        
        // Initialize Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isPhpEnabled', true);
        $pdf = new Dompdf($options);

        // Load HTML content
        $data = [];
        $data['depenses'] = $depenses;
        $data['factureDepense'] = $factureDepense;
        $data['application'] = $this->application;
        $data['user'] = $user;
        
        $html = $this->twig->render('admin/depense/facturePdf.html.twig', $data);

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

        return [$pdfContent, $factureDepense]; // Retourner le contenu PDF et l'objet facture
    }

    public function getLastValideFacture()
    {
        return $this->entityManager->getRepository(FactureDepense::class)->getLastValideFacture();
    }
}