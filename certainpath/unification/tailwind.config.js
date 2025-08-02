/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/*.html.twig",
  ],
  theme: {
    extend: {
      colors: {
        indigo: {
          600: '#17084A',
        }
      }
    }
  },
  plugins: [
    require('@tailwindcss/forms'),
  ],
}
