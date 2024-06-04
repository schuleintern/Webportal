import Vue from 'vue';
import App from './App.vue';

window.EventBus = new Vue();
Vue.config.productionTip = false

new Vue({
  el: '#app',
  render: h => h(App),
});