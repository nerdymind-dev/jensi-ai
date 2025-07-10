const fs = require('fs')
const path = require('path')
const mix = require('laravel-mix')
//const webpack = require('webpack')
const StylelintPlugin = require('stylelint-webpack-plugin')
const Dotenv = require('dotenv-webpack');

require('laravel-mix-tailwind')

// Customize webpack config
const webpackConfig = {
  externals: {
    'jquery': 'jQuery',
    'vue': 'Vue'
  },
  plugins: [
    /**
     * Stylelint
     *
     * @link https://github.com/webpack-contrib/stylelint-webpack-plugin
     */
    new StylelintPlugin({
      context: './assets',
      files: '**/*.css',
      fix: true,
    }),
    new Dotenv()
  ],
  module: {
    exprContextCritical: false,
    rules: [
      // {
      //   test: /\.(sass|scss)$/,
      //   use: [
      //     'style-loader',
      //     'css-loader',
      //     'postcss-loader',
      //     {
      //       loader: 'sass-loader',
      //       options: {
      //         // Prefer `dart-sass`
      //         implementation: require('sass'),
      //       },
      //     },
      //   ],
      // }
    ],
  },
  resolve: {
    extensions: ['.js', '.json', '.vue', '.sass', '.scss', '.ts'],
    alias: {
      '~src': path.resolve(__dirname, 'src'),
      'vue$': 'vue/dist/vue.esm.js'
    }
  },
  stats: {
    children: false
  },
  devServer: {
    port: 31337   // in case your port 8080 and 31337 are taken, replace this
  }
}

/* Copy vendor libs */
mix.copy('src/shared/vendor/vue.global.js', 'public/js/vendor')
mix.copy('src/shared/vendor/vue.global.prod.js', 'public/js/vendor')

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel applications. By default, we are compiling the CSS
 | file for the application as well as bundling up all the JS files.
 |
 */
mix.setPublicPath('public/')

// Add entry points
mix.ts('src/admin/admin.ts', 'js')
  .vue({
    version: 3,
    extractStyles: true
  })
mix.ts('src/frontend/frontend.ts', 'js')
  .vue({
    version: 3,
    extractStyles: true
  })
mix.ts('src/frontview/frontview.ts', 'js')
  .vue({
    version: 3,
    extractStyles: true
  })

/*
 * Extract the CSS from the Vue components.
 * Bare minimum packages: ['core-js', 'vue-router', '@vue/devtools-api']
 */
mix.extract() // empty to extract all

// PostCSS plugins to use
const postcssPlugins = [
  require('autoprefixer'),
  require('tailwindcss'),
  require('postcss-import'),
  require('postcss-preset-env')({ stage: 1 }),
  require('postcss-pxtorem')({
    propList: ['*'],
    selectorBlackList: ['border'],
    mediaQuery: true,
  })
]

// Add CSS
mix.options({
  postCss: postcssPlugins
})
  .postCss(
    'assets/admin.css',
    'css'
  )
  .postCss(
    'assets/frontend.css',
    'css'
  )
  .postCss(
    'assets/frontview.css',
    'css'
  )

// If in production, version things and add source maps
if (mix.inProduction()) {
  mix.sourceMaps()
  mix.version()
} else {
  // For local dev specify HMR options
  let devServer = {
    host: (process.env.APP_SCHEME + "://" + process.env.APP_HOST) || 'localhost',
  }
  if (process.env.APP_SCHEME && process.env.APP_SCHEME === 'https' || false) {
    devServer.https = {
      key: fs.readFileSync(process.env.KEY_PATH || ''),
      cert: fs.readFileSync(process.env.CERT_PATH || '')
    }
  }
  webpackConfig.devServer = devServer;
}

// Add webpack config (defined above)
mix.webpackConfig(webpackConfig)

// Override watch options
mix.override((config) => {
  config.watchOptions = {
    ignored: [
      '**/node_modules/**',
      '**/public/**',
      '**/vendor/**'
    ]
  }
})
