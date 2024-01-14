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

/***/ "./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=template&id=7ba5bd90&":
/*!*******************************************************************************************************************************************************************************************!*\
  !*** ./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options!./src/App.vue?vue&type=template&id=7ba5bd90& ***!
  \*******************************************************************************************************************************************************************************************/
/***/ ((__unused_webpack_module, __webpack_exports__, __webpack_require__) => {

eval("__webpack_require__.r(__webpack_exports__);\n/* harmony export */ __webpack_require__.d(__webpack_exports__, {\n/* harmony export */   \"render\": () => (/* binding */ render),\n/* harmony export */   \"staticRenderFns\": () => (/* binding */ staticRenderFns)\n/* harmony export */ });\nvar render = function () {\n  var _vm = this\n  var _h = _vm.$createElement\n  var _c = _vm._self._c || _h\n  return _c(\n    \"div\",\n    [\n      _c(\"Error\", { attrs: { error: _vm.error } }),\n      _vm._v(\" \"),\n      _c(\"Spinner\", { attrs: { loading: _vm.loading } }),\n      _vm._v(\" \"),\n      _c(\"h2\", [_vm._v(_vm._s(_vm.item.title))]),\n      _vm._v(\" \"),\n      _c(\"p\", [_vm._v(_vm._s(_vm.item.template))]),\n      _vm._v(\"\\n\\n  \" + _vm._s(_vm.item.fields) + \"\\n\\n  \"),\n      _c(\"div\", { staticClass: \"si-form\" }, [\n        _c(\n          \"ul\",\n          [\n            _c(\"li\", [\n              _c(\"label\", [_vm._v(\"Titel\")]),\n              _vm._v(\" \"),\n              _c(\"input\", {\n                directives: [\n                  {\n                    name: \"model\",\n                    rawName: \"v-model\",\n                    value: _vm.item.article_title,\n                    expression: \"item.article_title\",\n                  },\n                ],\n                attrs: { type: \"text\" },\n                domProps: { value: _vm.item.article_title },\n                on: {\n                  input: function ($event) {\n                    if ($event.target.composing) {\n                      return\n                    }\n                    _vm.$set(_vm.item, \"article_title\", $event.target.value)\n                  },\n                },\n              }),\n            ]),\n            _vm._v(\" \"),\n            _vm._l(_vm.item.fields, function (field, index) {\n              return _c(\"li\", { key: index }, [\n                _c(\"label\", [_vm._v(_vm._s(field.title))]),\n                _vm._v(\" \"),\n                _c(\"input\", {\n                  directives: [\n                    {\n                      name: \"model\",\n                      rawName: \"v-model\",\n                      value: field.content,\n                      expression: \"field.content\",\n                    },\n                  ],\n                  attrs: { type: \"text\" },\n                  domProps: { value: field.content },\n                  on: {\n                    input: function ($event) {\n                      if ($event.target.composing) {\n                        return\n                      }\n                      _vm.$set(field, \"content\", $event.target.value)\n                    },\n                  },\n                }),\n              ])\n            }),\n            _vm._v(\" \"),\n            _c(\"li\", [\n              _c(\n                \"button\",\n                { staticClass: \"si-btn\", on: { click: _vm.handlerSubmit } },\n                [_vm._v(\"Speichern\")]\n              ),\n            ]),\n          ],\n          2\n        ),\n      ]),\n    ],\n    1\n  )\n}\nvar staticRenderFns = []\nrender._withStripped = true\n\n\n\n//# sourceURL=webpack://extensions/./src/App.vue?./node_modules/vue-loader/lib/loaders/templateLoader.js??vue-loader-options!./node_modules/vue-loader/lib/index.js??vue-loader-options");

/***/ })

},
/******/ function(__webpack_require__) { // webpackRuntimeModules
/******/ /* webpack/runtime/getFullHash */
/******/ (() => {
/******/ 	__webpack_require__.h = () => ("58f424687a4a3e94bcc2")
/******/ })();
/******/ 
/******/ }
);