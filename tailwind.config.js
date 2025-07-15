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
    // Custom scrollbar plugin
    function({ addUtilities }) {
      const scrollbarUtilities = {
        '.scrollbar-corporate': {
          'scrollbar-width': 'auto',
          'scrollbar-color': '#00529B #F2F4F7',
        },
        '.scrollbar-thin': {
          'scrollbar-width': 'thin',
          'scrollbar-color': '#0073E6 rgba(242, 244, 247, 0.3)',
        },
        '.scrollbar-hide': {
          'scrollbar-width': 'none',
          '-ms-overflow-style': 'none',
          '&::-webkit-scrollbar': {
            'display': 'none',
          },
        },
      }
      addUtilities(scrollbarUtilities)
    },
  ],
}
