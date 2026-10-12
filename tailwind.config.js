/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './app/Views/**/*.{php,twig}',
    './public/assets/js/**/*.js',
    './app/Service/**/*.php',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Montserrat', 'sans-serif'],
        logo: ['Pacifico', 'cursive'],
      },
      colors: {
        brand: {
          50:  '#fdf1f1',
          100: '#fce1e1',
          400: '#e2323a',
          500: '#c8102e',
          600: '#a80c26',
          700: '#8a0a1f',
          900: '#4a0510',
        },
        ink: '#161616',
      },
      maxWidth: {
        '1536': '1536px',
      },
    },
  },
  daisyui: {
    themes: false,
    logs: false,
  },
  plugins: [require('daisyui')],
}
