import Vue from 'vue'
import App from './App.vue'

window.EventBus = new Vue();

const moment = require('moment')
require('moment/locale/de')
 
Vue.use(require('vue-moment'), {
    moment
})

Vue.config.productionTip = false

var globals = false;
globals = globals || {
  objekt:  false
};


new Vue({
  render: h => h(App),
}).$mount('#app')







