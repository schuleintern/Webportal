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

/***/ "./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/components/List.vue?vue&type=script&lang=js&":
/*!**************************************************************************************************************************************************************!*\
  !*** ./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/components/List.vue?vue&type=script&lang=js& ***!
  \**************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"default\": () => (__WEBPACK_DEFAULT_EXPORT__)\n/* harmony export */ });\n/* harmony import */ var _mixins_User_vue__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./../mixins/User.vue */ \"./src/mixins/User.vue\");\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n//\n\n/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = ({\n  components: {\n    User: _mixins_User_vue__WEBPACK_IMPORTED_MODULE_0__[\"default\"]\n  },\n  name: 'List',\n  props: {\n    items: Array\n  },\n  data: function data() {\n    return {\n      sort: {\n        column: false,\n        order: true\n      }\n    };\n  },\n  computed: {\n    sortedArray: function sortedArray() {\n      var _this = this;\n\n      if (this.sort.column) {\n        if (this.sort.order) {\n          return this.items.sort(function (a, b) {\n            return a[_this.sort.column].localeCompare(b[_this.sort.column]);\n          });\n        } else {\n          return this.items.sort(function (a, b) {\n            return b[_this.sort.column].localeCompare(a[_this.sort.column]);\n          });\n        }\n      }\n\n      return this.items;\n    }\n  },\n  created: function created() {},\n  mounted: function mounted() {},\n  methods: {\n    handlerClick: function handlerClick(item) {\n      EventBus.$emit('modal-form--open', {\n        item: item\n      });\n    },\n    handlerSort: function handlerSort(column) {\n      if (column) {\n        this.sort.column = column;\n\n        if (this.sort.order) {\n          this.sort.order = false;\n        } else {\n          this.sort.order = true;\n        }\n      }\n    }\n  }\n});\n\n//# sourceURL=webpack://extensions/./src/components/List.vue?./node_modules/babel-loader/lib/index.js!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/getFullHash */
/******/ (() => {
/******/ 	__webpack_require__.h = () => ("037d932978d1f7a46acf")
/******/ })();
/******/ 
/******/ }
);