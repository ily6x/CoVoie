<?php

class Utilisateur
{
    public int $id;
    public string $nom;
    public string $email;
    public string $password;
    public string $role;

    // Transforme une ligne de la base de données en objet utilisable dans les vues.
    public function __construct(array $ligne)
    {
        $this->id = (int)  $ligne['id'];
        $this->nom = $ligne['nom'];
        $this->email = $ligne['email'];
        $this->password = $ligne['mot_de_passe'];
        $this->role = $ligne['role'];
    }

    public static function find(int $id): ?self
    {
        $ligne = Database::query('SELECT * FROM utilisateurs WHERE id = ?', [$id])->fetch();
        return $ligne ? new self($ligne) : null;
    }

    public static function findByEmail(string $email): ?self
    {
        $ligne = Database::query('SELECT * FROM utilisateurs WHERE email = ?', [$email])->fetch();
        return $ligne ? new self($ligne) : null;
    }

    public static function all(): array
    {
        $lignes = Database::query('SELECT * FROM utilisateurs ORDER BY nom')->fetchAll();
        $resultat = [];

        foreach ($lignes as $ligne) {
            $resultat[] = new self($ligne);
        }

        return $resultat;
    }

    public static function register(string $nom, string $email, string $password, string $confirmation): int
    {
        if (mb_strlen($nom) < 2 || mb_strlen($nom) > 100) {
            throw new DomainException('Le nom doit contenir entre 2 et 100 caractères.');
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 180) {
            throw new DomainException('Adresse email invalide.');
        }
        if (strlen($password) < 8 || strlen($password) > 72 || $password !== $confirmation) {
            throw new DomainException('Les mots de passe doivent être identiques et contenir entre 8 et 72 caractères.');
        }
        try {
            Database::query("INSERT INTO utilisateurs (nom,email,mot_de_passe,role,date_inscription) VALUES (?,?,?,'membre',?)", [$nom, mb_strtolower($email), password_hash($password, PASSWORD_DEFAULT), now()]);
        }
        catch (PDOException $e) {
            if ($e->getCode() === '23000') {
                throw new DomainException('Cette adresse email possède déjà un compte.');
            }
            throw $e;
        }
        return (int)  Database::get()->lastInsertId();
    }

    public function noteMoyenne(): ?float
    {
        return Avis::noteMoyenne($this->id);
    }

    public function nombreAvis(): int
    {
        return Avis::nombreAvis($this->id);
    }

    public function completedTripCount(): int
    {
        return (int)  Database::query("SELECT COUNT(*)
            FROM trajets t
            WHERE t.statut='Termine' AND (t.id_conducteur=? OR EXISTS (SELECT 1
            FROM reservations r
            WHERE r.id_trajet=t.id AND r.id_passager=? AND r.statut='Confirmee'))", [$this->id, $this->id])->fetchColumn();
    }

    public static function delete(int $id, int $admin): void
    {
        Database::transaction(function () use ($id, $admin) {
            if ($id === $admin) {
                throw new DomainException('Vous ne pouvez pas supprimer votre propre compte.');
            }
            // Verrouiller les trajets avant de libérer les places du passager supprimé.
            $trajets = Database::query("SELECT t.*
            FROM trajets t
            WHERE EXISTS (SELECT 1
            FROM reservations r
            WHERE r.id_trajet=t.id AND r.id_passager=? AND r.statut='Confirmee')
            ORDER BY t.id" . Database::lockClause(), [$id])->fetchAll();
            foreach ($trajets as $trajet) {
                Database::query("UPDATE trajets
            SET places_restantes=places_restantes+1, statut=CASE WHEN statut='Complet' THEN 'Ouvert' ELSE statut END
            WHERE id=?", [$trajet['id']]);
            }
            Database::query('DELETE FROM utilisateurs WHERE id=?', [$id]);
        });
    }
}
