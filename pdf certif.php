<?php
require('../fpdf/fpdf.php');

class PDF extends FPDF
{
// En-tête
function Header()
{
    $this->SetFont('Arial','I',10);
    // Saut de ligne
    $this->Ln(25);
}

// Pied de page
function Footer()
{
    // Positionnement à 1,5 cm du bas
    $this->SetY(-15);
    // Police Arial italique 8
    $this->SetFont('Arial','I',10);
	// Numéro de page
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');

}
}

// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);


	$pdf->Image('logo.png',10,6,30);
	$pdf->Cell(90,6,"<Nom de la commune>",0,0,'L');
	$pdf->Cell(90,6,"<Nom et prénom de l’agent>",0,1,'R');
	$pdf->Cell(90,6,"",0,0,'L');
	$pdf->Cell(90,6,"<Nom du service>",0,1,'R');

	$pdf->Cell(90,6,"<Adresse de la commune>",0,0,'L');
	$pdf->Cell(90,6,"<Adresse du service>",0,1,'R');

	$pdf->Cell(90,6,"<Numéro de téléphone>",0,0,'L');
	$pdf->Cell(90,6,"<Numéro de téléphone de l’agent ou du service>",0,1,'R');

	$pdf->Cell(90,6,"<Adresse e-mail>",0,0,'L');
	$pdf->Cell(90,6,"<Adresse e-mail de l’agent ou du service>",0,1,'R');
	$pdf->Ln();
	$pdf->Cell(60,6,"Votre référence :",0,0,'L');
	$pdf->Cell(60,6,"Notre référence :",0,0,'L');
	$pdf->Cell(60,6,"Référence <nom du système> :",0,1,'L');
				
	$pdf->Cell(60,6,"<référence citoyen>",0,0,'L');
	$pdf->Cell(60,6,"<référence communale>",0,0,'L');
	$pdf->Cell(60,6,"<référence système>",0,1,'L');
	$pdf->Ln();

$pdf->Cell(0,9,"CONCERNE : Demande de certificat de nationalité belge",0,0,'L');
	$pdf->Ln();
$pdf->Cell(0,9,"Le <date de génération au format dd/MM/yyyy>",0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
$pdf->Cell(0,9,"Madame, Monsieur,",0,0,'L');
	$pdf->Ln();
	$pdf->Ln();
//$pdf->MultiCell(0,9,"Par la présente, nous avons le plaisir de vous délivrer votre certificat de nationalité belge.",0,'L');
$pdf->MultiCell(0,9,"Par la présente, nous avons le regret de ne pas pouvoir vous délivrer un certificat de nationalité belge.",0,'L');
	$pdf->Ln();
//$pdf->MultiCell(0,9,"Le nommé, <nom du demandeur>, <prénoms du demandeur, né le <date de naissance du demandeur au format dd/MM/yyyy>, ayant pour numéro de registre national <numéro de registre national du demandeur au format 00.00.00 000-00>, est certifié de nationalité belge.",0,'L');
$pdf->MultiCell(0,9,"En effet, après enquête, il s’est avéré que , <nom du demandeur>, <prénoms du demandeur, né le <date de naissance du demandeur au format dd/MM/yyyy>, ayant pour numéro de registre national <numéro de registre national du demandeur au format 00.00.00 000-00>, ne peut pas être certifié de nationalité belge.",0,'L');
	$pdf->Ln();
	$pdf->Cell(0,10,'Je vous prie d\'agréer, Madame, Monsieur, l\'expression de nos sentiments distingués.',0,1);
	$pdf->Cell(0,10,'',0,1);
	$pdf->Cell(0,10,'',0,1);
	$pdf->Cell(90,9,"<Nom et prénom du bourgmestre>",0,0,'L');
	$pdf->Cell(90,9,"<Nom et prénom du chef de service>",0,1,'R');
	$pdf->Cell(90,9,"Bourgmestre de la commune de <nom de la commune>",0,0,'L');
	$pdf->Cell(90,9,"Service <nom du service>",0,1,'R');
$pdf->Output();
?>