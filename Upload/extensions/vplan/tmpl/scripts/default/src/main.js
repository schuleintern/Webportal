import Vue from 'vue';
import App from './App.vue';

window.EventBus = new Vue();
Vue.config.productionTip = false

import $bus from './event.js';
Vue.prototype.$bus = $bus;


new Vue({
  el: '#app',
  render: h => h(App),
});