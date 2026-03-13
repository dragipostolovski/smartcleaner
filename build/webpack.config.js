const path = require('path');
const MiniCssExtractPlugin = require('mini-css-extract-plugin');
const TerserPlugin = require('terser-webpack-plugin');
const sassEmbedded = require('sass-embedded');

const externals = {
  '@wordpress/blocks': 'wp.blocks',
  '@wordpress/i18n': 'wp.i18n',
  '@wordpress/element': 'wp.element',
  '@wordpress/components': 'wp.components',
  '@wordpress/data': 'wp.data',
  '@wordpress/compose': 'wp.compose',
  '@wordpress/plugins': 'wp.plugins',
  '@wordpress/edit-post': 'wp.editPost',
};

module.exports = {
  entry: {
    app: path.resolve(__dirname, 'src', 'app.js'),
    editor: path.resolve(__dirname, 'src', 'editor.js'),
  },
  output: {
    filename: '[name].js',
    path: path.resolve(__dirname, '..', 'dist'),
    clean: false,
  },
  module: {
    rules: [
      {
        test: /\.m?js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: [
              [
                '@babel/preset-env',
                {
                  targets: '> 0.5%, not dead',
                  modules: false,
                  bugfixes: true,
                },
              ],
              '@babel/preset-react',
            ],
          },
        },
      },
      {
        test: /\.s?css$/,
        use: [
          MiniCssExtractPlugin.loader,
          {
            loader: 'css-loader',
            options: { sourceMap: true },
          },
          {
            loader: 'sass-loader',
            options: {
              sourceMap: true,
              implementation: sassEmbedded,
              sassOptions: {
                silenceDeprecations: ['legacy-js-api'],
              },
            },
          },
        ],
      },
    ],
  },
  plugins: [
    new MiniCssExtractPlugin({ filename: '[name].css' }),
  ],
  resolve: {
    extensions: ['.js'],
  },
  externals,
  optimization: {
    minimize: true,
    minimizer: [new TerserPlugin({ extractComments: false })],
  },
  devtool: 'source-map',
};
