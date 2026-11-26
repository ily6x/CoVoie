<?php

class Trajet
{
    public int $id;
    public string $origine;
    public string $destination;
    public string $dateHeure;
    public int $placesTotales;
    public int $placesRestantes;
    public string $statut;
    public float $prix;
    public int $idConducteur;
    public string $nomConducteur;
    private const SELECT = 'SELECT t.*, u.nom AS nom_conducteur FROM trajets t JOIN utilisateurs u ON u.id=t.id_conducteur';

    // Transforme une ligne de la base de données en objet utilisable dans les vues.
    public function __construct(array $ligne)
    {
        $this->id = (int) $ligne['id'];
        $this->origine = $ligne['origine'];
        $this->destination = $ligne['destination'];
        $this->dateHeure = $ligne['date_heure'];
        $this->placesTotales = (int) $ligne['places_totales'];
        $this->placesRestantes = (int) $ligne['places_restantes'];
        $this->statut = $ligne['statut'];
        $this->prix = (float) $ligne['prix'];
        $this->idConducteur = (int) $ligne['id_conducteur'];
        $this->nomConducteur = $ligne['nom_conducteur'];
    }

    public static function find(int $id): self
    {
        $ligne = Database::query(self::SELECT . ' WHERE t.id=?', [$id])->fetch();
        if (!$ligne) {
            throw new HttpException('Trajet introuvable.', 404);
        }
        return new self($ligne);
    }

    public static function verrouiller(int $id): self
    {
        $ligne = Database::query('SELECT t.*, u.nom AS nom_conducteur FROM trajets t JOIN utilisateurs u ON u.id=t.id_conducteur WHERE t.id=?' . Database::lockClause(), [$id])->fetch();
        if (!$ligne) {
            throw new HttpException('Trajet introuvable.', 404);
        }
        return new self($ligne);
    }

    public static function getList(string $condition = '1=1', array $params = []): array
    {
        $lignes = Database::query(self::SELECT . ' WHERE ' . $condition . ' ORDER BY t.date_heure DESC', $params)->fetchAll();
        $resultat = [];

        foreach ($lignes as $ligne) {
            $resultat[] = new self($ligne);
        }

        return $resultat;
    }

    public static function search(string $origine = '', string $destination = '', string $date = ''): array
    {
        $where = "t.statut='Ouvert' AND t.places_restantes>0 AND t.date_heure>?";
        $params = [now()];
        foreach (['t.origine' => $origine, 't.destination' => $destination] as $champ => $valeur) {
            if ($valeur !== '') {
                $where .= " AND $champ LIKE ?";
                $params[] = '%' . $valeur . '%';
            }
        }
        if ($date !== '') {
            $dateRecherche = DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if (!$dateRecherche || $dateRecherche->format('Y-m-d') !== $date) {
                throw new DomainException('Date de recherche invalide.');
            }
            $where .= ' AND t.date_heure>=? AND t.date_heure<?';
            $params[] = $date.' 00:00:00';
            $params[] = $dateRecherche->modify('+1 day')->format('Y-m-d 00:00:00');
        }
        return array_reverse(self::getList($where, $params));
    }

    public static function validate(array $donnees): array
    {
        $origine = inputText($donnees, 'origine');
        $destination = inputText($donnees, 'destination');
        $date = inputText($donnees, 'date_heure');
        $places = filter_var($donnees['places'] ?? null, FILTER_VALIDATE_INT);
        $prix = filter_var($donnees['prix'] ?? null, FILTER_VALIDATE_FLOAT);
        if (mb_strlen($origine)<2 || mb_strlen($origine)>120 || mb_strlen($destination)<2 || mb_strlen($destination)>120 || mb_strtolower($origine) === mb_strtolower($destination)) {
            throw new DomainException('Saisissez deux villes distinctes de 2 à 120 caractères.');
        }
        $dateDepart = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $date);
        if (!$dateDepart || $dateDepart->format('Y-m-d\TH:i') !== $date || $dateDepart<=new DateTimeImmutable()) {
            throw new DomainException('La date de départ doit être valide et future.');
        }
        if ($places === false || $places<1 || $places>8 || $prix === false || $prix<0 || $prix>200) {
            throw new DomainException('Prévoyez 1 à 8 places et un prix entre 0 et 200 €.');
        }
        return [$origine, $destination, $dateDepart->format('Y-m-d H:i:s'), $places, $prix];
    }

    public static function create(int $conducteur, string $origine, string $destination, string $date, int $places, ?float $prix): int
    {
        [$origine, $destination, $date, $places, $prix] = self::validate([
            'origine' => $origine,
            'destination' => $destination,
            'date_heure' => str_replace(' ', 'T', substr($date, 0, 16)),
            'places' => $places,
            'prix' => $prix ?? 0
        ]);
        Database::query("INSERT INTO trajets (origine,destination,date_heure,places_totales,places_restantes,prix,statut,id_conducteur)
            VALUES (?,?,?,?,?,?,'Ouvert',?)", [$origine, $destination, $date, $places, $places, $prix, $conducteur]);
        return (int) Database::get()->lastInsertId();
    }

    public function checkDriver(int $id): void
    {
        if ($id !== $this->idConducteur) {
            throw new HttpException('Ce trajet appartient à un autre conducteur.', 403);
        }
    }

    public function reserverPlace(int $idPassager): bool
    {
        // Cette méthode confirme une demande : l'appelant doit détenir le verrou du trajet.
        if (!Database::get()->inTransaction()) {
            throw new LogicException('La confirmation nécessite une transaction.');
        }
        if ($idPassager === $this->idConducteur || $this->hasDeparted() || $this->statut !== 'Ouvert' || $this->placesRestantes<1) {
            throw new DomainException('Aucune place ne peut être confirmée.');
        }
        if (!Database::query("SELECT 1 FROM reservations WHERE id_trajet=? AND id_passager=? AND statut='En attente'", [$this->id, $idPassager])->fetchColumn()) {
            throw new DomainException('Aucune demande en attente.');
        }
        $restantes = $this->placesRestantes-1;
        Database::query('UPDATE trajets SET places_restantes=?,statut=? WHERE id=?', [$restantes, $restantes === 0?'Complet':'Ouvert', $this->id]);
        $this->placesRestantes = $restantes;
        $this->statut = $restantes === 0?'Complet':'Ouvert';
        return true;
    }

    public static function update(int $id, int $conducteur, array $donnees): void
    {
        $valeurs = self::validate($donnees);
        Database::transaction(function() use($id, $conducteur, $valeurs) {
            $trajet = self::verrouiller($id);
            $trajet->checkDriver($conducteur);
            if ($trajet->statut === 'Termine' || $trajet->hasDeparted()) {
                throw new DomainException('Un trajet passé ne peut plus être modifié.');
            }
            [$origine, $destination, $date, $places, $prix] = $valeurs;
            $restantes = $places-$trajet->occupiedSeats();
            if ($restantes<0) {
                throw new DomainException('Le nombre de places est inférieur aux réservations confirmées.');
            }
            Database::query('UPDATE trajets SET origine=?,destination=?,date_heure=?,places_totales=?,places_restantes=?,prix=?,statut=? WHERE id=?', [
                $origine,
                $destination,
                $date,
                $places,
                $restantes,
                $prix,
                $restantes === 0?'Complet':'Ouvert',
                $id
            ]);
        });
    }

    public static function cloturer(int $id, int $conducteur): void
    {
        Database::transaction(function() use($id, $conducteur) {
            $trajet = self::verrouiller($id);
            $trajet->checkDriver($conducteur);
            if (!$trajet->hasDeparted() || $trajet->statut === 'Termine') {
                throw new DomainException('La clôture est possible après le départ d’un trajet non terminé.');
            }
            Database::query("UPDATE trajets SET statut='Termine' WHERE id=?", [$id]);
            Database::query("UPDATE reservations SET statut='Refusee' WHERE id_trajet=? AND statut='En attente'", [$id]);
        });
    }

    public static function delete(int $id, int $conducteur): void
    {
        Database::transaction(function() use($id, $conducteur) {
            self::verrouiller($id)->checkDriver($conducteur);
            Database::query('DELETE FROM trajets WHERE id=?', [$id]);
        });
    }

    public function hasDeparted(): bool
    {
        return $this->dateHeure<=now();
    }

    public function occupiedSeats(): int
    {
        return $this->placesTotales-$this->placesRestantes;
    }

    public function statusClass(): string
    {
        return match ($this->statut) {
            'Ouvert' => 'badge-ouvert', 'Complet' => 'badge-complet', default => 'badge-termine'
        }
        ;
    }

    public static function statistics(?int $conducteur = null): array
    {
        return Database::query("SELECT COUNT(*) AS publies, COUNT(*) AS total_trajets, COALESCE(SUM(CASE WHEN statut='Termine' THEN 1 ELSE 0 END),0) AS realises, COALESCE(SUM(CASE WHEN statut='Ouvert' THEN 1 ELSE 0 END),0) AS ouverts, COALESCE(SUM(CASE WHEN statut='Complet' THEN 1 ELSE 0 END),0) AS complets, COALESCE(SUM(places_totales-places_restantes),0) AS places_occupees, COALESCE(SUM(places_totales),0) AS places_totales, COALESCE(AVG(100.0*(places_totales-places_restantes)/places_totales),0) AS taux
            FROM trajets" . ($conducteur === null?'':' WHERE id_conducteur=?'), $conducteur === null?[]:[$conducteur])->fetch();
    }
}
