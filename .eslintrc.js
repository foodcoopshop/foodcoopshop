module.exports = {
    "env": {
        "browser": true,
        "jquery": true,
        "es6": true
    },
    "ignorePatterns": ["webroot/js/elFinder/"],
    "globals": {
        "Chart": true,
        "CKEDITOR": true,
        "ChartDataLabels": true,
        "ClipboardJS": true,
        "Cookies": true,
        "foodcoopshop": true,
        "GdprConsent": true,
        "Quagga": true,
        "slidebars": true,
        "Swiper": true
    },
    "extends": "eslint:recommended",
    "rules": {
        "indent": [
            "error",
            4
        ],
        "no-unused-vars": ["off"],
        "no-console": ["off"],
        "linebreak-style": [
            "error",
            "unix"
        ],
        "quotes": [
            "error",
            "single"
        ],
        "semi": [
            "error",
            "always"
        ]
    }
};