@if($cookieConsentConfig['enabled'] && ! $alreadyConsentedWithCookies)

    @include('cookie-consent::dialogContents')

    <script>

        window.laravelCookieConsent = (function () {

            const COOKIE_NAME = '{{ $cookieConsentConfig['cookie_name'] }}';
            const COOKIE_VALUE_ACCEPTED = '1';
            const COOKIE_VALUE_DECLINED = '0';
            const COOKIE_DOMAIN = '{{ config('session.domain') ?? request()->getHost() }}';

            function consentWithCookies() {
                setCookie(COOKIE_NAME, COOKIE_VALUE_ACCEPTED, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
                document.dispatchEvent(new CustomEvent('cookie-consent:accepted'));
            }

            function declineCookies() {
                // Même cookie, valeur 0 : suffit à empêcher la bannière de
                // se réafficher (le composeur du package ne vérifie que
                // l'existence du cookie, pas sa valeur), sans marquer un
                // consentement qui n'a pas été donné.
                setCookie(COOKIE_NAME, COOKIE_VALUE_DECLINED, {{ $cookieConsentConfig['cookie_lifetime'] }});
                hideCookieDialog();
                document.dispatchEvent(new CustomEvent('cookie-consent:declined'));
            }

            function getCookieValue(name) {
                const match = document.cookie.split('; ').find((row) => row.startsWith(name + '='));
                return match ? match.split('=')[1] : null;
            }

            function cookieExists(name) {
                return getCookieValue(name) !== null;
            }

            function hasAccepted() {
                return getCookieValue(COOKIE_NAME) === COOKIE_VALUE_ACCEPTED;
            }

            function hideCookieDialog() {
                const dialogs = document.getElementsByClassName('js-cookie-consent');

                for (let i = 0; i < dialogs.length; ++i) {
                    dialogs[i].style.display = 'none';
                }
            }

            function setCookie(name, value, expirationInDays) {
                const date = new Date();
                date.setTime(date.getTime() + (expirationInDays * 24 * 60 * 60 * 1000));
                document.cookie = name + '=' + value
                    + ';expires=' + date.toUTCString()
                    + ';domain=' + COOKIE_DOMAIN
                    + ';path=/{{ config('session.secure') ? ';secure' : null }}'
                    + '{{ config('session.same_site') ? ';samesite='.config('session.same_site') : null }}';
            }

            if (cookieExists(COOKIE_NAME)) {
                hideCookieDialog();
            }

            const agreeButtons = document.getElementsByClassName('js-cookie-consent-agree');

            for (let i = 0; i < agreeButtons.length; ++i) {
                agreeButtons[i].addEventListener('click', consentWithCookies);
            }

            const declineButtons = document.getElementsByClassName('js-cookie-consent-decline');

            for (let i = 0; i < declineButtons.length; ++i) {
                declineButtons[i].addEventListener('click', declineCookies);
            }

            return {
                consentWithCookies: consentWithCookies,
                declineCookies: declineCookies,
                hasAccepted: hasAccepted,
                hideCookieDialog: hideCookieDialog
            };
        })();
    </script>

@endif
