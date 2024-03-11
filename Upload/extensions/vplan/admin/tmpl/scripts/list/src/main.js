import { createApp } from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'

import uploader from 'vue-simple-uploader'
import 'vue-simple-uploader/dist/style.css'

import App from './App.vue'

import $bus from './event.js';

const app = createApp(App)

app.config.globalProperties.$bus = $bus;

app.use(uploader)
app.use(VueAxios, axios)

app.mount('#app')