<?php
// Activation des erreurs
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Chargement de TCPDF
require_once __DIR__ . '/../../vendor/autoload.php';

// Définition du dossier de test
$testDir = __DIR__ . '/../../Frontend/factures';
if (!file_exists($testDir)) {
    mkdir($testDir, 0777, true);
    chmod($testDir, 0777);
}

try {
    // Test simple de TCPDF
    $pdf = new TCPDF();
    $pdf->SetCreator('Test');
    $pdf->SetAuthor('Test');
    $pdf->SetTitle('Test PDF');
    
    // Ajout d'une page
    $pdf->AddPage();
    $pdf->SetFont('helvetica', '', 12);
    $pdf->Cell(0, 10, 'Test de génération PDF', 0, 1, 'C');
    
    // Sauvegarde du PDF
    $testFile = $testDir . '/test_' . date('YmdHis') . '.pdf';
    $result = $pdf->Output($testFile, 'F');
    
    if (file_exists($testFile)) {
        echo "✅ Test réussi ! Le PDF a été créé : " . $testFile;
        echo "<br>Taille du fichier : " . filesize($testFile) . " octets";
    } else {
        echo "❌ Erreur : Le fichier n'a pas été créé";
    }
    
} catch (Exception $e) {
    echo "❌ Erreur : " . $e->getMessage();
    echo "<br>Trace : <pre>" . $e->getTraceAsString() . "</pre>";
} 