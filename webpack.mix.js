const mix = require('laravel-mix');
const webpack = require('webpack');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .sass('resources/sass/app.scss', 'public/css');

mix.version().extract().sourceMaps();

mix.webpackConfig({
	module: {
		rules: [
			{
				test: /\.tsx?$/,
				loader: "ts-loader",
				options: { appendTsSuffixTo: [/\.vue$/] },
				exclude: /node_modules/,
			},
		],
	},
	resolve: {
		extensions: ["*", ".js", ".jsx", ".vue", ".ts", ".tsx"],
		alias: {
			'@': __dirname + '/resources'
		}
	},
});
