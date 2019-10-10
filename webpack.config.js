var Encore = require('@symfony/webpack-encore');

Encore
    // the project directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // the public path used by the web server to access the previous directory
    .setPublicPath('/build')
    // the public path you will use in Symfony's asset() function - e.g. asset('build/some_file.js')
    .setManifestKeyPrefix('build/')

    // .addEntry('stulipan_react', './assets/js/my_react_app.js')
    // .addEntry('invoice', './assets/js/invoice-app.js')
    .addEntry('v-admin', './assets/vue/admin/v-admin.js')
    .addEntry('v-shop', './assets/vue/shop/v-shop.js')
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())

    // the following line enables hashed filenames (e.g. app.abc123.css)
    // .enableVersioning(Encore.isProduction())

    // .enableReactPreset()

    // uncomment to define the assets of the project
    //.addEntry('js/app', './assets/js/app.js')
    //.addStyleEntry('css/app', './assets/css/app.scss')

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // Enable Vue loader
    .enableVueLoader()

    // uncomment if you use Sass/SCSS files
    //.enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    //.autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
