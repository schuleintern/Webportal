import { createApp } from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import App from './App.vue'

import $bus from './event.js';

import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';

const app = createApp(App)

app.config.globalProperties.$bus = $bus;

app.component('VueDatePicker', VueDatePicker);

app.use(VueAxios, axios)
app.mount('#app')