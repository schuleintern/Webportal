webpackHotUpdate("app",{

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/App.vue?vue&type=script&lang=js&":
/*!*************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js??ref--12-0!./node_modules/babel-loader/lib!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/App.vue?vue&type=script&lang=js& ***!
  \*************************************************************************************************************************************************************************************************************************************/
/*! exports provided: default */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//console.log('globals',globals);\n// import Calendar from './components/Calendar.vue'\n// import Form from './components/Form.vue'\n// import Item from './components/Item.vue'\nvar axios = __webpack_require__(/*! axios */ \"./node_modules/axios/index.js\").default; //import Dayjs from 'vue-dayjs';\n\n\n/* harmony default export */ __webpack_exports__[\"default\"] = ({\n  name: 'app',\n  components: {// Calendar,\n    // Form,\n    // Item\n  },\n  data: function data() {\n    return {\n      loading: false,\n      error: false,\n      dates: [],\n      acl: globals.acl\n    };\n  },\n  created: function created() {\n    var _this = this;\n\n    var that = this;\n    EventBus.$on('calendar--changedDate', function (data) {\n      _this.showFirstDayWeek = data.von;\n      _this.showLastDayWeek = data.bis;\n      that.ajaxGet('index.php?page=mensaSpeiseplan&action=getWeek', {\n        von: _this.showFirstDayWeek,\n        bis: _this.showLastDayWeek\n      }, function (response, that) {\n        if (response.data && response.data.error != true) {\n          that.dates = response.data;\n        } else {\n          that.dates = [];\n        }\n      });\n    }, function () {\n      console.log('error');\n    });\n    EventBus.$on('form--submit', function (data) {\n      // console.log(data);\n      if (!data.form.date || !data.form.title) {\n        return false;\n      }\n\n      that.ajaxPost('rest.php/SetMensaMeal', {\n        data: data.form\n      }, {}, function (response, that) {\n        that.error = false;\n\n        if (response.data.error == true && response.data.msg) {\n          that.error = response.data.msg;\n        } else if (response.data.done == true) {\n          EventBus.$emit('calender--reload', {});\n          EventBus.$emit('form--close', {});\n        }\n      });\n    });\n    EventBus.$on('item--delete', function (data) {\n      if (!data.item || !data.item.id) {\n        return false;\n      }\n\n      console.log(data.item);\n      that.ajaxPost('rest.php/SetMensaMeal/delete', {\n        data: data.item\n      }, {}, function (response, that) {\n        that.error = false;\n\n        if (response.data.error == true && response.data.msg) {\n          that.error = response.data.msg;\n        } else if (response.data.done == true) {\n          EventBus.$emit('calender--reload', {}); //EventBus.$emit('form--close', {});\n        }\n      });\n    });\n    EventBus.$on('item--order', function (data) {\n      if (!data.item || !data.item.id) {\n        return false;\n      } //console.log(data.item);\n\n\n      that.ajaxPost('rest.php/SetMensaOrder', {\n        data: data.item\n      }, {}, function (response, that) {\n        that.error = false;\n\n        if (response.data.error == true && response.data.msg) {\n          that.error = response.data.msg;\n        } else if (response.data.done == true) {\n          data.item.booked = response.data.booked; //EventBus.$emit('calender--reload', {});\n          //EventBus.$emit('form--close', {});\n        }\n      });\n    });\n  },\n  methods: {\n    ajaxGet: function ajaxGet(url, params, callback, error, allways) {\n      this.loading = true;\n      var that = this;\n      axios.get(url, {\n        params: params\n      }).then(function (response) {\n        // console.log(response.data);\n        if (callback && typeof callback === 'function') {\n          callback(response, that);\n        }\n      }).catch(function (resError) {\n        //console.log(error);\n        if (resError && typeof error === 'function') {\n          error(resError);\n        }\n      }).finally(function () {\n        // always executed\n        if (allways && typeof allways === 'function') {\n          allways();\n        }\n\n        that.loading = false;\n      });\n    },\n    ajaxPost: function ajaxPost(url, data, params, callback, error, allways) {\n      this.loading = true;\n      var that = this;\n      axios.post(url, data, {\n        params: params\n      }).then(function (response) {\n        // console.log(response.data);\n        if (callback && typeof callback === 'function') {\n          callback(response, that);\n        }\n      }).catch(function (resError) {\n        //console.log(error);\n        if (resError && typeof error === 'function') {\n          error(resError);\n        }\n      }).finally(function () {\n        // always executed\n        if (allways && typeof allways === 'function') {\n          allways();\n        }\n\n        that.loading = false;\n      });\n    }\n  }\n});//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9ub2RlX21vZHVsZXMvY2FjaGUtbG9hZGVyL2Rpc3QvY2pzLmpzPyEuL25vZGVfbW9kdWxlcy9iYWJlbC1sb2FkZXIvbGliL2luZGV4LmpzIS4vbm9kZV9tb2R1bGVzL2NhY2hlLWxvYWRlci9kaXN0L2Nqcy5qcz8hLi9ub2RlX21vZHVsZXMvdnVlLWxvYWRlci9saWIvaW5kZXguanM/IS4vc3JjL0FwcC52dWU/dnVlJnR5cGU9c2NyaXB0Jmxhbmc9anMmLmpzIiwic291cmNlcyI6WyJ3ZWJwYWNrOi8vL0FwcC52dWU/MjM0ZSJdLCJzb3VyY2VzQ29udGVudCI6WyI8dGVtcGxhdGU+XG4gIDxkaXYgaWQ9XCJhcHBcIj5cblxuICAgIDxkaXYgdi1zaG93PVwiZXJyb3JcIiBjbGFzcz1cImZvcm0tbW9kYWwtZXJyb3JcIj5cbiAgICAgIDxiPkZvbGdlbmRlIEZlaGxlciBzaW5kIGF1ZmdldHJldGVuOjwvYj5cbiAgICAgIDxkaXY+e3tlcnJvcn19PC9kaXY+XG4gICAgPC9kaXY+XG5cbiAgICA8ZGl2IHYtaWY9XCJsb2FkaW5nID09IHRydWVcIiBjbGFzcz1cIm92ZXJsYXlcIj5cbiAgICAgIDxpIGNsYXNzPVwiZmEgZmFzIGZhLXN5bmMtYWx0IGZhLXNwaW5cIj48L2k+XG4gICAgPC9kaXY+XG5cbiAgICA8ZGl2IGlkPVwibWFpbi1ib3hcIiBjbGFzcz1cIlwiPlxuICAgICAgPENhbGVuZGFyIHYtYmluZDpkYXRlcz1cImRhdGVzXCIgdi1iaW5kOmFjbD1cImFjbFwiPjwvQ2FsZW5kYXI+XG4gICAgPC9kaXY+XG5cblxuICA8L2Rpdj5cbjwvdGVtcGxhdGU+XG5cbjxzY3JpcHQ+XG4vL2NvbnNvbGUubG9nKCdnbG9iYWxzJyxnbG9iYWxzKTtcblxuLy8gaW1wb3J0IENhbGVuZGFyIGZyb20gJy4vY29tcG9uZW50cy9DYWxlbmRhci52dWUnXG4vLyBpbXBvcnQgRm9ybSBmcm9tICcuL2NvbXBvbmVudHMvRm9ybS52dWUnXG4vLyBpbXBvcnQgSXRlbSBmcm9tICcuL2NvbXBvbmVudHMvSXRlbS52dWUnXG5cblxuY29uc3QgYXhpb3MgPSByZXF1aXJlKCdheGlvcycpLmRlZmF1bHQ7XG5cbi8vaW1wb3J0IERheWpzIGZyb20gJ3Z1ZS1kYXlqcyc7XG5cblxuZXhwb3J0IGRlZmF1bHQge1xuICBuYW1lOiAnYXBwJyxcbiAgY29tcG9uZW50czoge1xuICAgIC8vIENhbGVuZGFyLFxuICAgIC8vIEZvcm0sXG4gICAgLy8gSXRlbVxuICB9LFxuICBkYXRhOiBmdW5jdGlvbiAoKSB7XG4gICAgcmV0dXJuIHtcblxuICAgICAgbG9hZGluZzogZmFsc2UsXG4gICAgICBlcnJvcjogZmFsc2UsXG4gICAgICBkYXRlczogW10sXG4gICAgICBhY2w6IGdsb2JhbHMuYWNsXG5cbiAgICB9XG4gIH0sXG4gIGNyZWF0ZWQ6IGZ1bmN0aW9uICgpIHtcblxuXG4gICAgdmFyIHRoYXQgPSB0aGlzO1xuXG4gICAgRXZlbnRCdXMuJG9uKCdjYWxlbmRhci0tY2hhbmdlZERhdGUnLCBkYXRhID0+IHtcblxuICAgICAgdGhpcy5zaG93Rmlyc3REYXlXZWVrID0gZGF0YS52b247XG4gICAgICB0aGlzLnNob3dMYXN0RGF5V2VlayA9IGRhdGEuYmlzO1xuXG4gICAgICB0aGF0LmFqYXhHZXQoXG4gICAgICAgICdpbmRleC5waHA/cGFnZT1tZW5zYVNwZWlzZXBsYW4mYWN0aW9uPWdldFdlZWsnLFxuICAgICAgICB7XG4gICAgICAgICAgdm9uOiB0aGlzLnNob3dGaXJzdERheVdlZWssXG4gICAgICAgICAgYmlzOiB0aGlzLnNob3dMYXN0RGF5V2Vla1xuICAgICAgICB9LFxuICAgICAgICBmdW5jdGlvbiAocmVzcG9uc2UsIHRoYXQpIHtcbiAgICAgICAgICBpZiAocmVzcG9uc2UuZGF0YSAmJiByZXNwb25zZS5kYXRhLmVycm9yICE9IHRydWUpIHtcbiAgICAgICAgICAgIHRoYXQuZGF0ZXMgPSByZXNwb25zZS5kYXRhO1xuICAgICAgICAgIH0gZWxzZSB7XG4gICAgICAgICAgICB0aGF0LmRhdGVzID0gW107XG4gICAgICAgICAgfVxuICAgICAgICAgIFxuICAgICAgICB9XG4gICAgICApO1xuICAgIH0sIGZ1bmN0aW9uICgpIHtcbiAgICAgIGNvbnNvbGUubG9nKCdlcnJvcicpO1xuICAgIH0pO1xuXG4gICAgRXZlbnRCdXMuJG9uKCdmb3JtLS1zdWJtaXQnLCBkYXRhID0+IHtcbiAgICAgIFxuICAgICAgLy8gY29uc29sZS5sb2coZGF0YSk7XG5cbiAgICAgIGlmICghZGF0YS5mb3JtLmRhdGUgfHwgIWRhdGEuZm9ybS50aXRsZSkge1xuICAgICAgICByZXR1cm4gZmFsc2U7XG4gICAgICB9XG5cblxuICAgICAgdGhhdC5hamF4UG9zdChcbiAgICAgICAgJ3Jlc3QucGhwL1NldE1lbnNhTWVhbCcsXG4gICAgICAgIHsgZGF0YTogZGF0YS5mb3JtIH0sXG4gICAgICAgIHsgfSxcbiAgICAgICAgZnVuY3Rpb24gKHJlc3BvbnNlLCB0aGF0KSB7XG4gICAgICAgICAgXG4gICAgICAgICAgdGhhdC5lcnJvciA9IGZhbHNlO1xuXG4gICAgICAgICAgaWYgKHJlc3BvbnNlLmRhdGEuZXJyb3IgPT0gdHJ1ZSAmJiByZXNwb25zZS5kYXRhLm1zZykge1xuICAgICAgICAgICAgdGhhdC5lcnJvciA9IHJlc3BvbnNlLmRhdGEubXNnO1xuICAgICAgICAgIH0gZWxzZSBpZiAocmVzcG9uc2UuZGF0YS5kb25lID09IHRydWUpIHtcbiAgICAgICAgICAgIEV2ZW50QnVzLiRlbWl0KCdjYWxlbmRlci0tcmVsb2FkJywge30pO1xuICAgICAgICAgICAgRXZlbnRCdXMuJGVtaXQoJ2Zvcm0tLWNsb3NlJywge30pO1xuICAgICAgICAgIH0gXG4gICAgICAgIH1cbiAgICAgICk7XG5cbiAgICB9KTtcblxuXG5cbiAgICBFdmVudEJ1cy4kb24oJ2l0ZW0tLWRlbGV0ZScsIGRhdGEgPT4ge1xuICAgICAgXG4gICAgICBpZiAoIWRhdGEuaXRlbSB8fCAhZGF0YS5pdGVtLmlkKSB7XG4gICAgICAgIHJldHVybiBmYWxzZTtcbiAgICAgIH1cblxuICAgICAgY29uc29sZS5sb2coZGF0YS5pdGVtKTtcblxuICAgICAgdGhhdC5hamF4UG9zdChcbiAgICAgICAgJ3Jlc3QucGhwL1NldE1lbnNhTWVhbC9kZWxldGUnLFxuICAgICAgICB7IGRhdGE6IGRhdGEuaXRlbSB9LFxuICAgICAgICB7IH0sXG4gICAgICAgIGZ1bmN0aW9uIChyZXNwb25zZSwgdGhhdCkge1xuICAgICAgICAgIFxuICAgICAgICAgIHRoYXQuZXJyb3IgPSBmYWxzZTtcblxuICAgICAgICAgIGlmIChyZXNwb25zZS5kYXRhLmVycm9yID09IHRydWUgJiYgcmVzcG9uc2UuZGF0YS5tc2cpIHtcbiAgICAgICAgICAgIHRoYXQuZXJyb3IgPSByZXNwb25zZS5kYXRhLm1zZztcbiAgICAgICAgICB9IGVsc2UgaWYgKHJlc3BvbnNlLmRhdGEuZG9uZSA9PSB0cnVlKSB7XG4gICAgICAgICAgICBFdmVudEJ1cy4kZW1pdCgnY2FsZW5kZXItLXJlbG9hZCcsIHt9KTtcbiAgICAgICAgICAgIC8vRXZlbnRCdXMuJGVtaXQoJ2Zvcm0tLWNsb3NlJywge30pO1xuICAgICAgICAgIH0gXG4gICAgICAgIH1cbiAgICAgICk7XG5cblxuICAgIH0pO1xuXG5cbiAgICBFdmVudEJ1cy4kb24oJ2l0ZW0tLW9yZGVyJywgZGF0YSA9PiB7XG4gICAgICBcbiAgICAgIGlmICghZGF0YS5pdGVtIHx8ICFkYXRhLml0ZW0uaWQpIHtcbiAgICAgICAgcmV0dXJuIGZhbHNlO1xuICAgICAgfVxuXG4gICAgICAvL2NvbnNvbGUubG9nKGRhdGEuaXRlbSk7XG5cbiAgICAgIHRoYXQuYWpheFBvc3QoXG4gICAgICAgICdyZXN0LnBocC9TZXRNZW5zYU9yZGVyJyxcbiAgICAgICAgeyBkYXRhOiBkYXRhLml0ZW0gfSxcbiAgICAgICAgeyB9LFxuICAgICAgICBmdW5jdGlvbiAocmVzcG9uc2UsIHRoYXQpIHtcbiAgICAgICAgICBcbiAgICAgICAgICB0aGF0LmVycm9yID0gZmFsc2U7XG5cbiAgICAgICAgICBpZiAocmVzcG9uc2UuZGF0YS5lcnJvciA9PSB0cnVlICYmIHJlc3BvbnNlLmRhdGEubXNnKSB7XG4gICAgICAgICAgICB0aGF0LmVycm9yID0gcmVzcG9uc2UuZGF0YS5tc2c7XG4gICAgICAgICAgfSBlbHNlIGlmIChyZXNwb25zZS5kYXRhLmRvbmUgPT0gdHJ1ZSkge1xuXG4gICAgICAgICAgICBkYXRhLml0ZW0uYm9va2VkID0gcmVzcG9uc2UuZGF0YS5ib29rZWQ7XG4gICAgICAgICAgICAvL0V2ZW50QnVzLiRlbWl0KCdjYWxlbmRlci0tcmVsb2FkJywge30pO1xuICAgICAgICAgICAgLy9FdmVudEJ1cy4kZW1pdCgnZm9ybS0tY2xvc2UnLCB7fSk7XG4gICAgICAgICAgfSBcbiAgICAgICAgfVxuICAgICAgKTtcblxuXG4gICAgfSk7XG5cblxuICB9LFxuICBtZXRob2RzOiB7XG5cbiAgICBhamF4R2V0OiBmdW5jdGlvbiAodXJsLCBwYXJhbXMsIGNhbGxiYWNrLCBlcnJvciwgYWxsd2F5cykge1xuICAgICAgdGhpcy5sb2FkaW5nID0gdHJ1ZTtcbiAgICAgIHZhciB0aGF0ID0gdGhpcztcbiAgICAgIGF4aW9zLmdldCh1cmwsIHtcbiAgICAgICAgcGFyYW1zOiBwYXJhbXNcbiAgICAgIH0pXG4gICAgICAudGhlbihmdW5jdGlvbiAocmVzcG9uc2UpIHtcbiAgICAgICAgLy8gY29uc29sZS5sb2cocmVzcG9uc2UuZGF0YSk7XG4gICAgICAgIGlmIChjYWxsYmFjayAmJiB0eXBlb2YgY2FsbGJhY2sgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICBjYWxsYmFjayhyZXNwb25zZSwgdGhhdCk7XG4gICAgICAgIH1cbiAgICAgIH0pXG4gICAgICAuY2F0Y2goZnVuY3Rpb24gKHJlc0Vycm9yKSB7XG4gICAgICAgIC8vY29uc29sZS5sb2coZXJyb3IpO1xuICAgICAgICBpZiAocmVzRXJyb3IgJiYgdHlwZW9mIGVycm9yID09PSAnZnVuY3Rpb24nKSB7XG4gICAgICAgICAgZXJyb3IocmVzRXJyb3IpO1xuICAgICAgICB9XG4gICAgICB9KVxuICAgICAgLmZpbmFsbHkoZnVuY3Rpb24gKCkge1xuICAgICAgICAvLyBhbHdheXMgZXhlY3V0ZWRcbiAgICAgICAgaWYgKGFsbHdheXMgJiYgdHlwZW9mIGFsbHdheXMgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICBhbGx3YXlzKCk7XG4gICAgICAgIH1cbiAgICAgICAgdGhhdC5sb2FkaW5nID0gZmFsc2U7XG4gICAgICB9KTsgIFxuICAgICAgXG4gICAgfSxcbiAgICBhamF4UG9zdDogZnVuY3Rpb24gKHVybCwgZGF0YSwgcGFyYW1zLCBjYWxsYmFjaywgZXJyb3IsIGFsbHdheXMpIHtcbiAgICAgIHRoaXMubG9hZGluZyA9IHRydWU7XG4gICAgICB2YXIgdGhhdCA9IHRoaXM7XG4gICAgICBheGlvcy5wb3N0KHVybCwgZGF0YSwge1xuICAgICAgICBwYXJhbXM6IHBhcmFtc1xuICAgICAgfSlcbiAgICAgIC50aGVuKGZ1bmN0aW9uIChyZXNwb25zZSkge1xuICAgICAgICAvLyBjb25zb2xlLmxvZyhyZXNwb25zZS5kYXRhKTtcbiAgICAgICAgaWYgKGNhbGxiYWNrICYmIHR5cGVvZiBjYWxsYmFjayA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICAgIGNhbGxiYWNrKHJlc3BvbnNlLCB0aGF0KTtcbiAgICAgICAgfVxuICAgICAgfSlcbiAgICAgIC5jYXRjaChmdW5jdGlvbiAocmVzRXJyb3IpIHtcbiAgICAgICAgLy9jb25zb2xlLmxvZyhlcnJvcik7XG4gICAgICAgIGlmIChyZXNFcnJvciAmJiB0eXBlb2YgZXJyb3IgPT09ICdmdW5jdGlvbicpIHtcbiAgICAgICAgICBlcnJvcihyZXNFcnJvcik7XG4gICAgICAgIH1cbiAgICAgIH0pXG4gICAgICAuZmluYWxseShmdW5jdGlvbiAoKSB7XG4gICAgICAgIC8vIGFsd2F5cyBleGVjdXRlZFxuICAgICAgICBpZiAoYWxsd2F5cyAmJiB0eXBlb2YgYWxsd2F5cyA9PT0gJ2Z1bmN0aW9uJykge1xuICAgICAgICAgIGFsbHdheXMoKTtcbiAgICAgICAgfVxuICAgICAgICB0aGF0LmxvYWRpbmcgPSBmYWxzZTtcbiAgICAgIH0pOyAgXG4gICAgICBcbiAgICB9XG5cbiAgfVxufVxuPC9zY3JpcHQ+XG5cbjxzdHlsZT5cbjwvc3R5bGU+XG4iXSwibWFwcGluZ3MiOiI7Ozs7Ozs7Ozs7Ozs7Ozs7Ozs7OztBQXFCQTtBQUVBO0FBQ0E7QUFDQTtBQUdBO0FBQ0E7QUFDQTtBQUdBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFIQTtBQUtBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUxBO0FBUUE7QUFDQTtBQUFBO0FBQ0E7QUFFQTtBQUVBO0FBRUE7QUFDQTtBQUVBO0FBR0E7QUFDQTtBQUZBO0FBS0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBRUE7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUFBO0FBSUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBR0E7QUFJQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBQUE7QUFJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUNBO0FBSUE7QUFHQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFFQTtBQUVBO0FBQUE7QUFJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFFQTtBQUNBO0FBQ0E7QUFJQTtBQUdBO0FBQ0E7QUFFQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBREE7QUFJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFBQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBREE7QUFJQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBRUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUVBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFBQTtBQUNBO0FBRUE7QUF2REE7QUF6SUEiLCJzb3VyY2VSb290IjoiIn0=\n//# sourceURL=webpack-internal:///./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/App.vue?vue&type=script&lang=js&\n");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Calendar.vue?vue&type=script&lang=js&":
false,

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Form.vue?vue&type=script&lang=js&":
false,

/***/ "./node_modules/cache-loader/dist/cjs.js?!./node_modules/babel-loader/lib/index.js!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Item.vue?vue&type=script&lang=js&":
false,

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"42eb867e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/App.vue?vue&type=template&id=7ba5bd90&":
/*!*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/cache-loader/dist/cjs.js?{"cacheDirectory":"node_modules/.cache/vue-loader","cacheIdentifier":"42eb867e-vue-loader-template"}!./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/cache-loader/dist/cjs.js??ref--0-0!./node_modules/vue-loader/lib??vue-loader-options!./src/App.vue?vue&type=template&id=7ba5bd90& ***!
  \*********************************************************************************************************************************************************************************************************************************************************************************************************************************************************************************/
/*! exports provided: render, staticRenderFns */
/***/ (function(module, __webpack_exports__, __webpack_require__) {

"use strict";
eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"render\", function() { return render; });\n/* harmony export (binding) */ __webpack_require__.d(__webpack_exports__, \"staticRenderFns\", function() { return staticRenderFns; });\nvar render = function() {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\"div\", { attrs: { id: \"app\" } }, [\n    _c(\n      \"div\",\n      {\n        directives: [\n          {\n            name: \"show\",\n            rawName: \"v-show\",\n            value: _vm.error,\n            expression: \"error\"\n          }\n        ],\n        staticClass: \"form-modal-error\"\n      },\n      [\n        _c(\"b\", [_vm._v(\"Folgende Fehler sind aufgetreten:\")]),\n        _c(\"div\", [_vm._v(_vm._s(_vm.error))])\n      ]\n    ),\n    _vm.loading == true\n      ? _c(\"div\", { staticClass: \"overlay\" }, [\n          _c(\"i\", { staticClass: \"fa fas fa-sync-alt fa-spin\" })\n        ])\n      : _vm._e(),\n    _c(\n      \"div\",\n      { attrs: { id: \"main-box\" } },\n      [_c(\"Calendar\", { attrs: { dates: _vm.dates, acl: _vm.acl } })],\n      1\n    )\n  ])\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n//# sourceURL=[module]\n//# sourceMappingURL=data:application/json;charset=utf-8;base64,eyJ2ZXJzaW9uIjozLCJmaWxlIjoiLi9ub2RlX21vZHVsZXMvY2FjaGUtbG9hZGVyL2Rpc3QvY2pzLmpzP3tcImNhY2hlRGlyZWN0b3J5XCI6XCJub2RlX21vZHVsZXMvLmNhY2hlL3Z1ZS1sb2FkZXJcIixcImNhY2hlSWRlbnRpZmllclwiOlwiNDJlYjg2N2UtdnVlLWxvYWRlci10ZW1wbGF0ZVwifSEuL25vZGVfbW9kdWxlcy92dWUtbG9hZGVyL2xpYi9sb2FkZXJzL3RlbXBsYXRlTG9hZGVyLmpzPyEuL25vZGVfbW9kdWxlcy9jYWNoZS1sb2FkZXIvZGlzdC9janMuanM/IS4vbm9kZV9tb2R1bGVzL3Z1ZS1sb2FkZXIvbGliL2luZGV4LmpzPyEuL3NyYy9BcHAudnVlP3Z1ZSZ0eXBlPXRlbXBsYXRlJmlkPTdiYTViZDkwJi5qcyIsInNvdXJjZXMiOlsid2VicGFjazovLy8uL3NyYy9BcHAudnVlPzk1YjMiXSwic291cmNlc0NvbnRlbnQiOlsidmFyIHJlbmRlciA9IGZ1bmN0aW9uKCkge1xuICB2YXIgX3ZtID0gdGhpc1xuICB2YXIgX2ggPSBfdm0uJGNyZWF0ZUVsZW1lbnRcbiAgdmFyIF9jID0gX3ZtLl9zZWxmLl9jIHx8IF9oXG4gIHJldHVybiBfYyhcImRpdlwiLCB7IGF0dHJzOiB7IGlkOiBcImFwcFwiIH0gfSwgW1xuICAgIF9jKFxuICAgICAgXCJkaXZcIixcbiAgICAgIHtcbiAgICAgICAgZGlyZWN0aXZlczogW1xuICAgICAgICAgIHtcbiAgICAgICAgICAgIG5hbWU6IFwic2hvd1wiLFxuICAgICAgICAgICAgcmF3TmFtZTogXCJ2LXNob3dcIixcbiAgICAgICAgICAgIHZhbHVlOiBfdm0uZXJyb3IsXG4gICAgICAgICAgICBleHByZXNzaW9uOiBcImVycm9yXCJcbiAgICAgICAgICB9XG4gICAgICAgIF0sXG4gICAgICAgIHN0YXRpY0NsYXNzOiBcImZvcm0tbW9kYWwtZXJyb3JcIlxuICAgICAgfSxcbiAgICAgIFtcbiAgICAgICAgX2MoXCJiXCIsIFtfdm0uX3YoXCJGb2xnZW5kZSBGZWhsZXIgc2luZCBhdWZnZXRyZXRlbjpcIildKSxcbiAgICAgICAgX2MoXCJkaXZcIiwgW192bS5fdihfdm0uX3MoX3ZtLmVycm9yKSldKVxuICAgICAgXVxuICAgICksXG4gICAgX3ZtLmxvYWRpbmcgPT0gdHJ1ZVxuICAgICAgPyBfYyhcImRpdlwiLCB7IHN0YXRpY0NsYXNzOiBcIm92ZXJsYXlcIiB9LCBbXG4gICAgICAgICAgX2MoXCJpXCIsIHsgc3RhdGljQ2xhc3M6IFwiZmEgZmFzIGZhLXN5bmMtYWx0IGZhLXNwaW5cIiB9KVxuICAgICAgICBdKVxuICAgICAgOiBfdm0uX2UoKSxcbiAgICBfYyhcbiAgICAgIFwiZGl2XCIsXG4gICAgICB7IGF0dHJzOiB7IGlkOiBcIm1haW4tYm94XCIgfSB9LFxuICAgICAgW19jKFwiQ2FsZW5kYXJcIiwgeyBhdHRyczogeyBkYXRlczogX3ZtLmRhdGVzLCBhY2w6IF92bS5hY2wgfSB9KV0sXG4gICAgICAxXG4gICAgKVxuICBdKVxufVxudmFyIHN0YXRpY1JlbmRlckZucyA9IFtdXG5yZW5kZXIuX3dpdGhTdHJpcHBlZCA9IHRydWVcblxuZXhwb3J0IHsgcmVuZGVyLCBzdGF0aWNSZW5kZXJGbnMgfSJdLCJtYXBwaW5ncyI6IkFBQUE7QUFBQTtBQUFBO0FBQUE7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBO0FBQ0E7QUFDQTtBQUNBOyIsInNvdXJjZVJvb3QiOiIifQ==\n//# sourceURL=webpack-internal:///./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"42eb867e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/App.vue?vue&type=template&id=7ba5bd90&\n");

/***/ }),

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"42eb867e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Calendar.vue?vue&type=template&id=12cb4c6e&":
false,

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"42eb867e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Form.vue?vue&type=template&id=1b5a9218&":
false,

/***/ "./node_modules/cache-loader/dist/cjs.js?{\"cacheDirectory\":\"node_modules/.cache/vue-loader\",\"cacheIdentifier\":\"42eb867e-vue-loader-template\"}!./node_modules/vue-loader/lib/loaders/templateLoader.js?!./node_modules/cache-loader/dist/cjs.js?!./node_modules/vue-loader/lib/index.js?!./src/components/Item.vue?vue&type=template&id=1c4665c3&":
false,

/***/ "./node_modules/core-js/modules/_advance-string-index.js":
false,

/***/ "./node_modules/core-js/modules/_fix-re-wks.js":
false,

/***/ "./node_modules/core-js/modules/_flags.js":
false,

/***/ "./node_modules/core-js/modules/_inherit-if-required.js":
false,

/***/ "./node_modules/core-js/modules/_object-gopd.js":
false,

/***/ "./node_modules/core-js/modules/_object-gopn.js":
false,

/***/ "./node_modules/core-js/modules/_parse-float.js":
false,

/***/ "./node_modules/core-js/modules/_regexp-exec-abstract.js":
false,

/***/ "./node_modules/core-js/modules/_regexp-exec.js":
false,

/***/ "./node_modules/core-js/modules/_set-proto.js":
false,

/***/ "./node_modules/core-js/modules/_string-at.js":
false,

/***/ "./node_modules/core-js/modules/_string-trim.js":
false,

/***/ "./node_modules/core-js/modules/_string-ws.js":
false,

/***/ "./node_modules/core-js/modules/es6.number.constructor.js":
false,

/***/ "./node_modules/core-js/modules/es6.number.parse-float.js":
false,

/***/ "./node_modules/core-js/modules/es6.regexp.exec.js":
false,

/***/ "./node_modules/core-js/modules/es6.regexp.replace.js":
false,

/***/ "./node_modules/core-js/modules/web.dom.iterable.js":
false,

/***/ "./src/components/Calendar.vue":
false,

/***/ "./src/components/Calendar.vue?vue&type=script&lang=js&":
false,

/***/ "./src/components/Calendar.vue?vue&type=template&id=12cb4c6e&":
false,

/***/ "./src/components/Form.vue":
false,

/***/ "./src/components/Form.vue?vue&type=script&lang=js&":
false,

/***/ "./src/components/Form.vue?vue&type=template&id=1b5a9218&":
false,

/***/ "./src/components/Item.vue":
false,

/***/ "./src/components/Item.vue?vue&type=script&lang=js&":
false,

/***/ "./src/components/Item.vue?vue&type=template&id=1c4665c3&":
false

})