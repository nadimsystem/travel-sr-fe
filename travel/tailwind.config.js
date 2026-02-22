/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./index.html",
    "./src/**/*.{vue,js,ts,jsx,tsx}",
  ],
  theme: {
    extend: {
      colors: {
        'sutan-dark': '#121212',
        'sutan-gold': '#D4AF37',
        'sutan-light': '#F8F9FA',
        'sutan-secondary': '#222222',
      },
      fontFamily: {
        'sans': ['Plus Jakarta Sans', 'sans-serif'],
        'serif': ['Playfair Display', 'serif'],
      },
      borderRadius: {
        '4xl': '2rem',
        '5xl': '2.5rem',
      }
    },
  },
  plugins: [],
}
