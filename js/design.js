/* Présentation seulement : aucun accès aux formulaires, sessions ou données. */
(() => {
  const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  const scene = document.querySelector('.hero-carte');
  const applyMotionPreference = () => {
    document.querySelectorAll('.campus-scene').forEach(svg => {
      if (reducedMotion.matches) svg.pauseAnimations();
      else svg.unpauseAnimations();
    });
    if (reducedMotion.matches && scene) {
      scene.style.removeProperty('--pointer-x');
      scene.style.removeProperty('--pointer-y');
    }
  };
  applyMotionPreference();
  reducedMotion.addEventListener('change', applyMotionPreference);
  if (scene && window.matchMedia('(hover: hover) and (pointer: fine)').matches) {
    let frame;
    scene.addEventListener('pointermove', event => {
      if (reducedMotion.matches) return;
      cancelAnimationFrame(frame);
      frame = requestAnimationFrame(() => {
        const rect = scene.getBoundingClientRect();
        scene.style.setProperty('--pointer-x', ((event.clientX - rect.left) / rect.width - .5) * 10 + 'px');
        scene.style.setProperty('--pointer-y', ((event.clientY - rect.top) / rect.height - .5) * 7 + 'px');
      });
    });
    scene.addEventListener('pointerleave', () => {
      cancelAnimationFrame(frame);
      scene.style.setProperty('--pointer-x', '0px');
      scene.style.setProperty('--pointer-y', '0px');
    });
  }
  if ('IntersectionObserver' in window && !reducedMotion.matches) {
    const observer = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('est-visible');
          observer.unobserve(entry.target);
        }
      });
    }, { threshold: .08 });
    document.querySelectorAll('.grille-chiffres, main > .deux-colonnes, .carte-trajet, .bloc').forEach(element => {
      // Le contenu est visible par défaut, même sans JavaScript.
      element.classList.add('apparition');
      observer.observe(element);
    });
  }
})();
