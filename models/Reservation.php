<?php

class Reservation
{
    public int $id;
    public string $statut;
    public string $dateReservation;
    public int $idTrajet;
    public int $idPassager;
    public string $nomPassager;
    public string $origine;
    public string $destination;
    public string $dateHeure;
    public int $idConducteur;
    public string $nomConducteur;
    public string $statutTrajet;
    private const SELECT = 'SELECT r.*, p.nom AS nom_passager, t.origine, t.destination, t.date_heure, t.id_conducteur, t.statut AS statut_trajet, c.nom AS nom_conducteur
            FROM reservations r
            JOIN utilisateurs p ON p.id=r.id_passager
            JOIN trajets t ON t.id=r.id_trajet
            JOIN utilisateurs c ON c.id=t.id_conducteur';

    // Transforme une ligne de la base de données en objet utilisable dans les vues.
    public function __construct(array $ligne)
    {
        $this->id = (int) $ligne['id'];
        $this->statut = $ligne['statut'];
        $this->dateReservation = $ligne['date_reservation'];
        $this->idTrajet = (int) $ligne['id_trajet'];
        $this->idPassager = (int) $ligne['id_passager'];
        $this->nomPassager = $ligne['nom_passager'];
        $this->origine = $ligne['origine'];
        $this->destination = $ligne['destination'];
        $this->dateHeure = $ligne['date_heure'];
        $this->idConducteur = (int) $ligne['id_conducteur'];
        $this->nomConducteur = $ligne['nom_conducteur'];
        $this->statutTrajet = $ligne['statut_trajet'];
    }

    public static function getList(string $condition, array $params): array
    {
        $lignes = Database::query(self::SELECT . ' WHERE ' . $condition . ' ORDER BY r.date_reservation DESC', $params)->fetchAll();
        $resultat = [];

        foreach ($lignes as $ligne) {
            $resultat[] = new self($ligne);
        }

        return $resultat;
    }

    public static function hasRequest(int $trajet, int $passager): bool
    {
        return (bool)  Database::query('SELECT 1 FROM reservations WHERE id_trajet=? AND id_passager=?', [$trajet, $passager])->fetchColumn();
    }

    public static function create(int $trajet, int $passager): int
    {
        return Database::transaction(function () use ($trajet, $passager) {
            $trajetTrouve = Trajet::verrouiller($trajet);
            if ($trajetTrouve->idConducteur === $passager) {
                throw new DomainException('Vous ne pouvez pas réserver votre propre trajet.');
            }
            if ($trajetTrouve->statut !== 'Ouvert' || $trajetTrouve->placesRestantes<1 || $trajetTrouve->hasDeparted()) {
                throw new DomainException('Ce trajet ne peut plus être réservé.');
            }
            if (self::hasRequest($trajet, $passager)) {
                throw new DomainException('Une demande existe déjà pour ce trajet.');
            }
            Database::query("INSERT INTO reservations (statut,date_reservation,id_trajet,id_passager) VALUES ('En attente',?,?,?)", [now(), $trajet, $passager]);
            return (int) Database::get()->lastInsertId();
        });
    }

    public static function traiter(int $trajet, int $reservation, int $conducteur, bool $accepter): void
    {
        Database::transaction(function () use ($trajet, $reservation, $conducteur, $accepter) {
            $trajetTrouve = Trajet::verrouiller($trajet);
            $trajetTrouve->checkDriver($conducteur);
            $ligne = Database::query('SELECT * FROM reservations WHERE id=? AND id_trajet=?' . Database::lockClause(), [$reservation, $trajet])->fetch();
            if (!$ligne) {
                throw new HttpException('Réservation introuvable.', 404);
            }
            if ($ligne['statut'] !== 'En attente') {
                throw new DomainException('Cette demande a déjà été traitée.');
            }
            if ($trajetTrouve->statut === 'Termine' || $trajetTrouve->hasDeparted()) {
                throw new DomainException('Ce trajet est déjà passé.');
            }
            if ($accepter) {
                $trajetTrouve->reserverPlace((int) $ligne['id_passager']);
            }
            Database::query('UPDATE reservations SET statut=? WHERE id=?', [$accepter?'Confirmee':'Refusee', $reservation]);
        });
    }

    public static function cancel(int $id, int $passager): void
    {
        $stmt = Database::query("DELETE FROM reservations WHERE id=? AND id_passager=? AND statut='En attente'", [$id, $passager]);
        if (!$stmt->rowCount()) {
            throw new HttpException('Demande introuvable ou déjà traitée.', 403);
        }
    }

    public function statusClass(): string
    {
        return match ($this->statut) {
            'Confirmee' => 'badge-confirmee', 'Refusee' => 'badge-refusee', default => 'badge-attente'
        }
        ;
    }

    public function passengerRating(): ?float
    {
        return Avis::noteMoyenne($this->idPassager);
    }
}
