<?php
/**
 * ------------------------------------------------------------------
 *  Gabarit : footer (bas de page) commun à toutes les pages
 * ------------------------------------------------------------------
 *  Contient aussi la boîte de dialogue de confirmation, utilisée par
 *  js/script.js avant chaque suppression ou annulation.
 * ------------------------------------------------------------------
 */
?>
</main>

<footer class="footer">
    <div class="conteneur">
        <span class="footer-marque"><img src="images/logo.svg" alt=""><strong>CoVoie</strong> - <?= SLOGAN_APPLICATION ?></span>
        <span>Projet BTS SIO option SLAM - <?= date('Y') ?></span>
    </div>
</footer>

<!-- Boîte de dialogue de confirmation (remplace le confirm() du navigateur) -->
<dialog class="dialogue" id="dialogue-confirmation" aria-labelledby="dialogue-titre">
    <form method="dialog">
        <h2 id="dialogue-titre">Confirmer l'action</h2>
        <p id="dialogue-texte"></p>
        <div class="actions">
            <button class="bouton bouton--secondaire bouton--petit" value="annuler">Annuler</button>
            <button class="bouton bouton--danger-plein bouton--petit" value="confirmer">Confirmer</button>
        </div>
    </form>
</dialog>

<script src="js/script.js?v=<?= filemtime('js/script.js') ?>"></script>
</body>
</html>
