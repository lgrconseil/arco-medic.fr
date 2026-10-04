<?php
// Formulaire de demande – arco-medic.fr
// Envoie la demande par e-mail à ARCO MEDIC. Réponse JSON pour le script de la page,
// redirection vers l'accueil si le navigateur n'exécute pas JavaScript.

const DESTINATAIRE = 'arcomedic@wanadoo.fr';
const EXPEDITEUR = 'noreply@arco-medic.fr';

$ajax = strpos($_SERVER['HTTP_ACCEPT'] ?? '', 'application/json') !== false;

function repondre($ok, $message, $ajax)
{
    if ($ajax) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['ok' => $ok, 'message' => $message]);
    } else {
        header('Location: /?' . ($ok ? 'envoye=1' : 'erreur=1') . '#contact', true, 303);
    }
    exit;
}

function champ($nom, $max)
{
    $valeur = (string) ($_POST[$nom] ?? '');
    $valeur = str_replace(["\r", "\n"], ' ', $valeur);
    return trim(mb_substr($valeur, 0, $max, 'UTF-8'));
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    repondre(false, 'Méthode non autorisée.', $ajax);
}

// Anti-spam : champ piège rempli, ou formulaire envoyé en moins de 3 secondes.
$debut = (int) ($_POST['t'] ?? 0);
if (!empty($_POST['site_web']) || ($debut > 0 && (time() * 1000 - $debut) < 3000)) {
    repondre(true, 'Merci, votre demande a bien été envoyée.', $ajax);
}

$nom = champ('nom', 100);
$etab = champ('etab', 150);
$email = champ('email', 150);
$tel = champ('tel', 30);
$objet = champ('objet', 80);
$produit = champ('produit', 150);
$message = trim(mb_substr((string) ($_POST['message'] ?? ''), 0, 5000, 'UTF-8'));

if ($nom === '' || $etab === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    repondre(false, 'Indiquez votre nom, votre établissement et une adresse e-mail valide.', $ajax);
}

$sujet = 'Demande site : ' . ($objet !== '' ? $objet : 'contact') . ($produit !== '' ? ' – ' . $produit : '');
$corps = "Nouvelle demande reçue depuis arco-medic.fr\n\n"
    . "Nom : $nom\n"
    . "Établissement : $etab\n"
    . "E-mail : $email\n"
    . "Téléphone : $tel\n"
    . "Objet : $objet\n"
    . "Produit : $produit\n\n"
    . "Message :\n$message\n\n"
    . "Pour répondre, utilisez simplement « Répondre » : la réponse partira vers $email.\n";

$entetes = [
    'From: Site ARCO MEDIC <' . EXPEDITEUR . '>',
    'Reply-To: ' . $email,
    'MIME-Version: 1.0',
    'Content-Type: text/plain; charset=UTF-8',
    'Content-Transfer-Encoding: 8bit',
];

$ok = mail(
    DESTINATAIRE,
    '=?UTF-8?B?' . base64_encode($sujet) . '?=',
    $corps,
    implode("\r\n", $entetes),
    '-f' . EXPEDITEUR
);

repondre(
    $ok,
    $ok ? 'Merci, votre demande a bien été envoyée. Nous vous répondrons rapidement.'
        : "L'envoi a échoué. Écrivez-nous directement à " . DESTINATAIRE . '.',
    $ajax
);
