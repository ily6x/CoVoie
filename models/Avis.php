<?php

class Avis
{
    public int $id;
    public int $note;
    public ?string $commentaire;
    public string $date;
    public int $idTrajet;
    public int $idAuteur;
    public int $idCible;
    public string $nomAuteur;
    public string $nomCible;
    public string $origine;
    public string $destination;

    // Transforme une ligne de la base de données en objet utilisable dans les vues.
    public function __construct(array $ligne)
    {
        $this->id = (int) $ligne['id'];
        $this->note = (int) $ligne['note'];
        $this->commentaire = $ligne['commentaire'];
        $this->date = $ligne['date_avis'];
        $this->idTrajet = (int) $ligne['id_trajet'];
        $this->idAuteur = (int) $ligne['id_auteur'];
        $this->idCible = (int) $ligne['id_cible'];
        $this->nomAuteur = $ligne['nom_auteur'];
        $this->nomCible = $ligne['nom_cible'];
        $this->origine = $ligne['origine'];
        $this->destination = $ligne['destination'];
    }

    public static function noteMoyenne(int $idUtilisateur): ?float
    {
        $v = Database::query('SELECT AVG(note) FROM avis WHERE id_cible=?', [$idUtilisateur])->fetchColumn();
        return $v === null || $v === false ? null : (float) $v;
    }

    public static function nombreAvis(int $id): int
    {
        return (int) Database::query('SELECT COUNT(*) FROM avis WHERE id_cible=?', [$id])->fetchColumn();
    }

    public static function getList(?int $cible = null): array
    {
        $lignes = Database::query('SELECT a.*, u.nom AS nom_auteur, c.nom AS nom_cible, t.origine,t.destination
            FROM avis a
            JOIN utilisateurs u ON u.id=a.id_auteur
            JOIN utilisateurs c ON c.id=a.id_cible
            JOIN trajets t ON t.id=a.id_trajet' . ($cible === null?'':' WHERE a.id_cible=?') . ' ORDER BY a.date_avis DESC', $cible === null?[]:[$cible])->fetchAll();
        $resultat = [];

        foreach ($lignes as $ligne) {
            $resultat[] = new self($ligne);
        }

        return $resultat;
    }

    public static function hasReviewed(int $trajet, int $auteur, int $cible): bool
    {
        return (bool) Database::query('SELECT 1 FROM avis WHERE id_trajet=? AND id_auteur=? AND id_cible=?', [$trajet, $auteur, $cible])->fetchColumn();
    }

    public static function participants(Trajet $trajet, int $auteur): array
    {
        if ($trajet->statut !== 'Termine' || !$trajet->hasDeparted()) {
            throw new DomainException('La notation est disponible après la clôture du trajet.');
        }
        $confirmes = Reservation::getList("r.id_trajet=? AND r.statut='Confirmee'", [$trajet->id]);
        $resultat = [];
        if ($auteur === $trajet->idConducteur) {
            foreach ($confirmes as $ligne) {
                $resultat[] = [
                    'utilisateur' => Utilisateur::find($ligne->idPassager),
                    'role' => 'Passager',
                    'dejaNote' => self::hasReviewed($trajet->id, $auteur, $ligne->idPassager)
                ];
            }
        }
        else {
            foreach ($confirmes as $ligne) {
                if ($ligne->idPassager === $auteur) {
                    $resultat[] = [
                        'utilisateur' => Utilisateur::find($trajet->idConducteur),
                        'role' => 'Conducteur',
                        'dejaNote' => self::hasReviewed($trajet->id, $auteur, $trajet->idConducteur)
                    ];
                }
            }
            if (!$resultat) {
                throw new HttpException('Vous n’êtes pas un participant confirmé.', 403);
            }
        }
        return $resultat;
    }

    public static function create(int $trajet, int $auteur, int $cible, int $note, string $commentaire): void
    {
        if ($note<1 || $note>5 || mb_strlen($commentaire)>500) {
            throw new DomainException('La note doit être entre 1 et 5 et le commentaire limité à 500 caractères.');
        }
        Database::transaction(function() use($trajet, $auteur, $cible, $note, $commentaire) {
            $trajetTrouve = Trajet::verrouiller($trajet);
            $participants = self::participants($trajetTrouve, $auteur);
            $autorise = false;
            foreach ($participants as $participant) {
                if ($participant['utilisateur']->id === $cible && !$participant['dejaNote']) {
                    $autorise = true;
                }
            }
            if (!$autorise) {
                throw new DomainException('Cette personne ne peut pas être notée, ou a déjà reçu votre avis.');
            }
            Database::query('INSERT INTO avis (note,commentaire,date_avis,id_trajet,id_auteur,id_cible) VALUES (?,?,?,?,?,?)', [$note, $commentaire?:null, now(), $trajet, $auteur, $cible]);
        });
    }

    public function stars(): string
    {
        return str_repeat('★', $this->note) . str_repeat('☆', 5-$this->note);
    }
}
