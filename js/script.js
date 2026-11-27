/* ==================================================================
   CoVoie - script.js
   ------------------------------------------------------------------
   Trois petites aides pour l'utilisateur. Le site fonctionne aussi
   SANS JavaScript : toutes les règles sont vérifiées en PHP.

     1. Menu mobile : ouvrir / fermer le menu sur téléphone
     2. Confirmation avant une suppression ou une annulation
     3. Bouton qui inverse les champs "Départ" et "Arrivée"
   ================================================================== */


/* ---------- 1. Menu mobile ---------- */
const boutonMenu = document.querySelector('.bouton-menu');
const menu = document.querySelector('#menu');

if (boutonMenu) {
    boutonMenu.addEventListener('click', function () {
        // On ajoute ou retire la classe "ouvert" : le CSS affiche ou cache le menu
        menu.classList.toggle('ouvert');

        // Pour les lecteurs d'écran : le menu est-il ouvert ?
        boutonMenu.setAttribute('aria-expanded', menu.classList.contains('ouvert'));
    });
}


/* ---------- 2. Confirmation avant les actions définitives ---------- */
// Tous les formulaires qui ont un attribut data-confirmation="..." sont concernés.
const formulairesAConfirmer = document.querySelectorAll('form[data-confirmation]');

formulairesAConfirmer.forEach(function (formulaire) {
    formulaire.addEventListener('submit', function (evenement) {
        const question = formulaire.dataset.confirmation;

        // Si l'utilisateur clique sur "Annuler", on bloque l'envoi du formulaire
        if (!confirm(question)) {
            evenement.preventDefault();
        }
    });
});


/* ---------- 3. Inverser départ et arrivée ---------- */
const boutonsInverser = document.querySelectorAll('[data-inverser]');

boutonsInverser.forEach(function (bouton) {
    bouton.addEventListener('click', function () {
        const depart = document.querySelector('#depart');
        const arrivee = document.querySelector('#arrivee');

        // Échange classique avec une variable temporaire
        const temporaire = depart.value;
        depart.value = arrivee.value;
        arrivee.value = temporaire;
    });
});
