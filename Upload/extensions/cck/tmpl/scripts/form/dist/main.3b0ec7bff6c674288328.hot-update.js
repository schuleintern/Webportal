"use strict";
/*
 * ATTENTION: The "eval" devtool has been used (maybe by default in mode: "development").
 * This devtool is neither made for production nor for readable output files.
 * It uses "eval()" calls to create a separate source file in the browser devtools.
 * If you are trying to read the output file, select a different devtool (https://webpack.js.org/configuration/devtool/)
 * or disable the default devtool with "devtool: false".
 * If you are looking for production-ready output files, see mode: "production" (https://webpack.js.org/configuration/mode/).
 */
self["webpackHotUpdateextensions"]("main",{

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _mixins_Error_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./mixins/Error.vue */ \"./src/mixins/Error.vue\");\n/* harmony import */ var _mixins_Spinner_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./mixins/Spinner.vue */ \"./src/mixins/Spinner.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\nvar axios = (__webpack_require__(/*! axios */ \"./node_modules/axios/index.js\")[\"default\"]);\n\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Error: _mixins_Error_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n    Spinner: _mixins_Spinner_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]\n  },\n  data: function data() {\n    return {\n      apiURL: globals.apiURL,\n      acl: globals.acl,\n      loading: false,\n      error: false,\n      item: globals.item\n    };\n  },\n  created: function created() {},\n  mounted: function mounted() {\n    var _this = this;\n\n    EventBus.$on('form--submit', function (data) {\n      if (!data.item.title) {\n        console.log('missing');\n        return false;\n      }\n\n      var formData = new FormData();\n      formData.append('id', data.item.id);\n      formData.append('title', data.item.title);\n      formData.append('template', data.item.template);\n      _this.loading = true;\n      var that = _this;\n      axios.post(_this.apiURL + '/setForm', formData, {\n        headers: {\n          'Content-Type': 'multipart/form-data'\n        }\n      }).then(function (response) {\n        if (response.data) {\n          if (response.data.insert) {\n            that.loadLists();\n            EventBus.$emit('modal-form--close', {});\n          } else {\n            that.error = '' + response.data.msg;\n          }\n        } else {\n          that.error = 'Fehler beim Laden. 01';\n        }\n      })[\"catch\"](function () {\n        that.error = 'Fehler beim Laden. 02';\n      })[\"finally\"](function () {\n        // always executed\n        that.loading = false;\n      });\n    });\n  },\n  methods: {}\n});\n\n//# sourceURL=webpack://extensions/./src/App.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=template&id=7ba5bd90&":
/*!*******************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=template&id=7ba5bd90& ***!
  \*******************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* binding */ render),\n/* harmony export */   \"staticRenderFns\": () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    [\n      _vm._v(\"\\n\\n  \" + _vm._s(_vm.item) + \"\\n\\n  \"),\n      _c(\"Error\", { attrs: { error: _vm.error } }),\n      _vm._v(\" \"),\n      _c(\"Spinner\", { attrs: { loading: _vm.loading } }),\n      _vm._v(\" \"),\n      _c(\"h2\", [_vm._v(_vm._s(_vm.item.title))]),\n      _vm._v(\" \"),\n      _c(\"p\", [_vm._v(_vm._s(_vm.item.template))]),\n      _vm._v(\" \"),\n      _c(\n        \"ul\",\n        _vm._l(_vm.item.fields, function (field, index) {\n          return _c(\"li\", { key: index }, [\n            _c(\"label\", [_vm._v(_vm._s(field.title))]),\n            _vm._v(\" \"),\n            _c(\"input\", {\n              directives: [\n                {\n                  name: \"model\",\n                  rawName: \"v-model\",\n                  value: field.content,\n                  expression: \"field.content\",\n                },\n              ],\n              attrs: { type: \"text\" },\n              domProps: { value: field.content },\n              on: {\n                input: function ($event) {\n                  if ($event.target.composing) {\n                    return\n                  }\n                  _vm.$set(field, \"content\", $event.target.value)\n                },\n              },\n            }),\n          ])\n        }),\n        0\n      ),\n    ],\n    1\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack://extensions/./src/App.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/getFullHash */
/******/ (() => {
/******/ 	__webpack_require__.h = () => ("bff075e219c1179b5a36")
/******/ })();
/******/ 
/******/ }
);