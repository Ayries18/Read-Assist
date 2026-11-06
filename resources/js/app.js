/**
 * Entry point JS aplikasi.
 *
 * Saat ini satu-satunya tanggung jawabnya: reveal-on-scroll untuk section
 * landing page (lihat `landing.css`). Konten TIDAK pernah disembunyikan
 * oleh CSS kecuali class `.ra-js` sudah dipasang di <html> — kelas itu
 * dipasang oleh script inline di <head> (lihat layouts/app.blade.php),
 * jadi bila bundel ini gagal dimuat, fallback 4 detik di sana tetap
 * membuka semua konten.
 */
(function () {
    'use strict';

    var revealSelector = '[data-ra-reveal]';
    var nodes = document.querySelectorAll(revealSelector);

    if (!nodes.length) {
        return;
    }

    var revealAll = function () {
        for (var i = 0; i < nodes.length; i++) {
            nodes[i].classList.add('is-revealed');
        }
    };

    var prefersReducedMotion =
        window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (prefersReducedMotion || typeof window.IntersectionObserver !== 'function') {
        revealAll();
        return;
    }

    var observer = new IntersectionObserver(
        function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) {
                    return;
                }
                entry.target.classList.add('is-revealed');
                observer.unobserve(entry.target);
            });
        },
        {
            // rootMargin 0 0 -12% 0 → elemen baru ter-trigger saat sudah
            // masuk viewport sedikit, bukan tepat di tepi bawah.
            rootMargin: '0px 0px -12% 0px',
            threshold: 0.08,
        }
    );

    for (var j = 0; j < nodes.length; j++) {
        observer.observe(nodes[j]);
    }
})();
