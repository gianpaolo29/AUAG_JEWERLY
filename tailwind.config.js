import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        "./resources/**/*.js",
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php', 
    ],

    theme: {
        extend: {
          fontFamily: {
            serif: ['Cormorant Garamond','serif'],
            sans:  ['Inter','ui-sans-serif','system-ui']
          },
          colors: {
            header: '#727271',
            page:   '#8F8F8F',
            card:   '#141517',
            ink:    '#111111',
          }
        }
      },

    plugins: [forms],
};