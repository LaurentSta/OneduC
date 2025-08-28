import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import plugin from 'tailwindcss/plugin'
import aspectRatio from '@tailwindcss/aspect-ratio' // ✅ ajout ici

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './resources/**/*.blade.php',
    './resources/**/*.js',
    './resources/**/*.vue',
  ],
  theme: {
    extend: {
      fontFamily: {
        raleway: ['Raleway', ...defaultTheme.fontFamily.sans],
        varela: ['Varela Round', 'sans-serif'],
        lisible: ['OpenDyslexic', 'Arial', 'sans-serif'], // police de lecture accessible
      },
      fontSize: {
        'titre': '55px',
        'sous-titre': '29px',
      },
      colors: {
        bleuone: '#004461',
        orangeone: '#E94D2A',
        vertone: '#01c69c',
      },
    },
  },
  plugins: [
    aspectRatio, // ✅ ajouté ici
    forms,
    plugin(function ({ addComponents }) {
      addComponents({
        '.btn-oneduc': {
          '@apply inline-block px-4 py-2 text-base tracking-wide font-varela text-white bg-orangeone border-4 border-orangeone rounded-full transition duration-300 hover:bg-white hover:text-orangeone active:scale-95': {},
        },
        '.prose-oneduc': {
          '@apply text-[18px] leading-relaxed space-y-4 font-lisible': {},
        },
        '.tooltip-oneduc': {
          '@apply pointer-events-none absolute z-10 bg-bleuone text-white text-[11px] rounded px-2 py-1 shadow invisible opacity-0': {},
        },
        '.tooltip-right': {
          '@apply left-full ml-2 top-1/2 -translate-y-1/2': {},
        }
      });
    }),
  ],
}
