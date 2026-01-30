    module.exports = {
  root: true,
  env: {
    node: true,
  },
  extends: [
    'plugin:vue/vue3-recommended', // Aturan rekomendasi Vue 3
    'eslint:recommended',          // Aturan dasar ESLint
    'prettier'                     // WAJIB TERAKHIR: Mematikan aturan ESLint yang bentrok dengan Prettier
  ],
  rules: {
    // Anda bisa menambahkan custom rule di sini
    'vue/multi-word-component-names': 'off', // Contoh: matikan peringatan nama komponen satu kata
  }
}