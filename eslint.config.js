module.exports = [
    {
        languageOptions: {
            ecmaVersion: 2020,
            globals: {
                bootstrap: true,
                Chart: true,
                ChartDataLabels: true,
                ClipboardJS: true,
                Cookies: true,
                foodcoopshop: true,
                CookiesEuBanner: true,
                Jodit: true,
                math: true,
                Quagga: true,
                slidebars: true,
                Swiper: true
            },
        },
        rules: {
            indent: [
                'error',
                4
            ],
            'no-unused-vars': ['off'],
            'no-console': ['off'],
            'linebreak-style': [
                'error',
                'unix'
            ],
            quotes: [
                'error',
                'single'
            ],
            semi: [
                'error',
                'always'
            ]
        }
    }
];