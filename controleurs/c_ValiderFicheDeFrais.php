<?php
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

$action = filter_input(INPUT_GET, 'action', FILTER_SANITIZE_STRING);
$idVisiteur = filter_input(INPUT_POST, 'lstVisiteur', FILTER_SANITIZE_STRING);
$montants = 0;
switch ($action) {
    case 'selectionnerMois' :
        if (empty($pdo->getMoisFicheDeFrais())) {
           ?></br><?php ajouterErreur("Aucune fiche de frais n'est à valider");
            include 'vues/v_erreurs.php';
            include 'vues/v_SelectMois.php';
        } else {
            $lesMois = $pdo->getMoisFicheDeFrais();
            // Afin de sélectionner par défaut le dernier mois dans la zone de liste
            // on demande toutes les clés, et on prend la première,
            // les mois étant triés décroissants
            $lesCles = array_keys($lesMois);
            $moisASelectionne = $lesCles[0];
            include 'vues/v_SelectMois.php';
        }
        break;
    case 'selectionnerVisiteur' :
        $leMois = filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING);
        $lesMois = $pdo->getMoisFicheDeFrais();
        $moisASelectionne = $leMois;
        include 'vues/v_SelectMois.php';
        $date = str_replace('/', '', filter_input(INPUT_POST, 'lstMois', FILTER_SANITIZE_STRING));
        trim($date);
        $_SESSION['date'] = $date;
        $lesVisiteur = $pdo->getVisiteurFromMois($date);
        $selectedValue = $lesVisiteur[0];
        include 'vues/v_SelectVisiteur.php';
        break;
    case 'ValiderFicheDeFrais':
        $lesMois = $pdo->getMoisFicheDeFrais();
        $moisASelectionne = $_SESSION['date'];
        include 'vues/v_SelectMois.php';
        $leVisiteur = filter_input(INPUT_POST, 'lstVisiteur', FILTER_SANITIZE_STRING);
        $lesVisiteur = $pdo->getVisiteurFromMois($_SESSION['date']);
        $selectedValue = $leVisiteur;
        include 'vues/v_SelectVisiteur.php';
        $nomVis = (filter_input(INPUT_POST, 'lstVisiteur', FILTER_SANITIZE_STRING));
        trim($nomVis);
        $idVis = $pdo->getIdFromNomVisiteur($nomVis);
        $_SESSION['visiteur'] = $idVis['id'];
        $infoFicheDeFrais = $pdo->getLesInfosFicheFrais($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisForfait = $pdo->getLesFraisForfait($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['visiteur'], $_SESSION['date']);
        include'vues/v_ValiderFicheDeFrais.php';
        $_SESSION['montant'] = $montants;
        break;
    case 'CorrigerNbJustificatifs' :
        $lesMois = $pdo->getMoisFicheDeFrais();
        $moisASelectionne = $_SESSION['date'];
        include 'vues/v_SelectMois.php';
        $lesVisiteur = $pdo->getVisiteurFromMois($_SESSION['date']);
        $selectedValue = $_SESSION['visiteur'];
        include 'vues/v_SelectVisiteur.php';
        $nbJust = filter_input(INPUT_POST, 'nbJust', FILTER_DEFAULT);
        $pdo->majNbJustificatifs($_SESSION['visiteur'], $_SESSION['date'], $nbJust);
        $infoFicheDeFrais = $pdo->getLesInfosFicheFrais($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisForfait = $pdo->getLesFraisForfait($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['visiteur'], $_SESSION['date']);
        include'vues/v_ValiderFicheDeFrais.php';
        ?>
        <script>alert("<?php echo htmlspecialchars('Votre fiche de frais a bien été corrigée ! ', ENT_QUOTES); ?>")</script>
        <?php
        break;
    case 'CorrigerFraisForfait':
        $lesMois = $pdo->getMoisFicheDeFrais();
        $moisASelectionne = $_SESSION['date'];
        include 'vues/v_SelectMois.php';
        $lesVisiteur = $pdo->getVisiteurFromMois($_SESSION['date']);
        $selectedValue = $_SESSION['visiteur'];
        include 'vues/v_SelectVisiteur.php';
        $lesFrais = filter_input(INPUT_POST, 'lesFrais', FILTER_DEFAULT, FILTER_FORCE_ARRAY);
        if (lesQteFraisValides($lesFrais)) {
            $pdo->majFraisForfait($_SESSION['visiteur'], $_SESSION['date'], $lesFrais);
            ?>
            <script>alert("<?php echo htmlspecialchars('Votre fiche de frais a bien été corrigée ! ', ENT_QUOTES); ?>")</script>
            <?php
        } else {
            ajouterErreur('Les valeurs des frais doivent être numériques');
            include 'vues/v_erreurs.php';
        }
        $infoFicheDeFrais = $pdo->getLesInfosFicheFrais($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisForfait = $pdo->getLesFraisForfait($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['visiteur'], $_SESSION['date']);
        include'vues/v_ValiderFicheDeFrais.php';
        break;
    case 'CorrigerElemHorsForfait' :
        $lesMois = $pdo->getMoisFicheDeFrais();
        $moisASelectionne = $_SESSION['date'];
        include 'vues/v_SelectMois.php';
        $lesVisiteur = $pdo->getVisiteurFromMois($_SESSION['date']);
        $selectedValue = $_SESSION['visiteur'];
        include 'vues/v_SelectVisiteur.php';
        $lesHorsForfaitDate = (filter_input(INPUT_POST, 'lesDates', FILTER_DEFAULT, FILTER_FORCE_ARRAY));
        $lesHorsForfaitLibelle = (filter_input(INPUT_POST, 'lesLibelles', FILTER_DEFAULT, FILTER_FORCE_ARRAY));
        $lesHorsForfaitMontant = (filter_input(INPUT_POST, 'lesMontants', FILTER_DEFAULT, FILTER_FORCE_ARRAY));
        $pdo->majFraisHorsForfait($_SESSION['visiteur'], $_SESSION['date'], $lesHorsForfaitLibelle, $lesHorsForfaitMontant, $lesHorsForfaitDate);
        ?>
        <script>alert("<?php echo htmlspecialchars('Votre fiche de frais a bien été corrigée ! ', ENT_QUOTES); ?>")</script>
        <?php
        $infoFicheDeFrais = $pdo->getLesInfosFicheFrais($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisForfait = $pdo->getLesFraisForfait($_SESSION['visiteur'], $_SESSION['date']);
        $infoFraisHorsForfait = $pdo->getLesFraisHorsForfait($_SESSION['visiteur'], $_SESSION['date']);
        include'vues/v_ValiderFicheDeFrais.php';
        break;
    case 'supprimerFrais':
        $unIdFrais = filter_input(INPUT_GET, 'idFrais', FILTER_SANITIZE_NUMBER_INT);
        $ceMois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_STRING);
        $idVisiteur = filter_input(INPUT_GET, 'idVisiteur', FILTER_SANITIZE_STRING);
        ?>
        <div class="alert alert-info" role="alert">
            <p><h4>Voulez vous modifier ou supprimer le frais?<br></h4>
            <a href="index.php?uc=ValiderFicheDeFrais&action=supprimer&idFrais=<?php echo $unIdFrais ?>">Supprimer</a> 
            ou <a href="index.php?uc=ValiderFicheDeFrais&action=reporter&idFrais=<?php echo $unIdFrais ?>&mois=<?php echo $ceMois ?>&id=<?php echo $idVisiteur ?>">Reporter</a></p>
        </div>
        <?php
        break;
    case 'supprimer':
        $idFrais = filter_input(INPUT_GET, 'idFrais', FILTER_SANITIZE_NUMBER_INT);
        $pdo->refuserFraisHorsForfait($idFrais);
        ?>
        <div class="alert alert-info" role="alert">
            <p>Ce frais hors forfait a bien été supprimé! <a href = "index.php?uc=ValiderFicheDeFrais&action=selectionnerMois">Cliquez ici</a>
                pour revenir à la selection.</p>
        </div>
        <?php
        break;

    case 'reporter':
        $idFrais = filter_input(INPUT_GET, 'idFrais', FILTER_SANITIZE_NUMBER_INT);
        $mois = filter_input(INPUT_GET, 'mois', FILTER_SANITIZE_STRING);
        $moisSuivant = $pdo->getMoisSuivant($mois);
        $idVisiteur = filter_input(INPUT_GET, 'id', FILTER_SANITIZE_STRING);
        if ($pdo->estPremierFraisMois($idVisiteur, $moisSuivant)) {
            $pdo->creeNouvellesLignesFrais($idVisiteur, $moisSuivant);
        }
        $moisAReporter = $pdo->reporterFraisHorsForfait($idFrais, $mois);
        ?>
        <div class="alert alert-info" role="alert">
            <p>Ce frais hors forfait a bien été reporté au mois suivant! <a href = "index.php?uc=ValiderFicheDeFrais&action=selectionnerMois">Cliquez ici</a>
                pour revenir à la selection.</p>
        </div>
        <?php
        break;
    case 'Valider' :
        $pdo->validerFicheDeFrais($_SESSION['visiteur'], $_SESSION['date'], $_SESSION['montant']);
        ?> </br>
        <div class = "alert alert-success" role = "alert">
            <p>Votre fiche de frais a bien été validée ! <a href = "index.php?uc=ValiderFicheDeFrais&action=selectionnerMois">Cliquez ici</a>
                pour revenir à la selection.</p>
        </div>
    <?php
}
