import { createApp } from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'
import App from './App.vue'

import $bus from './event.js';

import VueQrcode from '@chenfengyuan/vue-qrcode';

const app = createApp(App)

app.config.globalProperties.$bus = $bus;

app.component(VueQrcode.name, VueQrcode);

app.use(VueAxios, axios)
app.mount('#app')