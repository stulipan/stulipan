const Encore = require('@symfony/webpack-encore');

if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

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
    .addEntry('ProductEdit', './assets/vue/admin/product/ProductEdit.js')
    .addEntry('CmsImageUpload', './assets/vue/admin/_components/CmsImageUpload.js')
    .addEntry('SmartLabelEdit', './assets/vue/admin/smart-label/SmartLabelEdit.js')


    .addEntry('v-shop', './assets/vue/shop/v-shop.js')
    .addEntry('sidebar', './assets/js/sidebar.js')
    .addEntry('loading-overlay', './assets/js/loading-overlay.js')
    .addEntry('floating-input', './assets/js/floating-input.js')

    .addEntry('checkout', './assets/js/checkout.js')
    .addEntry('webshop', './assets/js/webshop.js')

    .addStyleEntry('daterangepicker', './assets/css/admin/daterangepicker.scss')

    .addStyleEntry('admin-theme', './assets/css/admin/admin-theme.scss')
    .addStyleEntry('store-theme', './assets/css/store/store-theme.scss')


    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())

    // the following line enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // .enableReactPreset()

    // uncomment to define the assets of the project
    //.addEntry('js/app', './assets/js/app.js')
    // .addStyleEntry('css/app', './assets/css/app.scss')

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // Enable Vue loader
    .enableVueLoader()

    // uncomment if you use Sass/SCSS files
    .enableSassLoader()

    // uncomment for legacy applications that require $/jQuery as a global variable
    // .autoProvidejQuery()
;

module.exports = Encore.getWebpackConfig();
