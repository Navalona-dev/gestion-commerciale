<?php
namespace App\Service;

use PhpOffice\PhpSpreadsheet\IOFactory;
use Doctrine\ORM\EntityManagerInterface;

class ExcelImporter
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function import(string $filePath): void
    {
        $spreadsheet = IOFactory::load($filePath);
        $sheet = $spreadsheet->getActiveSheet();
        foreach ($sheet->getRowIterator() as $index => $row) {
            if ($index == 1) {
                continue; // Ignore the header row
            }

            $data = [];

            foreach ($row->getCellIterator() as $cell) {
                $data[] = $cell->getValue();
            }

            $order = new Order();
            $order->setCompteAffaire($data[0]);
            $order->setCompteEvenement($data[1] ?? null);
            $order->setCompteDernierEvenement($data[2] ?? null);
            $order->setNumeroFiche($data[3] ?? null);
            $order->setLibelleCivilite($data[4] ?? null);
            $order->setProprietaireActuelVehicule($data[5] ?? null);
            $order->setNom($data[6] ?? null);
            $order->setPrenom($data[7]);
            $order->setNumeroEtNomVoie($data[8] ?? null);
            $order->setComplement($data[9] ?? null);
            $order->setCodePostal($data[10] ?? null);
            $order->setVille($data[11] ?? null);
            $order->setTelephoneDomicile($data[12] ?? null);
            $order->setTelephonePortable($data[13] ?? null);
            $order->setTelephoneJob($data[14] ?? null);
            $order->setEmail($data[15] ?? null);

            $dateMiseCirculation = !empty($data[16]) ? \DateTime::createFromFormat('m/d/y', $data[16]) : null;
            if ($dateMiseCirculation === false) {
                $dateMiseCirculation = null;
            }

            $order->setDateMiseCirculation($dateMiseCirculation);

            $dateAchat = !empty($data[17]) ? \DateTime::createFromFormat('m/d/y', $data[17]) : null;
            if ($dateAchat === false) {
                $dateAchat = null;
            }
            $order->setDateAchat($dateAchat);

            $dateDernierEvenement = \DateTime::createFromFormat('m/d/y', $data[18]);
            if ($dateDernierEvenement === false) {
                $dateDernierEvenement = null; 
            }
            $order->setDateDernierEvenement($dateDernierEvenement);

            $order->setLibelleMarque($data[19] ?? null);
            $order->setLibelleModele($data[20] ?? null);
            $order->setVersion($data[21] ?? null);
            $order->setVin($data[22] ?? null);
            $order->setImmatriculation($data[23] ?? null);
            $order->setTypeProspect($data[24] ?? null);
            $order->setKilometrage($data[25] ?? null);
            $order->setLibelleEnergie($data[26] ?? null);
            $order->setVendeurVN($data[27] ?? null);
            $order->setVendeurVO($data[28] ?? null);
            $order->setCommentaireFacturation($data[29] ?? null);
            $order->setTypeVNVO($data[30] ?? null);
            $order->setNumeroDossierVNVO($data[31] ?? null);
            $order->getIntermediaireVenteVN($data[32] ?? null);
            $dateEvenement = \DateTime::createFromFormat('m/d/y', $data[33]);
            if ($dateEvenement === false) {
                $dateEvenement = null; 
            }
            $order->setDateEvenement($dateEvenement);

            $order->setOrigineEvenement($data[34] ?? null);

            $this->entityManager->persist($order);
        }

        $this->entityManager->flush();
    }
}
