import('./bootstrap');

import { createApp } from 'vue/dist/vue.esm-bundler';

import App from './components/App.vue'
import Navbar from './components/Navbar.vue'
import router from './router.js'
const app = createApp({})
app.use(router);
app.component('app', App)
app.component('navbar', Navbar)
app.mount("#app")
