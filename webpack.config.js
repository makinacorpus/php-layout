const CleanWebpackPlugin = require('clean-webpack-plugin');
const ExtractTextPlugin = require("extract-text-webpack-plugin");
const MinifyPlugin = require("babel-minify-webpack-plugin");
const path = require('path');
const webpack = require('webpack');

const DIST_DIR = path.resolve(__dirname, 'src/Resources/public/dist');
const extractLess = new ExtractTextPlugin({
  filename: "edit.css",
});

module.exports = {
  entry: ['core-js/modules/es6.promise', './src/Resources/front/index.js'],
  //devtool: 'source-map',
  plugins: [
    new CleanWebpackPlugin([
      DIST_DIR
    ]),
    //new MinifyPlugin(),
    extractLess
  ],
  module: {
    rules: [{
      test: /\.ts$/,
      exclude: /node_modules/,
      use: [{
        loader: "babel-loader"
      }, {
        loader: "ts-loader"
      }],
    },{
      test: /\.js$/,
      exclude: /node_modules/,
      use: [{
        loader: "babel-loader"
      }]
    },{
      test: /\.less$/,
      use: extractLess.extract({
        fallback: "style-loader",
        use: [{
          loader: "css-loader"
        },{
          loader: "less-loader"
        }]
      })
    }]
  },
  resolve: {
    extensions: [".tsx", ".ts", ".js", ".less"]
  },
  output: {
    filename: 'edit.js',
    path: DIST_DIR
  }
};
