const path = require('path');
const defaultConfig = require('@wordpress/scripts/config/webpack.config');
const WooCommerceDependencyExtractionWebpackPlugin = require('@woocommerce/dependency-extraction-webpack-plugin');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');

const defaultRules = defaultConfig.module.rules.filter((rule) => {
  return String(rule.test) !== String(/\.(sc|sa)ss$/);
});

module.exports = {
  ...defaultConfig,
  entry: {
     index: path.resolve(process.cwd(), 'src', 'index.js'),
    'aruba-fatturazione-elettronica-checkout-blocks-editor': path.resolve(process.cwd(), 'src', 'CheckoutBlocks', 'index.js'),
    'aruba-fatturazione-elettronica-checkout-blocks-frontend-billing':
        path.resolve(
            process.cwd(),
            'src',
            'CheckoutBlocks',
            'billing',
            'frontend.js'
        ),
    'aruba-fatturazione-elettronica-checkout-blocks-frontend-shipping':
        path.resolve(
            process.cwd(),
            'src',
            'CheckoutBlocks',
            'shipping',
            'frontend.js'
        ),
  },
  output: {
    ...defaultConfig.output,
    path: path.resolve(process.cwd(), 'build'),
    filename: '[name].js',
  },
  module: {
    ...defaultConfig.module,
    rules: [
      ...defaultRules,
      {
        test: /\.(sc|sa)ss$/,
        exclude: /node_modules/,
        use: [
          MiniCssExtractPlugin.loader,
          { loader: 'css-loader', options: { importLoaders: 1 } },
          {
            loader: 'sass-loader',
            options: {
              sassOptions: {
                includePaths: ['src/css'],
              },
              additionalData: (content, loaderContext) => {
                const { resourcePath, rootContext } =
                    loaderContext;
                const relativePath = path.relative(
                    rootContext,
                    resourcePath
                );

                if (relativePath.startsWith('src/css/')) {
                  return content;
                }

                // Add code here to prepend to all .scss/.sass files.
                return content;
              },
            },
          },
        ],
      },
    ],
  },
  plugins: [
    ...defaultConfig.plugins.filter(
        (plugin) =>
            plugin.constructor.name !== 'DependencyExtractionWebpackPlugin'
    ),
    new WooCommerceDependencyExtractionWebpackPlugin(),
    new MiniCssExtractPlugin({
      filename: `[name].css`,
    }),
  ],
};
