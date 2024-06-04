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

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/components/Form.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/components/Form.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _mixins_User_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../mixins/User.vue */ \"./src/mixins/User.vue\");\n/* harmony import */ var _mixins_UserSelect_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../mixins/UserSelect.vue */ \"./src/mixins/UserSelect.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    User: _mixins_User_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n    UserSelect: _mixins_UserSelect_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]\n  },\n  name: 'Form',\n  props: {\n    item: Object\n  },\n  data: function data() {\n    return {\n      error: false,\n      required: ''\n    };\n  },\n  created: function created() {},\n  mounted: function mounted() {// access our input using template refs, then focus\n  },\n  methods: {\n    submitForm: function submitForm() {\n      var that = this; //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')\n\n      if (!this.item.title) {\n        console.log('missing');\n        this.required = 'required';\n        return false;\n      }\n\n      EventBus.$emit('form--submit', {\n        item: that.item\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack://extensions/./src/components/Form.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ }),

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/components/Form.vue?vue&type=template&id=1b5a9218&":
/*!*******************************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/components/Form.vue?vue&type=template&id=1b5a9218& ***!
  \*******************************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* binding */ render),\n/* harmony export */   \"staticRenderFns\": () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\"div\", { staticClass: \"si-form\" }, [\n    _c(\"ul\", {}, [\n      _c(\"li\", { class: _vm.required }, [\n        _c(\"label\", [_vm._v(\"Titel\")]),\n        _vm._v(\" \"),\n        _c(\"input\", {\n          directives: [\n            {\n              name: \"model\",\n              rawName: \"v-model\",\n              value: _vm.item.title,\n              expression: \"item.title\",\n            },\n          ],\n          domProps: { value: _vm.item.title },\n          on: {\n            input: function ($event) {\n              if ($event.target.composing) {\n                return\n              }\n              _vm.$set(_vm.item, \"title\", $event.target.value)\n            },\n          },\n        }),\n      ]),\n      _vm._v(\" \"),\n      _c(\"li\", [\n        _c(\"label\", [_vm._v(\"Template\")]),\n        _vm._v(\" \"),\n        _c(\"input\", {\n          directives: [\n            {\n              name: \"model\",\n              rawName: \"v-model\",\n              value: _vm.item.template,\n              expression: \"item.template\",\n            },\n          ],\n          domProps: { value: _vm.item.template },\n          on: {\n            input: function ($event) {\n              if ($event.target.composing) {\n                return\n              }\n              _vm.$set(_vm.item, \"template\", $event.target.value)\n            },\n          },\n        }),\n      ]),\n      _vm._v(\" \"),\n      _c(\"li\", [\n        _c(\"button\", { staticClass: \"si-btn\", on: { click: _vm.submitForm } }, [\n          _c(\"i\", { staticClass: \"fa fa-save\" }),\n          _vm._v(\" Speichern\"),\n        ]),\n      ]),\n    ]),\n  ])\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack://extensions/./src/components/Form.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/getFullHash */
/******/ (() => {
/******/ 	__webpack_require__.h = () => ("9e5b0f125a66f2aece83")
/******/ })();
/******/ 
/******/ }
);