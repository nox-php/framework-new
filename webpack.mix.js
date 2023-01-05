const mix = require('laravel-mix');

mix.disableSuccessNotifications();
mix.options({
    terser: {
        extractComments: false
    }
});
mix.setPublicPath('dist');
mix.version();

mix.postCss('resources/css/nox.css', 'dist/css', [
    require('tailwindcss')
]);
