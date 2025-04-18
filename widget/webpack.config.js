const path = require('path')
const VueLoaderPlugin = require('vue-loader/lib/plugin'); // плагин для загрузки кода Vue
const AmdWebpackPlugin = require('amd-webpack-plugin');
const ZipFilesPlugin = require('webpack-zip-files-plugin');
// const UglifyJSPlugin = require('uglifyjs-webpack-plugin');


const ENV = 'dev';

module.exports = {
    entry: ['./src/app.js'],
    mode: 'production',
    devtool: 'source-map',
    output: {
        path: path.resolve(__dirname, './widget'),
        publicPath: '/',
        filename: 'app.js',
        libraryTarget: 'amd'
    },
    module: {
        rules: [
            {
                test: /\.js?$/,
                loader: 'babel-loader',
                options: {
                    presets: ['@babel/preset-env'],
                    plugins: [
                        ["@babel/plugin-proposal-decorators", { "legacy": true }],
                        ["@babel/plugin-proposal-class-properties", { "loose" : true }]
                    ]
                }
            },
            {
                test: /\.vue$/,
                loader: 'vue-loader',
                exclude: file => (
                    /node_modules/.test(file) &&
                    !/\.vue\.js/.test(file)
                )
            }, {
                test: /\.css$/,
                use: [
                    'vue-style-loader',
                    'css-loader'
                ]
            }
        ]
    },
    resolve: {
        alias: {
            '@': path.resolve(__dirname, './src/components'),
        }
    },
    plugins: [
        new AmdWebpackPlugin(),
        new VueLoaderPlugin(),
        new ZipFilesPlugin({
            entries: [
                {
                    src: path.join(__dirname, './widget/'),
                    dist: '/',
                },
            ],
            output: path.join(__dirname, './widget'),
            format: 'zip',
        }),
    ],
    /*
    optimization: {
        minimizer: [
            new UglifyJSPlugin({
                uglifyOptions: {
                    compress: {
                        drop_console: true,
                    },
                    output: {
                        comments: false,
                    },
                },
            }),
        ],
    },
    */
}