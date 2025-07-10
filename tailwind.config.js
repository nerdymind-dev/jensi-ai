module.exports = {
  // allow PurgeCSS to analyze components
  content: [
    './src/**/*.{js,jsx,ts,tsx,vue}',
    './assets/*.{css,scss}',
    './node_modules/@variantjs/core/src/config/**/*.ts'
  ],
  theme: {
    extend: {
      'zIndex': {
        '9999': '9999'
      }
    },
    screens: {
      'sm': '600px',
      // => @media (min-width: 640px) { ... }

      'md': '783px',
      // => @media (min-width: 768px) { ... }

      'lg': '1024px',
      // => @media (min-width: 1024px) { ... }

      'xl': '1280px',
      // => @media (min-width: 1280px) { ... }

      '2xl': '1536px',
      // => @media (min-width: 1536px) { ... }
    }
  },
  variants: {
    extend: {
      opacity: ['disabled'],
      cursor: ['disabled'],
    },
  },
  // prefix: 'vwr_live_catalog-', //to prevent overlapping styles from WP
  plugins: [
    require('@tailwindcss/forms'),
    // require('@tailwindcss/typography'),
  ],
}
