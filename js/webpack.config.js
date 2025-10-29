import flarumConfig from 'flarum-webpack-config';
import { merge } from 'webpack-merge';

export default merge(flarumConfig(), {
  module: {
    rules: [
      {
        test: /\.tsx?$/,
        use: {
          loader: 'ts-loader',
          options: {
            transpileOnly: true,
            compilerOptions: {
              jsx: 'react',
              jsxFactory: 'm',
              jsxFragmentFactory: 'm.fragment',
            },
          },
        },
        exclude: /node_modules/,
      },
    ],
  },
  resolve: {
    extensions: ['.ts', '.tsx', '.js'],
  },
});
