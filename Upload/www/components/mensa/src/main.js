import Vue from 'vue'
import App from './App.vue'

window.EventBus = new Vue();

// require('moment/locale/de')
 
// Vue.use(require('vue-moment'), {
//     moment
// })

import Dayjs from 'vue-dayjs';
 
// import calendar from "dayjs/plugin/calendar";





//import 'dayjs/locale/es'
 import de from 'dayjs/locale/de'

// Dayjs.locale('de-german', de);




Vue.use(Dayjs, {
  // language set, default cn
  lang: de,
  /**
   * addon filters { key: filter name }
   * if set {} will only dayjs base filter can use.
   */
  filters: {
    //ago: 'ago'
  },
  /**
   * addon directives { key: directives name }
   * set {} to disable.
   */
  directives: {
    //countdown: 'countdown'
  }
});



// Dayjs.extend(calendar);


Vue.config.productionTip = false

var globals = false;
globals = globals || {
  objekt:  false
};


new Vue({
  render: h => h(App),
}).$mount('#app')







