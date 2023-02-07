const defaultTheme = require('tailwindcss/defaultTheme');

/** @type {import('tailwindcss').Config} */
module.exports = {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Nunito', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                spred: {
                    120: "#B60022",
                    110: "#CE0027",
                    DEFAULT: "#E4002B",
                    90: "#E71A41",
                    80: "#E93355",
                    70: "#ED4D6B",
                    60: "#EF6680",
                    50: "#F28095",
                    40: "#F499AA",
                    30: "#F7B3C0",
                    20: "#FACCD5",
                    10: "#FDE6EA",
                },
                spblack: {
                    DEFAULT: "#000424",
                    90: "#1A1E3A",
                    80: "#333650",
                    70: "#4D5066",
                    60: "#66687C",
                    50: "#808292",
                    40: "#999BA7",
                    30: "#B3B4BE",
                    20: "#CCCDD3",
                    10: "#E6E6EA",
                },
                spgrey: {
                    120: "#969696",
                    110: "#A9A9A9",
                    DEFAULT: "#BBBBBB",
                    90: "#C2C2C2",
                    80: "#C9C9C9",
                    70: "#D0D0D0",
                    60: "#D6D6D6",
                    50: "#DDDDDD",
                    40: "#E4E4E4",
                    30: "#EBEBEB",
                    20: "#F1F1F1",
                    10: "#F9F9F9",
                },
            },
        },
    },

    plugins: [require('@tailwindcss/forms')],
};
