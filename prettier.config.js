/**
 * @see https://prettier.io/docs/configuration
 * @type {import("prettier").Config}
 */
const config = {
	trailingComma: "all",
	semi: true,
	useTabs: true,

  parser: "php",
	plugins: ["@prettier/plugin-php"],
};

export default config;
