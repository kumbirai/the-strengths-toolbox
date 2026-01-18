export default {
  plugins: {
    tailwindcss: {},
    autoprefixer: {},
    // Add cssnano for additional CSS minification in production
    ...(process.env.NODE_ENV === 'production' ? { cssnano: {} } : {}),
  },
}
