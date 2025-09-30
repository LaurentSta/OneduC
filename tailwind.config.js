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
    aspectRatio, // ✅ plugin officiel
    forms,
    plugin(function ({ addComponents }) {
      addComponents({
        // Bouton principal orange
        '.btn-oneduc': {
          '@apply inline-block px-4 py-2 text-base tracking-wide font-varela text-white bg-orangeone border-4 border-orangeone rounded-full transition duration-300 hover:bg-white hover:text-orangeone active:scale-95': {},
        },
        // Bouton secondaire bleu
        '.btn-oneduc-blue': {
          '@apply inline-block px-4 py-2 text-base tracking-wide font-varela text-white bg-bleuone border-4 border-bleuone rounded-full transition duration-300 hover:bg-white hover:text-bleuone active:scale-95': {},
        },
        // Typographie lisible pour les contenus
        '.prose-oneduc': {
          '@apply text-[18px] leading-relaxed space-y-4 font-lisible': {},
        },
      })
    }),
  ],
}
