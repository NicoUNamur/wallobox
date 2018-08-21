<?php
require('fpdf/fpdf.php');
require_once('class-gen/objet.class.php');

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

require_once('class-gen/db.class.php');
	require_once('class/auth.inc.php');
    session_name(PREFIX.'auth');
    session_start();
//--------------------------------------------------------------------------------------------------
$db=t_DB::getInstance($MySqlServer, $MySqlLogin, $MySqlPass,$MySqlDatabase);

$req='select `cp`.`date_ech`,'.
'`d`.`ref_organisme` AS `ref_organisme`,'.
'`d`.`commentaire` AS `commentaire`,'.
'`d`.`last_update_time`,'.
'`w`.`nom` AS `Workflow`,'.
'`o`.`nom` AS `nomCommune`,'.
'`o`.`id` AS `idCommune`,'.
'`o`.`adresse` AS `adrCommune`,'.
'`o`.nom_bourg,'.
'`o`.nom_service,'.
'`o`.nom_chef_service,'.
'cp.plaque,'.
'`cp`.`created_by` AS `id_utilisateur` '.
'from '.PREFIX.'carte_parking `cp` '.
'join '.PREFIX.'demande `d` on `d`.`id` = `cp`.`id_demande` '.
'join '.PREFIX.'workflow `w` on `w`.`id` = `d`.`id_workflow` '.
'join '.PREFIX.'organisme `o` on `d`.`id_organisme` = `o`.`id` '.
'WHERE cp.id=\''.$_REQUEST['id'].'\';'
;
$db_var=$db->RequestDB($req,'req_organisme' );
$res=$db->GetLigneDB($db_var);


// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);


	$pdf->Image('uploads/organisme.photo.'.$res['idCommune'].'.jpg',10,6,30);
	$pdf->Cell(90,6,utf8_decode($res['nomCommune']),0,1,'L');
	$pdf->Cell(90,6,"",0,1,'L');

	$pdf->MultiCell(90,6,utf8_decode($res['adrCommune']),0,"L");
	$pdf->Ln();

$pdf->SetFont('Arial','',30);
	$pdf->Cell(0,27,"Carte de stationnement",0,0,'C');
$pdf->SetFont('Arial','',10);
	$pdf->Ln();

	$pdf->MultiCell(0,9,"Plaque d’immatriculation du véhicule: ".$res['plaque'].".",0,'L');
	$pdf->Ln();
$pdf->MultiCell(0,9,"Numéro de la carte : ".$res['ref_organisme'].".",0,'L');
	$pdf->Ln();
$date_deb=substr($res['last_update_time'],8,2).'/'.substr($res['last_update_time'],5,2).'/'.substr($res['last_update_time'],0,4);
$date_fin=substr($res['last_update_time'],8,2).'/'.substr($res['last_update_time'],5,2).'/'.(substr($res['last_update_time'],0,4)+1);
$date_ech=substr($res['date_ech'],8,2).'/'.substr($res['date_ech'],5,2).'/'.substr($res['date_ech'],0,4);
$pdf->MultiCell(0,9,"Période de validité de la carte : du $date_deb au $date_fin compris.",0,'L');
	$pdf->MultiCell(0,9,"Veuillez effectuer le versement pour le ".$date_ech." compris. Si ce n’est pas fait, la carte de stationnement sera invalidée dès le lendemain de l’échéance.",0,'L');



	$pdf->Ln();
	$pdf->Cell(90,9,utf8_decode($res['nom_bourg']),0,0,'L');
	$pdf->Cell(90,9,utf8_decode($res['nom_chef_service']),0,1,'R');
	$pdf->Cell(90,9,"Bourgmestre de la ".utf8_decode($res['nomCommune']),0,0,'L');
	$pdf->Cell(90,9,utf8_decode($res['nom_service']),0,1,'R');
$pdf->Output();
?>