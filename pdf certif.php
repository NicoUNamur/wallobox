<?php
require('../fpdf/fpdf.php');

class PDF extends FPDF
{
// En-t�te
function Header()
{
    $this->SetFont('Arial','I',10);
    // Saut de ligne
    $this->Ln(25);
}

// Pied de page
function Footer()
{
    // Positionnement � 1,5 cm du bas
    $this->SetY(-15);
    // Police Arial italique 8
    $this->SetFont('Arial','I',10);
	// Num�ro de page
    $this->Cell(0,10,'Page '.$this->PageNo().'/{nb}',0,0,'C');

}
}

// Instanciation de la classe d�riv�e
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);


	$pdf->Image('logo.png',10,6,30);
	$pdf->Cell(90,6,"<Nom de la commune>",0,0,'L');
	$pdf->Cell(90,6,"<Nom et pr�nom de l�agent>",0,1,'R');
	$pdf->Cell(90,6,"",0,0,'L');
	$pdf->Cell(90,6,"<Nom du service>",0,1,'R');

	$pdf->Cell(90,6,"<Adresse de la commune>",0,0,'L');
	$pdf->Cell(90,6,"<Adresse du service>",0,1,'R');

	$pdf->Cell(90,6,"<Num�ro de t�l�phone>",0,0,'L');
	$pdf->Cell(90,6,"<Num�ro de t�l�phone de l�agent ou du service>",0,1,'R');

	$pdf->Cell(90,6,"<Adresse e-mail>",0,0,'L');
	$pdf->Cell(90,6,"<Adresse e-mail de l�agent ou du service>",0,1,'R');
	$pdf->Ln();
	$pdf->Cell(60,6,"Votre r�f�rence :",0,0,'L');
	$pdf->Cell(60,6,"Notre r�f�rence :",0,0,'L');
	$pdf->Cell(60,6,"R�f�rence <nom du syst�me> :",0,1,'L');
				
	$pdf->Cell(60,6,"<r�f�rence citoyen>",0,0,'L');
	$pdf->Cell(60,6,"<r�f�rence communale>",0,0,'L');
	$pdf->Cell(60,6,"<r�f�rence syst�me>",0,1,'L');
	$pdf->Ln();

$pdf->Cell(0,9,"CONCERNE : Demande de certificat de nationalit� belge",0,0,'L');
	$pdf->Ln();
$pdf->Cell(0,9,"Le <date de g�n�ration au format dd/MM/yyyy>",0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
$pdf->Cell(0,9,"Madame, Monsieur,",0,0,'L');
	$pdf->Ln();
	$pdf->Ln();
//$pdf->MultiCell(0,9,"Par la pr�sente, nous avons le plaisir de vous d�livrer votre certificat de nationalit� belge.",0,'L');
$pdf->MultiCell(0,9,"Par la pr�sente, nous avons le regret de ne pas pouvoir vous d�livrer un certificat de nationalit� belge.",0,'L');
	$pdf->Ln();
//$pdf->MultiCell(0,9,"Le nomm�, <nom du demandeur>, <pr�noms du demandeur, n� le <date de naissance du demandeur au format dd/MM/yyyy>, ayant pour num�ro de registre national <num�ro de registre national du demandeur au format 00.00.00 000-00>, est certifi� de nationalit� belge.",0,'L');
$pdf->MultiCell(0,9,"En effet, apr�s enqu�te, il s�est av�r� que , <nom du demandeur>, <pr�noms du demandeur, n� le <date de naissance du demandeur au format dd/MM/yyyy>, ayant pour num�ro de registre national <num�ro de registre national du demandeur au format 00.00.00 000-00>, ne peut pas �tre certifi� de nationalit� belge.",0,'L');
	$pdf->Ln();
	$pdf->Cell(0,10,'Je vous prie d\'agr�er, Madame, Monsieur, l\'expression de nos sentiments distingu�s.',0,1);
	$pdf->Cell(0,10,'',0,1);
	$pdf->Cell(0,10,'',0,1);
	$pdf->Cell(90,9,"<Nom et pr�nom du bourgmestre>",0,0,'L');
	$pdf->Cell(90,9,"<Nom et pr�nom du chef de service>",0,1,'R');
	$pdf->Cell(90,9,"Bourgmestre de la commune de <nom de la commune>",0,0,'L');
	$pdf->Cell(90,9,"Service <nom du service>",0,1,'R');
$pdf->Output();
?>