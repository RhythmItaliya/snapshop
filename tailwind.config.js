module.exports = {
  content: [
    './index.php',
    './component/**/*.php',
    './assets/**/*.js',
    './assets/**/*.css'
  ],
  theme: {
    fontFamily: {
      sans: ['League Spartan', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      primary: ['League Spartan', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      body: ['League Spartan', 'ui-sans-serif', 'system-ui', 'sans-serif'],
    },
    container: {
      padding: {
        DEFAULT: '30px',
        lg: '0',
      },
    },
    screens: {
      sm: '640px',
      md: '768px',
      lg: '1024px',
      xl: '1440px',
    },
    extend: {
      colors: {
        primary: '#1f2937',      // Dark gray/blue - main brand color
        secondary: '#f59e0b',    // Amber/orange - accent color
        accent: '#3b82f6',       // Blue - call-to-action
        success: '#10b981',      // Green - success states
        danger: '#ef4444',       // Red - error states
        neutral: '#6b7280',      // Gray - neutral text
        light: '#f9fafb',        // Light gray - backgrounds
      },
      fontFamily: {
        spartan: ['League Spartan', 'ui-sans-serif', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
};
