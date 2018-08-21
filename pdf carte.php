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

$req='select cp.date_ech,'.
'cp.date_debut,'.
'cp.date_fin,'.
'cp.plaque,'.
'cp.longu,'.
'`d`.`id` ref_sys,'.
'`d`.`ref_organisme`,'.
'`d`.`ref_user`,'.
'`d`.`commentaire` AS `commentaire`,'.
'`d`.`last_update_time`,'.
'`w`.`nom` AS `Workflow`,'.
'`o`.`nom` AS `nomCommune`,'.
'`o`.`id` AS `idCommune`,'.
'`o`.`adresse` AS `adrCommune`,'.
'`o`.`adr_service` AS `adrService`,'.
'`o`.nom_bourg,'.
'`o`.nom_service,'.
'`o`.nom_chef_service,'.
'`o`.email,'.
'`o`.email_service,'.
'`o`.no_tel_service,'.
'`o`.tel,'.
'agent.nom nomAgent, '.
'util.adr adrUser, '.
'pcp.prix,'.
'`cp`.`created_by` AS `id_utilisateur` '.
'from '.PREFIX.'carte_parking `cp` '.
'join '.PREFIX.'demande `d` on `d`.`id` = `cp`.`id_demande` '.
'join '.PREFIX.'workflow `w` on `w`.`id` = `d`.`id_workflow` '.
'join '.PREFIX.'organisme `o` on `d`.`id_organisme` = `o`.`id` '.
'join '.PREFIX.'utilisateur agent on agent.id=d.last_update_by '.
'join '.PREFIX.'utilisateur util on util.id=d.created_by '.
'join '.PREFIX.'param_carte_parking pcp on o.id=pcp.id_organisme '.
'WHERE cp.id=\''.$_REQUEST['id'].'\';'
;
$db_var=$db->RequestDB($req,'req_organisme' );
$res=$db->GetLigneDB($db_var);
$date_deb=substr($res['date_debut'],8,2).'/'.substr($res['date_debut'],5,2).'/'.substr($res['date_debut'],0,4);
$date_fin=substr($res['date_fin'],8,2).'/'.substr($res['date_fin'],5,2).'/'.(substr($res['date_fin'],0,4));
$date_ech=substr($res['date_ech'],8,2).'/'.substr($res['date_ech'],5,2).'/'.substr($res['date_ech'],0,4);

// Instanciation de la classe dérivée
$pdf = new PDF();
$pdf->AliasNbPages();
$pdf->AddPage();
$pdf->SetFont('Arial','',10);


	$pdf->Image('uploads/organisme.photo.'.$res['idCommune'].'.jpg',10,6,30);
	$pdf->Cell(90,6,utf8_decode($res['nomCommune']),0,0,'L');
	$pdf->Cell(90,6,utf8_decode($res['nomAgent']),0,1,'R');
	$pdf->MultiCell(90,6,utf8_decode($res['adrCommune']),0,"L");
	$pdf->Cell(90,6,"",0,0,'L');
	$pdf->Cell(90,6,utf8_decode($res['nom_service']),0,1,'R');
	$pdf->Cell(90,6,"",0,0,'L');

	$pdf->Cell(90,6,utf8_decode($res['adrService']),0,1,'R');

	$pdf->Cell(90,6,utf8_decode($res['tel']),0,0,'L');
	$pdf->Cell(90,6,utf8_decode($res['no_tel_service']),0,1,'R');

	$pdf->Cell(90,6,utf8_decode($res['email']),0,0,'L');
	$pdf->Cell(90,6,utf8_decode($res['email_service']),0,1,'R');
	$pdf->Ln();
	$pdf->Cell(60,6,"Votre référence :",0,0,'L');
	$pdf->Cell(60,6,"Notre référence :",0,0,'L');
	$pdf->Cell(60,6,"Référence WalloBox01 :",0,1,'L');
				
	$pdf->Cell(60,6,utf8_decode($res['ref_user']),0,0,'L');
	$pdf->Cell(60,6,utf8_decode($res['ref_organisme']),0,0,'L');
	$pdf->Cell(60,6,utf8_decode($res['ref_sys']),0,1,'L');
	$pdf->Ln();

	if(isset($_REQUEST['IAP']))
		$pdf->Cell(0,9,"CONCERNE : Invitation à payer",0,0,'L');
	else
		$pdf->Cell(0,9,"CONCERNE : Demande de carte de stationnement pour riverain ou visiteur",0,0,'L');
	$pdf->Ln();
	$pdf->Cell(0,9,"Le ".substr($res['last_update_time'],8,2).'/'.substr($res['last_update_time'],5,2).'/'.substr($res['last_update_time'],0,4),0,0,'R');
	$pdf->Ln();
	$pdf->Ln();
	$pdf->Cell(0,9,"Madame, Monsieur,",0,0,'L');
	$pdf->Ln();
	$pdf->Ln();

	switch(utf8_decode($res['Workflow'])){
		case 'Demande accordée':
			if(isset($_REQUEST['IAP'])){
				$pdf->MultiCell(0,9,"Suite à votre demande de carte de stationnement pour riverain ou visiteur acceptée, voici les informations nécessaires pour effectuer le paiement.",0,'L');
				$pdf->MultiCell(0,9,"Montant à payer : ".utf8_decode($res['prix'])." €
			
				Destinataire :
				".utf8_decode($res['nomCommune'])."
				".utf8_decode($res['adrCommune'])."

				Communication libre : ".utf8_decode($res['ref_organisme']),0,'L');
				$pdf->MultiCell(0,9,"Veuillez effectuer le versement pour le ".utf8_decode($date_ech)." compris. Si ce n’est pas fait, la carte de stationnement sera invalidée dès le lendemain de l’échéance.",0,'L');
			}
			else{
				$pdf->MultiCell(0,9,"L’emplacement réservé à l’adresse ".$res['adrUser']." est valable pour une longueur de ".$res['longu']."m, du ".$date_deb." au ".$date_fin.".",0,'L');
				$pdf->MultiCell(0,9,"En annexe à ce document, vous trouverez une invitation à payer le montant de ".$res['prix']." €. Toutes les informations nécessaires figurent dans le document.",0,'L');
			}
			break;
		case 'Demande refusée':
			$pdf->MultiCell(0,9,"Par la présente, nous avons le regret de vous faire savoir que votre demande de carte de stationnement pour riverain ou visiteur n’a pas été acceptée.",0,'L');
			$pdf->MultiCell(0,9,"En effet, après enquête, il s’est avéré que :".utf8_decode($res['commentaire'])."",0,'L');
			break;
		default:
			$pdf->MultiCell(0,9,"La demande est toujours en suspens, veuillez revenir plus tard.",0,'L');
	}


	$pdf->Ln();
	$pdf->Cell(0,10,'Je vous prie d\'agréer, Madame, Monsieur, l\'expression de nos sentiments distingués.',0,1);
	$pdf->Cell(0,10,'',0,1);
	$pdf->Ln();
	$pdf->Cell(90,9,utf8_decode($res['nom_bourg']),0,0,'L');
	$pdf->Cell(90,9,utf8_decode($res['nom_chef_service']),0,1,'R');
	$pdf->Cell(90,9,"Bourgmestre de la ".utf8_decode($res['nomCommune']),0,0,'L');
	$pdf->Cell(90,9,utf8_decode($res['nom_service']),0,1,'R');
$pdf->Output();
?>