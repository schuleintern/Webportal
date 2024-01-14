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

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _mixins_User_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../mixins/User.vue */ \"./src/mixins/User.vue\");\n/* harmony import */ var _mixins_UserSelect_vue__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../mixins/UserSelect.vue */ \"./src/mixins/UserSelect.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    User: _mixins_User_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"],\n    UserSelect: _mixins_UserSelect_vue__WEBPACK_IMPORTED_MODULE_1__[\"default\"]\n  },\n  name: 'Form',\n  props: {\n    item: Object\n  },\n  data: function data() {\n    return {\n      error: false,\n      required: ''\n    };\n  },\n  created: function created() {},\n  mounted: function mounted() {// access our input using template refs, then focus\n  },\n  methods: {\n    submitForm: function submitForm() {\n      var that = this; //this.form.date = this.$date(this.form.date).format('YYYY-MM-DD')\n\n      if (!this.item.template) {\n        this.item.template = '';\n      }\n\n      if (!this.item.title) {\n        console.log('missing');\n        this.required = 'required';\n        return false;\n      }\n\n      EventBus.$emit('form--submit', {\n        item: that.item\n      });\n    }\n  }\n});\n\n//# sourceURL=webpack://extensions/./src/components/Form.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/getFullHash */
/******/ (() => {
/******/ 	__webpack_require__.h = () => ("8917e6b2d583c8c9ff73")
/******/ })();
/******/ 
/******/ }
);