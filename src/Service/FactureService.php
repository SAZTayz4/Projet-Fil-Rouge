<?php

namespace App\Service;

use App\Entity\Facture;
use App\Entity\Paiement;
use App\Entity\User;
use TCPDF;

class FactureService
{
    private string $uploadDir;

    public function __construct(string $uploadDir)
    {
        $this->uploadDir = $uploadDir;

        // Définir les constantes TCPDF si elles ne sont pas définies
        if (!defined('PDF_PAGE_ORIENTATION')) {
            define('PDF_PAGE_ORIENTATION', 'P');
        }
        if (!defined('PDF_UNIT')) {
            define('PDF_UNIT', 'mm');
        }
        if (!defined('PDF_PAGE_FORMAT')) {
            define('PDF_PAGE_FORMAT', 'A4');
        }
    }

    public function genererFacture(Paiement $paiement, User $user): Facture
    {
        // Créer le numéro de facture unique
        $numeroFacture = 'FACT-' . date('Ymd') . '-' . sprintf('%04d', $paiement->getId());

        // Créer la facture
        $facture = new Facture();
        $facture->setPaiement($paiement);
        $facture->setUtilisateur($user);
        $facture->setNumeroFacture($numeroFacture);
        $facture->setMontantTotal($paiement->getMontant());
        $facture->setStatut('payée');

        // Générer le PDF
        $pdfPath = $this->genererPDF($facture);

        // Stocker les détails de la facture
        $facture->setDetails([
            'pdf_path' => $pdfPath,
            'date_emission' => date('Y-m-d H:i:s'),
            'type_abonnement' => $paiement->getTypeAbonnement(),
            'methode_paiement' => $paiement->getMethodePaiement()
        ]);

        return $facture;
    }

    private function genererPDF(Facture $facture): string
    {
        // Créer une nouvelle instance de TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Définir les informations du document
        $pdf->SetCreator('CheckMyKicks');
        $pdf->SetAuthor('CheckMyKicks');
        $pdf->SetTitle('Facture ' . $facture->getNumeroFacture());

        // Supprimer les en-têtes et pieds de page par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Définir le contenu de la facture
        $html = $this->getFactureTemplate($facture);

        // Écrire le contenu HTML
        $pdf->writeHTML($html, true, false, true, false, '');

        // Générer le chemin du fichier
        $filename = 'facture_' . $facture->getNumeroFacture() . '.pdf';
        $filepath = $this->uploadDir . '/' . $filename;

        // Sauvegarder le PDF
        $pdf->Output($filepath, 'F');

        return $filename;
    }

    private function getFactureTemplate(Facture $facture): string
    {
        $user = $facture->getUtilisateur();
        $paiement = $facture->getPaiement();

        return <<<HTML
        <div style="font-family: helvetica; padding: 20px;">
            <div style="text-align: right; margin-bottom: 30px;">
                <img src="{$this->uploadDir}/logo.png" style="width: 150px; margin-bottom: 20px;">
                <h1 style="color: #333; font-size: 24px;">FACTURE</h1>
                <p style="color: #666;">N° {$facture->getNumeroFacture()}</p>
                <p style="color: #666;">Date: {$facture->getCreatedAt()->format('d/m/Y')}</p>
            </div>

            <div style="margin-bottom: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                <h2 style="color: #333; font-size: 18px; margin-bottom: 15px;">CheckMyKicks</h2>
                <p style="color: #666; margin: 5px 0;">123 Rue des Sneakers</p>
                <p style="color: #666; margin: 5px 0;">75000 Paris, France</p>
                <p style="color: #666; margin: 5px 0;">contact@checkmykicks.com</p>
                <p style="color: #666; margin: 5px 0;">SIRET: 123 456 789 00000</p>
            </div>

            <div style="margin-bottom: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                <h2 style="color: #333; font-size: 18px; margin-bottom: 15px;">Client</h2>
                <p style="color: #666; margin: 5px 0;"><strong>{$user->getNom()}</strong></p>
                <p style="color: #666; margin: 5px 0;">{$user->getEmail()}</p>
            </div>

            <table style="width: 100%; border-collapse: collapse; margin-bottom: 30px;">
                <thead>
                    <tr style="background-color: #f8f9fa;">
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: left;">Description</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Montant HT</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">TVA (20%)</th>
                        <th style="padding: 12px; border: 1px solid #ddd; text-align: right;">Montant TTC</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="padding: 12px; border: 1px solid #ddd;">Abonnement {$paiement->getTypeAbonnement()}</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">{$this->formatMontant($facture->getMontantTotal() / 1.2)} €</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;">{$this->formatMontant($facture->getMontantTotal() - ($facture->getMontantTotal() / 1.2))} €</td>
                        <td style="padding: 12px; border: 1px solid #ddd; text-align: right;"><strong>{$this->formatMontant($facture->getMontantTotal())} €</strong></td>
                    </tr>
                </tbody>
            </table>

            <div style="text-align: right; margin-top: 30px; padding: 20px; background-color: #f8f9fa; border-radius: 5px;">
                <p style="margin: 5px 0;"><strong>Total HT:</strong> {$this->formatMontant($facture->getMontantTotal() / 1.2)} €</p>
                <p style="margin: 5px 0;"><strong>TVA (20%):</strong> {$this->formatMontant($facture->getMontantTotal() - ($facture->getMontantTotal() / 1.2))} €</p>
                <p style="margin: 5px 0; font-size: 18px;"><strong>Total TTC:</strong> {$this->formatMontant($facture->getMontantTotal())} €</p>
                <p style="margin: 5px 0; color: #666;"><small>Méthode de paiement: Simulation</small></p>
            </div>

            <div style="margin-top: 50px; padding: 20px; border-top: 1px solid #ddd;">
                <p style="color: #666; font-size: 12px; margin: 5px 0;">Merci de votre confiance !</p>
                <p style="color: #666; font-size: 12px; margin: 5px 0;">Cette facture est une simulation et n'a pas de valeur légale.</p>
            </div>
        </div>
        HTML;
    }

    private function formatMontant(float $montant): string
    {
        return number_format($montant, 2, ',', ' ');
    }

    public function genererPDFFacture(array $data): string
    {
        // Créer une nouvelle instance de TCPDF
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Définir les informations du document
        $pdf->SetCreator('FileRouge');
        $pdf->SetAuthor('FileRouge');
        $pdf->SetTitle('Facture ' . $data['numero']);

        // Supprimer les en-têtes et pieds de page par défaut
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        // Ajouter une page
        $pdf->AddPage();

        // Définir la police
        $pdf->SetFont('helvetica', '', 12);

        // En-tête de la facture
        $pdf->Image(__DIR__ . '/../../public/images/logo.png', 15, 15, 50);
        $pdf->SetXY(120, 15);
        $pdf->SetFont('helvetica', 'B', 20);
        $pdf->Cell(0, 10, 'FACTURE', 0, 1, 'R');
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetXY(120, 25);
        $pdf->Cell(0, 10, 'N° ' . $data['numero'], 0, 1, 'R');
        $pdf->SetXY(120, 35);
        $pdf->Cell(0, 10, 'Date : ' . $data['date'], 0, 1, 'R');

        // Informations client
        $pdf->SetXY(15, 60);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Client', 0, 1);
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetXY(15, 70);
        $pdf->Cell(0, 10, $data['utilisateur']['nom'], 0, 1);
        $pdf->SetXY(15, 80);
        $pdf->Cell(0, 10, $data['utilisateur']['email'], 0, 1);

        // Détails de la facture
        $pdf->SetXY(15, 100);
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Détails de la facture', 0, 1);

        // Tableau des détails
        $pdf->SetFont('helvetica', '', 12);
        $pdf->SetXY(15, 110);
        $pdf->Cell(100, 10, 'Description', 1, 0, 'L');
        $pdf->Cell(40, 10, 'Montant HT', 1, 0, 'R');
        $pdf->Cell(40, 10, 'TVA (20%)', 1, 0, 'R');
        $pdf->Cell(40, 10, 'Total TTC', 1, 1, 'R');

        $montantHT = $data['montant'] / 1.2;
        $tva = $data['montant'] - $montantHT;

        $pdf->Cell(100, 10, 'Abonnement ' . $data['type_abonnement'], 1, 0, 'L');
        $pdf->Cell(40, 10, number_format($montantHT, 2, ',', ' ') . ' €', 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($tva, 2, ',', ' ') . ' €', 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($data['montant'], 2, ',', ' ') . ' €', 1, 1, 'R');

        // Total
        $pdf->SetXY(135, 120);
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(40, 10, 'Total TTC', 1, 0, 'R');
        $pdf->Cell(40, 10, number_format($data['montant'], 2, ',', ' ') . ' €', 1, 1, 'R');

        // Note
        $pdf->SetXY(15, 150);
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->MultiCell(0, 10, 'Note : Cette facture est une simulation et n\'a pas de valeur légale.', 0, 'L');

        // Sauvegarder le PDF
        $pdfPath = $this->uploadDir . '/' . $data['numero'] . '.pdf';
        $pdf->Output($pdfPath, 'F');

        return $pdfPath;
    }
}
