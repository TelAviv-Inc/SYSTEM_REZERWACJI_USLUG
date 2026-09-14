/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        primary: '#1e40af',
        secondary: '#3b82f6',
        accent: '#f59e0b',
        dark: '#1f2937',
        light: '#f9fafb',
        'brand-navy': '#111C3D',      // Granat (nagłówki, CTA)
        'brand-blue': '#1E3A8A',      // Niebieski (przyciski, logo)
        'brand-accent': '#2563EB',    // Niebieski akcent (linki, hover, batony)
        'brand-hover': '#16306F',     // Niebieski hover
        'brand-bg': '#F6F7F9',        // Tło strony
        'brand-light-bg': '#EEF1F6',  // Jasne tło (ikony)
        'brand-border': '#E2E8F0',    // Obramowania
        'brand-text': '#1E293B',      // Tekst podstawowy
        'brand-muted': '#64748B',     // Tekst przygaszony (opisy)
        'brand-dark-text': '#AAB4CC',
      }
    },
  },
  plugins: [],
}