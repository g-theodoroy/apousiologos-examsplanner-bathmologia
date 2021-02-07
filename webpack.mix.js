const mix = require('laravel-mix');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
    .sass('resources/sass/app.scss', 'public/css');

mix.copy('node_modules/font-awesome/css/font-awesome.min.css', 'public/css');
mix.copy('node_modules/font-awesome/fonts/*', 'public/fonts');

mix.copy('node_modules/jquery/dist/jquery.min.js', 'public/js');

mix.copy('node_modules/datatables.net/js/jquery.dataTables.min.js', 'public/js');
mix.copy('node_modules/datatables-bulma/js/dataTables.bulma.min.js', 'public/js');
mix.copy('node_modules/datatables-bulma/css/dataTables.bulma.min.css', 'public/css');
mix.copy(
    "node_modules/fullcalendar/main.min.css",
    "public/css/fullcalendar.min.css"
);
mix.copy(
    "node_modules/fullcalendar/main.min.js",
    "public/js/fullcalendar.min.js"
);
mix.copy(
    "node_modules/fullcalendar/locales/el.js",
    "public/js/fullcalendar.el.js"
);
