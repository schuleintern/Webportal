import Vue from 'vue'
import App from './App.vue'

window.EventBus = new Vue();



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


Vue.config.productionTip = false

var globals = false;
globals = globals || {
  objekt:  false
};


new Vue({
  render: h => h(App),
}).$mount('#app')







