/** @type {import('tailwindcss').Config} */
module.exports = {
   content: [
    './formulaire.html',
    './alerte.html',
  ],
  theme: {
    extend: {},
  },
  plugins: [],
}

//npx tailwindcss -i ./src/input.css -o ./dist/output.css --watch
