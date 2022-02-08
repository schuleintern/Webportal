import Vue from 'vue'
import App from './App.vue'

window.EventBus = new Vue();
Vue.config.productionTip = false

const moment = require('moment')
require('moment/locale/de')
 
Vue.use(require('vue-moment'), {
    moment
})


var globals = false;
globals = globals || {
  objekt:  false
};


new Vue({
  render: h => h(App),
}).$mount('#app')







