import Vue from 'vue';
import App from './App.vue';

window.EventBus = new Vue();
Vue.config.productionTip = false


import dayjs from 'dayjs';
import de from 'dayjs/locale/de'

dayjs.locale('de');

Object.defineProperties(Vue.prototype, {
  $date: {
    get() {
      return dayjs
    }
  }
});


var globals = false;
globals = globals || {
  objekt:  false
};

new Vue({
  el: '#app',
  render: h => h(App),
});