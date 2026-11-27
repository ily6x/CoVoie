/* ==================================================================
   CoVoie - script.js
   ------------------------------------------------------------------
   Quelques aides pour l'utilisateur. Le site fonctionne aussi SANS
   JavaScript : toutes les règles sont vérifiées en PHP.

     1. Confirmation avant une suppression ou une annulation
     2. Bouton "Envoi..." pendant l'envoi d'un formulaire (évite le double clic)
     3. Bouton qui inverse les champs "Départ" et "Arrivée"
     4. Fermeture des messages
   ================================================================== */


/* ---------- 1. Confirmation avant les actions définitives ---------- */
// Tous les formulaires qui ont un attribut data-confirmation="..." sont concernés.
// On affiche notre boîte <dialog> (dans footer.php) ; si le navigateur ne la
// connaît pas, on utilise le confirm() classique.
const dialogue = document.querySelector('#dialogue-confirmation');
const texteDialogue = document.querySelector('#dialogue-texte');

document.querySelectorAll('form[data-confirmation]').forEach(function (formulaire) {
    formulaire.addEventListener('submit', function (evenement) {
        // Deuxième passage : l'utilisateur a déjà confirmé, on laisse partir le formulaire
        if (formulaire.dataset.confirme === 'oui') {
            return;
        }

        evenement.preventDefault();
        const question = formulaire.dataset.confirmation;

        if (!dialogue || typeof dialogue.showModal !== 'function') {
            if (confirm(question)) {
                formulaire.dataset.confirme = 'oui';
                formulaire.requestSubmit();
            }
            return;
        }

        texteDialogue.textContent = question;
        dialogue.showModal();

        // Quand la boîte se ferme, returnValue contient la valeur du bouton cliqué
        dialogue.addEventListener('close', function () {
            if (dialogue.returnValue === 'confirmer') {
                formulaire.dataset.confirme = 'oui';
                formulaire.requestSubmit();
            }
        }, { once: true });
    });
});


/* ---------- 2. Bouton "Envoi..." ---------- */
// Une fois le formulaire parti, on désactive son bouton : un double clic
// n'envoie pas deux fois la même demande.
document.querySelectorAll('form[method="post"]').forEach(function (formulaire) {
    formulaire.addEventListener('submit', function (evenement) {
        // Si l'envoi a été bloqué (confirmation en attente), on ne touche à rien
        if (evenement.defaultPrevented) {
            return;
        }
        const bouton = evenement.submitter;
        if (bouton) {
            // Petit délai : le navigateur lit d'abord le formulaire, puis on désactive
            setTimeout(function () {
                bouton.disabled = true;
                bouton.dataset.texte = bouton.textContent;
                bouton.textContent = 'Envoi…';
            }, 0);
        }
    });
});

// Si on revient sur la page avec le bouton "Précédent", on réactive les boutons
window.addEventListener('pageshow', function () {
    document.querySelectorAll('button[data-texte]').forEach(function (bouton) {
        bouton.disabled = false;
        bouton.textContent = bouton.dataset.texte;
    });
});


/* ---------- 3. Inverser départ et arrivée ---------- */
document.querySelectorAll('[data-inverser]').forEach(function (bouton) {
    bouton.addEventListener('click', function () {
        const depart = document.querySelector('#depart');
        const arrivee = document.querySelector('#arrivee');

        // Échange classique avec une variable temporaire
        const temporaire = depart.value;
        depart.value = arrivee.value;
        arrivee.value = temporaire;
    });
});


/* ---------- 4. Fermer un message ---------- */
document.querySelectorAll('[data-fermer]').forEach(function (bouton) {
    bouton.addEventListener('click', function () {
        bouton.closest('.message').remove();
    });
});
