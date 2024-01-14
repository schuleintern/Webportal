/* eslint-disable */

import dayjs from 'dayjs'
import 'dayjs/locale/de'


import { createApp } from 'vue'
import axios from 'axios'
import VueAxios from 'vue-axios'

//import VueTimepicker from 'vue3-timepicker'
//import 'vue3-timepicker/dist/VueTimepicker.css'
import Datepicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css'

import App from './App.vue'

import $bus from './event.js';

const app = createApp(App)



var isoWeek = require('dayjs/plugin/isoWeek')
dayjs.extend(isoWeek)

//require('dayjs/locale/de');

app.config.globalProperties.$dayjs = dayjs
app.config.globalProperties.$bus = $bus;

app.component('Datepicker', Datepicker);
//app.component('VueTimepicker', VueTimepicker);

app.use(VueAxios, axios);
app.mount('#app')