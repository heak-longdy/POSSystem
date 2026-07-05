const mix = require("laravel-mix");

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

// mix.ts("resources/admin/ts/app.js", "public/admin-public/js")
//     .ts("resources/admin/ts/body.js", "public/admin-public/js")
//     .ts("resources/admin/ts/defer.js", "public/admin-public/js")
//     .sass("resources/admin/sass/app.scss", "public/admin-public/css")
//     .sass("resources/admin/sass/carHistory/invoice.scss", "public/admin-public/css/history/invoice.css")
//     .js("resources/admin/ts/select_geo.js", "public/admin-public/js");

// mix.js('src/app.js', 'dist').vue();
mix.copyDirectory("vendor/tinymce/tinymce", "public/admin-public/js/tinymce");
mix
.ts("resources/admin/ts/app.js", "public/admin-public/js")
.ts("resources/admin/ts/body.js", "public/admin-public/js")
.sass(
    "resources/admin/sass/app.scss",
    "public/admin-public/css"
);


/*
 |--------------------------------------------------------------------------
 | Mix Asset Frontend
 |--------------------------------------------------------------------------
*/

mix
  .ts("resources/website/ts/app.js", "public/website/js")
  .ts("resources/website/ts/body.js", "public/website/js")
  .sass("resources/website/sass/app.scss", "public/website/css")
  .sass("resources/website/sass/verifyDoc.scss", "public/website/verifyDoc.css");

// mix.browserSync("127.0.0.1:8000");
mix.browserSync({
  proxy: '127.0.0.1:8000', // Laravel's local development server
  files: [
      'resources/website/views/**/*.php', // Blade templates
      'resources/admin/views/**/*.php', // Blade templates
      'app/**/*.php',            // PHP files in app folder
      'routes/**/*.php',         // Route files
      'public/**/*.(css|js)',    // Compiled CSS and JS
      'resources/js/**/*.js',    // Source JS files (optional)
      'resources/sass/**/*.scss' // Source SCSS files (optional)
  ],
  open: false, // Prevent browser auto-opening (optional)
});

mix.sass("resources/admin/sass/testing.scss", "public/website/form11.css");

mix.sass("resources/website/sass/carousel.scss", "public/website/carousel.css");
