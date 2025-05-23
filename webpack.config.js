// webpack.config.js
var Encore = require('@symfony/webpack-encore');
const CopyWebpackPlugin = require('copy-webpack-plugin');
Encore
    // directory where all compiled assets will be stored
    .setOutputPath('public/build')

    // what's the public path to this directory (relative to your project's document root dir)
    .setPublicPath('/build')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()
    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    //.enableSingleRuntimeChunk()
    .disableSingleRuntimeChunk()
    .addEntry('landing', './assets/js/landing.js')
    .addEntry('app', './assets/js/app.js')
    .addEntry('mailgun', './assets/js/mailgun.js')
    .addEntry('frontend', './assets/js/frontend.js')
    .addEntry('startpage', './assets/js/start.js')
    .addEntry('schulen', './assets/js/schulen.js')
    .addEntry('angemeldeteKinder', './assets/js/angemeldeteKinder.js')
    .addRule({
        test: /trumbowgy\/dist\/ui\/[^/]+\.svg$/,
        use: [ {
            loader: 'raw-loader'
        } ]
    })
    // empty the outputPath dir before each build
    .cleanupOutputBeforeBuild()
    .addPlugin(
        new CopyWebpackPlugin(
            [
                {
                    from: './node_modules/mdbootstrap-pro/js/mdb.min.js',
                    to: 'js/mdb.min.js'
                },
                {
                    from: './node_modules/mdbootstrap-pro/js/mdb.min.js.map',
                    to: 'js/mdb.min.js.map'
                },
                {
                    from: './node_modules/mdbootstrap-pro/js/jquery.min.js',
                    to: 'js/jquery.min.js'
                },
                {
                    from: './node_modules/mdbootstrap-pro/js/bootstrap.min.js',
                    to: 'js/bootstrap.min.js'
                },
                {
                    from: './node_modules/mdbootstrap-pro/js/popper.min.js',
                    to: 'js/popper.min.js'
                }


            ]
        )
    )

    // will output as web/build/app.js
    //.addEntry('app', ['./src/public/app.js','./src/public/main.scss'])

   // .createSharedEntry('vendor', './src/public/app.js')

  
    // allow sass/scss files to be processed
    .enableSassLoader()
    // allow legacy applications to use $/jQuery as a global variable
    .autoProvidejQuery()
    .enableSourceMaps(!Encore.isProduction())
    .enableVersioning(Encore.isProduction())
    // create hashed filenames (e.g. app.abc123.css)
    //.enableVersioning()

;

// export the final configuration
module.exports = Encore.getWebpackConfig();
