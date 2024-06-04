import { createApp } from 'vue'

import axios from 'axios'
import VueAxios from 'vue-axios'
import App from './App.vue'
import { QuillEditor } from '@vueup/vue-quill'
import '@vueup/vue-quill/dist/vue-quill.snow.css';
//import '@vueup/vue-quill/dist/vue-quill.bubble.css'

import $bus from './event.js';

import uploader from 'vue-simple-uploader'
import 'vue-simple-uploader/dist/style.css'


//import Datepicker from '@vuepic/vue-datepicker';
//import '@vuepic/vue-datepicker/dist/main.css'

//createApp(App).mount('#app')

const app = createApp(App)
//app.component('Datepicker', Datepicker);
app.component('QuillEditor', QuillEditor)

app.config.globalProperties.$bus = $bus;

app.use(uploader)
app.use(VueAxios, axios)
app.mount('#app')