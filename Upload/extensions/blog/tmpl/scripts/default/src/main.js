import { createApp } from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import App from './App.vue'

import $bus from './event.js';

import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

const app = createApp(App)
app.component('Datepicker', Datepicker);

app.config.globalProperties.$bus = $bus;

app.use(VueAxios, axios)
app.mount('#app')