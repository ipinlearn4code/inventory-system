/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        'corporate-blue': '#00529B',
        'action-orange': '#F37021',
        'clean-white': '#FFFFFF',
        'neutral-background': '#F2F4F7',
        'deep-gray-text': '#344054',
        'bright-blue': '#0073E6',
      },
      fontFamily: {
        'sans': ['Poppins', 'ui-sans-serif', 'system-ui'],
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
}
