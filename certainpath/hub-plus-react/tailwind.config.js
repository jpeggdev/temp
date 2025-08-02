/** @type {import('tailwindcss').Config} */
module.exports = {
  darkMode: ["class", "class"],
  content: ["./src/**/*.{js,ts,jsx,tsx}", "./public/index.html"],
  theme: {
    extend: {
      fontFamily: {
        base: ["Urbanist", "sans-serif"],
      },
      colors: {
        muted: {
          DEFAULT: "hsl(var(--muted) / <alpha-value>)",
          foreground: "hsl(var(--muted-foreground) / <alpha-value>)",
        },
        destructive: "#ef4444",
        primary: "#B21E34",
        secondary: "#17084A",
        accent: "#FF6710",
        fontColor: "#000000",
        white: "#fff",
        darkGray: "#818181",
        success: "#92c741",
        light: "#e5e5e5",
        dark: "#818181",
        "primary-dark": "#930023",
        "secondary-dark": "#0e0637",
        "primary-light": "#D94C65",
        "secondary-light": "#3A2C6A",
        sidebar: {
          DEFAULT: "hsl(var(--sidebar-background))",
          foreground: "hsl(var(--sidebar-foreground))",
          primary: "hsl(var(--sidebar-primary))",
          "primary-foreground": "hsl(var(--sidebar-primary-foreground))",
          accent: "hsl(var(--sidebar-accent))",
          "accent-foreground": "hsl(var(--sidebar-accent-foreground))",
          border: "hsl(var(--sidebar-border))",
          ring: "hsl(var(--sidebar-ring))",
        },
      },
      spacing: {
        mapRowHeight: "650px",
        mapRowHeightMobile: "250px",
      },
    },
  },
  plugins: [],
};
