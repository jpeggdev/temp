/* eslint-disable @typescript-eslint/no-require-imports */
const path = require("path");

module.exports = {
  webpack: {
    alias: {
      "@": path.resolve(__dirname, "src"),
    },
    configure: (webpackConfig) => {
      webpackConfig.module.rules.forEach((rule) => {
        if (
          rule.use &&
          Array.isArray(rule.use) &&
          rule.use.some(
            (u) => u.loader && u.loader.includes("source-map-loader"),
          )
        ) {
          rule.exclude = /node_modules/;
        }
      });

      if (!webpackConfig.ignoreWarnings) {
        webpackConfig.ignoreWarnings = [];
      }
      webpackConfig.ignoreWarnings.push(/Failed to parse source map/);

      return webpackConfig;
    },
  },
};
