@if($cookieConsentConfig['enabled'])

    @include('cookie-consent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const COOKIE_NAME = '{{ $cookieConsentConfig['cookie_name'] }}';
            const COOKIE_LIFETIME_DAYS = {{ $cookieConsentConfig['cookie_lifetime'] }};
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';

            const banner = document.querySelector('.js-cookie-consent');
            const modalBackdrop = document.querySelector('.js-cookie-consent-modal-backdrop');

            function getConsent() {
                const match = document.cookie.split('; ').find((row) => row.startsWith(COOKIE_NAME + '='));

                if (!match) {
                    return null;
                }

                try {
                    const consent = JSON.parse(decodeURIComponent(match.split('=')[1]));
                    return consent && typeof consent === 'object' ? consent : null;
                } catch (e) {
                    return null;
                }
            }

            function hasConsented(category) {
                const consent = getConsent();
                return !!(consent && consent[category]);
            }

            function setCookie(consent) {
                const date = new Date();
                date.setTime(date.getTime() + (COOKIE_LIFETIME_DAYS * 24 * 60 * 60 * 1000));
                document.cookie = COOKIE_NAME + '=' + encodeURIComponent(JSON.stringify(consent))
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            function hideBanner() {
                if (banner) {
                    banner.style.display = 'none';
                }
            }

            function openPreferences() {
                if (!modalBackdrop) {
                    return;
                }

                const consent = getConsent() || { essentiel: true };

                modalBackdrop.querySelectorAll('.js-cookie-consent-category').forEach((input) => {
                    input.checked = !!consent[input.dataset.category];
                });

                modalBackdrop.classList.remove('hidden');
                modalBackdrop.classList.add('flex');
            }

            function closePreferences() {
                if (!modalBackdrop) {
                    return;
                }

                modalBackdrop.classList.add('hidden');
                modalBackdrop.classList.remove('flex');
            }

            function saveConsent(consent) {
                setCookie(consent);
                hideBanner();
                closePreferences();
                document.dispatchEvent(new CustomEvent('cookie-consent:updated', { detail: consent }));
            }

            function acceptAll() {
                saveConsent({ essentiel: true, video: true });
            }

            function declineAll() {
                saveConsent({ essentiel: true, video: false });
            }

            function saveFromModal() {
                const consent = { essentiel: true };

                modalBackdrop.querySelectorAll('.js-cookie-consent-category').forEach((input) => {
                    consent[input.dataset.category] = input.checked;
                });

                saveConsent(consent);
            }

            document.querySelectorAll('.js-cookie-consent-agree').forEach((btn) => btn.addEventListener('click', acceptAll));
            document.querySelectorAll('.js-cookie-consent-decline').forEach((btn) => btn.addEventListener('click', declineAll));
            document.querySelectorAll('.js-cookie-consent-save').forEach((btn) => btn.addEventListener('click', saveFromModal));
            document.querySelectorAll('.js-cookie-consent-modal-close').forEach((btn) => btn.addEventListener('click', closePreferences));

            document.querySelectorAll('.js-cookie-consent-manage').forEach((btn) => btn.addEventListener('click', (event) => {
                event.preventDefault();
                openPreferences();
            }));

            if (modalBackdrop) {
                modalBackdrop.addEventListener('click', (event) => {
                    if (event.target === modalBackdrop) {
                        closePreferences();
                    }
                });
            }

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && modalBackdrop && !modalBackdrop.classList.contains('hidden')) {
                    closePreferences();
                }
            });

            if (getConsent()) {
                hideBanner();
            }

            return {
                hasConsented: hasConsented,
                openPreferences: openPreferences,
            };
        })();
    </script>

@endif
