<?php

class HttpException extends RuntimeException
{
}
function inputText(array $source, string $cle, string $defaut = ''): string
{
    if (isset($source[$cle]) && is_string($source[$cle])) {
        return trim($source[$cle]);
    }

    return $defaut;
}
function inputString(array $source, string $cle): string
{
    return isset($source[$cle]) && is_string($source[$cle]) ? $source[$cle] : '';
}
function escape(mixed $v): string
{
    return htmlspecialchars((string)  ($v ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function formatDate(string $date): string
{
    return date('d/m/Y à H:i', strtotime($date));
}
function formatPrice(float $prix): string
{
    return number_format($prix, 2, ',', ' ') . ' €';
}
function formatRating(?float $note, ?int $nombre = null): string
{
    if ($note === null) {
        return '<span class="note-vide">Pas encore noté</span>';
    }

    $affichage = '<span class="etoiles">★ ';
    $affichage .= number_format($note, 2, ',', ' ');
    $affichage .= ' / 5</span>';

    if ($nombre !== null) {
        $affichage .= ' (' . $nombre . ' avis)';
    }

    return $affichage;
}
function currentUser(): ?Utilisateur
{
    return isset($_SESSION['user_id']) ? Utilisateur::find((int)  $_SESSION['user_id']) : null;
}
function isLoggedIn(): bool
{
    return currentUser() !== null;
}
function isAdmin(): bool
{
    return currentUser()?->role === 'admin';
}
function currentUserId(): int
{
    return currentUser()?->id ?? 0;
}
function currentUserName(): string
{
    return currentUser()?->nom ?? '';
}
function requireLogin(): int
{
    if (!isLoggedIn()) {
        throw new HttpException('Connectez-vous pour accéder à cette page.', 401);
    }
    return currentUserId();
}
function requireAdmin(): void
{
    requireLogin();
    if (!isAdmin()) {
        throw new HttpException('Accès réservé à l’administrateur.', 403);
    }
}
function inputId(string $cle = 'id', ?array $source = null): int
{
    $id = filter_var(($source ?? $_GET)[$cle] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false || $id === null) {
        throw new HttpException('Identifiant invalide.', 400);
    }
    return $id;
}
function csrf(): string
{
    // Ce jeton vérifie que le formulaire provient bien de la session ouverte.
    if (!isset($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf'];
}
function csrfField(): string
{
    return '<input type="hidden" name="csrf" value="' . escape(csrf()) . '">';
}
function requirePost(): void
{
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
        header('Allow: POST');
        throw new HttpException('Cette action nécessite un formulaire POST.', 405);
    }
    if (!hash_equals(csrf(), inputText($_POST, 'csrf'))) {
        throw new HttpException('Formulaire expiré. Rechargez la page.', 403);
    }
}
function flash(string $texte, string $type = 'succes'): void
{
    $_SESSION['messages'][] = compact('texte', 'type');
}
function getFlashMessages(): array
{
    $m = $_SESSION['messages'] ?? [];
    unset($_SESSION['messages']);
    return $m;
}
function redirect(string $page): never
{
    header('Location: index.php?page=' . $page, true, 303);
    exit;
}
function render(string $nom, array $donnees = []): void
{
    extract($donnees, EXTR_SKIP);
    require __DIR__ . '/../views/' . $nom . '.php';
}
function now(): string
{
    return date('Y-m-d H:i:s');
}

// Les valeurs techniques de la base restent sans accent ; le site affiche leurs libellés.
function statusLabel(string $statut): string
{
    $libelles = [
        'Termine' => 'Terminé',
        'Confirmee' => 'Confirmée',
        'Refusee' => 'Refusée'
    ];

    return $libelles[$statut] ?? $statut;
}

function placeLabel(string $lieu): string
{
    $libelles = [
        'Aeroport Roissy-CDG' => 'Aéroport Roissy-CDG',
        "Aeroport d'Orly" => "Aéroport d'Orly",
        'Boulogne Pont de Sevres' => 'Boulogne Pont de Sèvres',
        'Boulogne Jean Jaures' => 'Boulogne Jean Jaurès',
        'Creteil Universite' => 'Créteil Université',
        'Nanterre Universite' => 'Nanterre Université',
        'Meudon-la-Foret' => 'Meudon-la-Forêt',
        'Javel Andre Citroen' => 'Javel André Citroën'
    ];

    return $libelles[$lieu] ?? $lieu;
}

function nameLabel(string $nom): string
{
    $libelles = [
        'Ines Bouchard' => 'Inès Bouchard',
        'Lea Fontaine' => 'Léa Fontaine',
        'Chloe Marchand' => 'Chloé Marchand'
    ];

    return $libelles[$nom] ?? $nom;
}

// Correction d'affichage des commentaires du jeu de démonstration initial.
function commentLabel(?string $commentaire): string
{
    $corrections = [
        'Tres' => 'Très',
        'tres' => 'très',
        'Ca va' => 'Ça va',
        'a l’heure' => 'à l’heure',
        "a l'heure" => "à l'heure",
        'A accepte' => 'A accepté',
        'deposer' => 'déposer',
        'agreable' => 'agréable',
        'depart' => 'départ',
        'Arrivee' => 'Arrivée',
        'arrivee' => 'arrivée',
        'passe' => 'passé'
    ];

    return strtr($commentaire ?? '', $corrections);
}
