// FE-DOC: Script frontend untuk resources/js/bootstrap.js. Komentar dipakai untuk menandai file ini sebagai bagian dari interaksi UI dan helper JavaScript project.

// FE-DOC: Import dipakai untuk menarik dependency frontend yang dibutuhkan file ini.
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
