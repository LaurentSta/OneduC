import defaultTheme from 'tailwindcss/defaultTheme'
import forms from '@tailwindcss/forms'
import plugin from 'tailwindcss/plugin'
import aspectRatio from '@tailwindcss/aspect-ratio'

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
        lisible: ['OpenDyslexic', 'Arial', 'sans-serif'],
      },
      fontSize: {
        'titre': ['55px', { lineHeight: '1.1', fontWeight: '800' }],
        'sous-titre': ['28px', { lineHeight: '1.3', fontWeight: '600' }],
      },
      colors: {
        bleuone: {
          DEFAULT: '#004461',
          light: '#005d85',
          dark: '#002c3f',
        },
        orangeone: {
          DEFAULT: '#E94D2A',
          hover: '#c43d1f',
          light: '#ff7a5c',
        },
        vertone: '#01c69c',
      },
      boxShadow: {
        'soft': '0 10px 30px -10px rgba(0, 68, 97, 0.1)',
        'card': '0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06)',
      }
    },
  },

  plugins: [
    aspectRatio,
    forms,
    plugin(function ({ addComponents }) {
      addComponents({
        // Bouton Principal (Inclusion : focus visible)
        '.btn-oneduc': {
          '@apply inline-flex items-center justify-center px-8 py-3 text-lg font-varela text-white bg-orangeone border-4 border-orangeone rounded-full transition-all duration-300 hover:bg-white hover:text-orangeone focus:outline-none focus:ring-4 focus:ring-orange-200 active:scale-95': {},
        },
        // Bouton Secondaire
        '.btn-oneduc-outline': {
          '@apply inline-flex items-center justify-center px-8 py-3 text-lg font-varela text-bleuone bg-white border-4 border-bleuone rounded-full transition-all duration-300 hover:bg-bleuone hover:text-white focus:outline-none focus:ring-4 focus:ring-blue-100 active:scale-95': {},
        },
        // Conteneur de texte accessible
        '.prose-oneduc': {
          '@apply font-lisible text-[18px] leading-[1.8] text-gray-700 space-y-5': {},
        },
        // Carte de fonctionnalité
        '.card-feature': {
          '@apply bg-white p-8 rounded-3xl shadow-soft border-2 border-transparent hover:border-vertone hover:translate-y-[-5px] transition-all duration-300': {},
        }
      })
    }),
  ],
}