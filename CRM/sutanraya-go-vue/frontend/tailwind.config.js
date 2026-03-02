/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1e293b', // Soft Black (Slate 800)
        gold: '#d4af37', // SR Gold
        'gold-light': '#fefce8', // Pastel Yellow
        'gold-dark': '#b45309', 
        accent: '#d4af37',
      },
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'sans-serif'],
      }
    },
  },
  plugins: [],
}
