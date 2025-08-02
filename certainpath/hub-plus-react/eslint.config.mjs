import globals from "globals";
import pluginJs from "@eslint/js";
import tseslint from "typescript-eslint";
import pluginReact from "eslint-plugin-react";
import eslintPluginPrettierRecommended from "eslint-plugin-prettier/recommended";
import eslintPluginReactHooks from "eslint-plugin-react-hooks";

export default [
  {
    files: ["**/*.{js,mjs,cjs,ts,jsx,tsx}"],
  },
  {
    languageOptions: {
      globals: {
        ...globals.browser,
        ...globals.node,
      },
    },
  },
  {
    plugins: {
      reactHooks: eslintPluginReactHooks,
    },
  },
  {
    ignores: [
      "src/components/catalyst-ui-kit/**",
      "src/components/Dashtail/**",
    ],
  },
  pluginJs.configs.recommended,
  ...tseslint.configs.recommended,
  pluginReact.configs.flat.recommended,
  eslintPluginPrettierRecommended,
  {
    rules: {
      "react/react-in-jsx-scope": "off",
      "react/prop-types": "off",
      "react/jsx-sort-props": "error",
      "react/no-unused-state": "error",
      "react/jsx-pascal-case": "warn",
      //quotes: ["error", "single"],
    },
    settings: {
      react: {
        version: "detect",
      },
    },
  },
];
