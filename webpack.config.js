const Encore = require('@symfony/webpack-encore');
// const CompressionPlugin = require("compression-webpack-plugin");

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.js file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or sub-directory deploy
    .setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */

    .addEntry('store-app', './assets/store-app.js')

    .addEntry('admin-app', './assets/admin-app.js')
    .addEntry('v-admin', './assets/vue/admin/v-admin.js')
    .addEntry('ProductEdit', './assets/vue/admin/product/ProductEdit.js')
    .addEntry('StoreImageUpload', './assets/vue/admin/_components/StoreImageUpload.js')
    .addEntry('SmartLabelEdit', './assets/vue/admin/smart-label/SmartLabelEdit.js')


    .addEntry('v-shop', './assets/vue/shop/v-shop.js')

    .addEntry('store', './assets/js/store.js')

    // .addEntry('admin/init', './assets/js/admin/init.js')

    .addStyleEntry('owlcarousel', './assets/styles/store/stulipan-theme/owlcarousel2/scss/owl.carousel.scss')
    .addStyleEntry('admin-theme', './assets/styles/admin/admin-theme.scss')
    .addStyleEntry('store-theme', './assets/styles/store/stulipan-theme/store-theme.scss')
    .addStyleEntry('store-plugins', './assets/styles/store/stulipan-theme/store-plugins.scss')

    // enables the Symfony UX Stimulus bridge (used in assets/bootstrap.js)
    .enableStimulusBridge('./assets/controllers.json')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    // .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableBuildNotifications()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    .configureBabel((config) => {
        config.plugins.push('@babel/plugin-proposal-class-properties');
    })

    // enables @babel/preset-env polyfills
    .configureBabelPresetEnv((config) => {
        config.useBuiltIns = 'usage';
        config.corejs = 3;
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // Enable Vue loader
    .enableVueLoader()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvidejQuery()
;

    // Ez volt az eredeti:
module.exports = Encore.getWebpackConfig();

//     // ezt utolag, hogy a gzip compressiont is elvegezze:
// // fetch the config, then modify it!
// const config = Encore.getWebpackConfig();
//
// // add an extension
// config.plugins.push(new CompressionPlugin());
//
// // export the final config
// module.exports = config;