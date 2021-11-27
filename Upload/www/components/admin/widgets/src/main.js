import Vue from 'vue';
import App from './App.vue';

window.EventBus = new Vue();

new Vue({
  el: '#app',
  render: h => h(App),
});