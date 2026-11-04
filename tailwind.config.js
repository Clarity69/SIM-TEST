import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/**
 * Palet brand diekstrak dari logo STITEK Bontang (public/images/logo-stitek.jpg):
 *   primary   #1291EC  biru langit (37.8% piksel berwarna)
 *   secondary #FE6500  oranye      (34.8%)
 *   accent    #0000FD  biru tua    (5.6%)
 * Teks putih minimal di primary-600 (4.58:1), secondary-700 (6.08:1), accent-400 (5.51:1).
 *
 * @type {import('tailwindcss').Config}
 */
export default {
    darkMode: 'class',

    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.js',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Plus Jakarta Sans', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                primary: {
                    50: '#f3fafe', 100: '#e3f2fd', 200: '#c1e2fa', 300: '#94cef6', 400: '#59b2f2',
                    500: '#1291ec', 600: '#1079c7', 700: '#0e61a2', 800: '#0d4c81', 900: '#0b3964', 950: '#0a294b',
                },
                secondary: {
                    50: '#fff7f2', 100: '#ffede0', 200: '#ffd7bd', 300: '#ffba8c', 400: '#fe934d',
                    500: '#fe6500', 600: '#d25505', 700: '#a5450b', 800: '#7e3710', 900: '#5c2a14', 950: '#3e2017',
                },
                accent: {
                    50: '#f2f2ff', 100: '#e0e0ff', 200: '#bdbdfe', 300: '#8c8cfe', 400: '#4d4dfe',
                    500: '#0000fd', 600: '#0102d5', 700: '#0304ad', 800: '#040689', 900: '#05086a', 950: '#06094f',
                },
            },
        },
    },

    plugins: [forms],
};
