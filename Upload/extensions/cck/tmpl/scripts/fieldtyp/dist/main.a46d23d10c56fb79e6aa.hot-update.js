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

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/mixins/ModalForm.vue?vue&type=script&lang=js&":
/*!***************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/mixins/ModalForm.vue?vue&type=script&lang=js& ***!
  \***************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _components_Form_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../components/Form.vue */ \"./src/components/Form.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    Form: _components_Form_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n  },\n  data: function data() {\n    return {\n      open: false,\n      form: false\n    };\n  },\n  props: {\n    item: Array\n  },\n  computed: {},\n  created: function created() {\n    var that = this;\n    EventBus.$on('modal-form--open', function (data) {\n      that.form = false;\n\n      if (data && data.item) {\n        that.form = data.item;\n      }\n\n      that.open = true;\n    });\n    EventBus.$on('modal-form--close', function (data) {\n      that.open = false;\n    });\n  },\n  methods: {\n    getItem: function getItem() {\n      if (this.form) {\n        return this.form;\n      }\n\n      return this.item;\n    },\n    handlerClose: function handlerClose() {\n      EventBus.$emit('modal-form--close');\n    }\n  }\n});\n\n//# sourceURL=webpack://extensions/./src/mixins/ModalForm.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/getFullHash */
/******/ (() => {
/******/ 	__webpack_require__.h = () => ("b867b3400954295d5abf")
/******/ })();
/******/ 
/******/ }
);