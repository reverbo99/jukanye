/**
 * Media loading helpers for /site (legacy Nicepage HTML + Tailwind hybrid).
 */
function enhanceLazyVideos() {
    const videos = document.querySelectorAll('video[data-jk-lazy-video]');
    if (!videos.length) {
        return;
    }

    const loadVideo = (video) => {
        if (video.dataset.jkLoaded === '1') {
            return;
        }
        video.dataset.jkLoaded = '1';

        // Sources may already be in DOM; calling load() after near-viewport is enough
        // when preload="none" was set server-side.
        if (typeof video.load === 'function') {
            video.load();
        }
    };

    if (!('IntersectionObserver' in window)) {
        videos.forEach(loadVideo);
        return;
    }

    const io = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    loadVideo(entry.target);
                    io.unobserve(entry.target);
                }
            });
        },
        { rootMargin: '200px 0px', threshold: 0.01 }
    );

    videos.forEach((video) => io.observe(video));
}

function markGalleriesReady() {
    document.querySelectorAll('.wb_gallery').forEach((gallery) => {
        const mark = () => gallery.classList.add('is-ready');
        if (gallery.querySelector('img, canvas, video, .wb-gallery-image')) {
            mark();
            return;
        }
        const mo = new MutationObserver(() => {
            if (gallery.querySelector('img, canvas, video, .wb-gallery-image')) {
                mark();
                mo.disconnect();
            }
        });
        mo.observe(gallery, { childList: true, subtree: true });
        // Fallback: stop shimmer after GalleryLib init window
        setTimeout(mark, 4000);
    });
}

function wireMobileMenu() {
    document.querySelectorAll('.wb-menu-mobile .btn-collapser').forEach((btn) => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            btn.closest('.wb-menu-mobile')?.classList.toggle('is-open');
        });
    });
}

function deferYoutubeIframes() {
    document.querySelectorAll('iframe[data-src]').forEach((frame) => {
        if (frame.getAttribute('src')) {
            return;
        }

        const activate = () => {
            const src = frame.getAttribute('data-src');
            if (src) {
                frame.setAttribute('src', src);
            }
        };

        if (!('IntersectionObserver' in window)) {
            activate();
            return;
        }

        const io = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        activate();
                        io.unobserve(frame);
                    }
                });
            },
            { rootMargin: '240px 0px' }
        );
        io.observe(frame);
    });
}

function boot() {
    enhanceLazyVideos();
    markGalleriesReady();
    wireMobileMenu();
    deferYoutubeIframes();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
} else {
    boot();
}
