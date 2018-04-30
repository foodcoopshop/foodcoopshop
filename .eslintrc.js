module.exports = {
    "env": {
        "browser": true,
        "jquery": true,
        "es6": true
    },
    "globals": {
        "foodcoopshop": true,
        "CKEDITOR": true,
        "slidebars": true
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