(function(e){function l(l){for(var r,n,i=l[0],o=l[1],s=l[2],d=0,h=[];d<i.length;d++)n=i[d],Object.prototype.hasOwnProperty.call(t,n)&&t[n]&&h.push(t[n][0]),t[n]=0;for(r in o)Object.prototype.hasOwnProperty.call(o,r)&&(e[r]=o[r]);u&&u(l);while(h.length)h.shift()();return c.push.apply(c,s||[]),a()}function a(){for(var e,l=0;l<c.length;l++){for(var a=c[l],r=!0,i=1;i<a.length;i++){var o=a[i];0!==t[o]&&(r=!1)}r&&(c.splice(l--,1),e=n(n.s=a[0]))}return e}var r={},t={app:0},c=[];function n(l){if(r[l])return r[l].exports;var a=r[l]={i:l,l:!1,exports:{}};return e[l].call(a.exports,a,a.exports,n),a.l=!0,a.exports}n.m=e,n.c=r,n.d=function(e,l,a){n.o(e,l)||Object.defineProperty(e,l,{enumerable:!0,get:a})},n.r=function(e){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(e,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(e,"__esModule",{value:!0})},n.t=function(e,l){if(1&l&&(e=n(e)),8&l)return e;if(4&l&&"object"===typeof e&&e&&e.__esModule)return e;var a=Object.create(null);if(n.r(a),Object.defineProperty(a,"default",{enumerable:!0,value:e}),2&l&&"string"!=typeof e)for(var r in e)n.d(a,r,function(l){return e[l]}.bind(null,r));return a},n.n=function(e){var l=e&&e.__esModule?function(){return e["default"]}:function(){return e};return n.d(l,"a",l),l},n.o=function(e,l){return Object.prototype.hasOwnProperty.call(e,l)},n.p="/";var i=window["webpackJsonp"]=window["webpackJsonp"]||[],o=i.push.bind(i);i.push=l,i=i.slice();for(var s=0;s<i.length;s++)l(i[s]);var u=o;c.push([0,"chunk-vendors"]),a()})({0:function(e,l,a){e.exports=a("56d7")},"56d7":function(e,l,a){"use strict";a.r(l);a("cadf"),a("551c"),a("f751"),a("097d");var r=a("2b0e"),t=function(){var e=this,l=e.$createElement,a=e._self._c||l;return a("div",{attrs:{id:"app"}},[a("Acl",{attrs:{moduleID:!1,childID:!1,moduleName:e.moduleName}})],1)},c=[],n=function(){var e=this,l=e.$createElement,a=e._self._c||l;return a("div",{attrs:{id:"app"}},[e.error?a("div",{staticClass:"form-modal-error"},[a("b",[e._v("Folgende Fehler sind aufgetreten:")]),a("ul",[a("li",[e._v(e._s(e.error))])])]):e._e(),a("h3",[e._v("Access Control List")]),a("div",{staticClass:"acl"},[a("ul",[a("li",[a("h5",[e._v("Schüler")]),a("ul",[a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.schuelerRead,expression:"acl.schuelerRead"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.schuelerRead)?e._i(e.acl.schuelerRead,null)>-1:e._q(e.acl.schuelerRead,"1")},on:{change:function(l){var a=e.acl.schuelerRead,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"schuelerRead",a.concat([c])):n>-1&&e.$set(e.acl,"schuelerRead",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"schuelerRead",t)}}}),a("label",[e._v("Lesen")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.schuelerWrite,expression:"acl.schuelerWrite"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.schuelerWrite)?e._i(e.acl.schuelerWrite,null)>-1:e._q(e.acl.schuelerWrite,"1")},on:{change:function(l){var a=e.acl.schuelerWrite,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"schuelerWrite",a.concat([c])):n>-1&&e.$set(e.acl,"schuelerWrite",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"schuelerWrite",t)}}}),a("label",[e._v("Schreiben")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.schuelerDelete,expression:"acl.schuelerDelete"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.schuelerDelete)?e._i(e.acl.schuelerDelete,null)>-1:e._q(e.acl.schuelerDelete,"1")},on:{change:function(l){var a=e.acl.schuelerDelete,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"schuelerDelete",a.concat([c])):n>-1&&e.$set(e.acl,"schuelerDelete",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"schuelerDelete",t)}}}),a("label",[e._v("Löschen")])])])]),a("li",[a("h5",[e._v("Eltern")]),a("ul",[a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.elternRead,expression:"acl.elternRead"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.elternRead)?e._i(e.acl.elternRead,null)>-1:e._q(e.acl.elternRead,"1")},on:{change:function(l){var a=e.acl.elternRead,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"elternRead",a.concat([c])):n>-1&&e.$set(e.acl,"elternRead",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"elternRead",t)}}}),a("label",[e._v("Lesen")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.elternWrite,expression:"acl.elternWrite"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.elternWrite)?e._i(e.acl.elternWrite,null)>-1:e._q(e.acl.elternWrite,"1")},on:{change:function(l){var a=e.acl.elternWrite,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"elternWrite",a.concat([c])):n>-1&&e.$set(e.acl,"elternWrite",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"elternWrite",t)}}}),a("label",[e._v("Schreiben")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.elternDelete,expression:"acl.elternDelete"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.elternDelete)?e._i(e.acl.elternDelete,null)>-1:e._q(e.acl.elternDelete,"1")},on:{change:function(l){var a=e.acl.elternDelete,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"elternDelete",a.concat([c])):n>-1&&e.$set(e.acl,"elternDelete",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"elternDelete",t)}}}),a("label",[e._v("Löschen")])])])]),a("li",[a("h5",[e._v("Lehrer")]),a("ul",[a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.lehrerRead,expression:"acl.lehrerRead"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.lehrerRead)?e._i(e.acl.lehrerRead,null)>-1:e._q(e.acl.lehrerRead,"1")},on:{change:function(l){var a=e.acl.lehrerRead,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"lehrerRead",a.concat([c])):n>-1&&e.$set(e.acl,"lehrerRead",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"lehrerRead",t)}}}),a("label",[e._v("Lesen")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.lehrerWrite,expression:"acl.lehrerWrite"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.lehrerWrite)?e._i(e.acl.lehrerWrite,null)>-1:e._q(e.acl.lehrerWrite,"1")},on:{change:function(l){var a=e.acl.lehrerWrite,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"lehrerWrite",a.concat([c])):n>-1&&e.$set(e.acl,"lehrerWrite",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"lehrerWrite",t)}}}),a("label",[e._v("Schreiben")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.lehrerDelete,expression:"acl.lehrerDelete"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.lehrerDelete)?e._i(e.acl.lehrerDelete,null)>-1:e._q(e.acl.lehrerDelete,"1")},on:{change:function(l){var a=e.acl.lehrerDelete,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"lehrerDelete",a.concat([c])):n>-1&&e.$set(e.acl,"lehrerDelete",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"lehrerDelete",t)}}}),a("label",[e._v("Löschen")])])])]),a("li",[a("h5",[e._v("Sonstige")]),a("ul",[a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.noneRead,expression:"acl.noneRead"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.noneRead)?e._i(e.acl.noneRead,null)>-1:e._q(e.acl.noneRead,"1")},on:{change:function(l){var a=e.acl.noneRead,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"noneRead",a.concat([c])):n>-1&&e.$set(e.acl,"noneRead",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"noneRead",t)}}}),a("label",[e._v("Lesen")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.noneWrite,expression:"acl.noneWrite"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.noneWrite)?e._i(e.acl.noneWrite,null)>-1:e._q(e.acl.noneWrite,"1")},on:{change:function(l){var a=e.acl.noneWrite,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"noneWrite",a.concat([c])):n>-1&&e.$set(e.acl,"noneWrite",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"noneWrite",t)}}}),a("label",[e._v("Schreiben")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.noneDelete,expression:"acl.noneDelete"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.noneDelete)?e._i(e.acl.noneDelete,null)>-1:e._q(e.acl.noneDelete,"1")},on:{change:function(l){var a=e.acl.noneDelete,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"noneDelete",a.concat([c])):n>-1&&e.$set(e.acl,"noneDelete",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"noneDelete",t)}}}),a("label",[e._v("Löschen")])])])]),a("li",[a("h5",[e._v("Eigentümer")]),a("ul",[a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.owneRead,expression:"acl.owneRead"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.owneRead)?e._i(e.acl.owneRead,null)>-1:e._q(e.acl.owneRead,"1")},on:{change:function(l){var a=e.acl.owneRead,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"owneRead",a.concat([c])):n>-1&&e.$set(e.acl,"owneRead",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"owneRead",t)}}}),a("label",[e._v("Lesen")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.owneWrite,expression:"acl.owneWrite"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.owneWrite)?e._i(e.acl.owneWrite,null)>-1:e._q(e.acl.owneWrite,"1")},on:{change:function(l){var a=e.acl.owneWrite,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"owneWrite",a.concat([c])):n>-1&&e.$set(e.acl,"owneWrite",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"owneWrite",t)}}}),a("label",[e._v("Schreiben")])]),a("li",[a("input",{directives:[{name:"model",rawName:"v-model",value:e.acl.owneDelete,expression:"acl.owneDelete"}],attrs:{type:"checkbox","true-value":"1","false-value":"0"},domProps:{checked:Array.isArray(e.acl.owneDelete)?e._i(e.acl.owneDelete,null)>-1:e._q(e.acl.owneDelete,"1")},on:{change:function(l){var a=e.acl.owneDelete,r=l.target,t=r.checked?"1":"0";if(Array.isArray(a)){var c=null,n=e._i(a,c);r.checked?n<0&&e.$set(e.acl,"owneDelete",a.concat([c])):n>-1&&e.$set(e.acl,"owneDelete",a.slice(0,n).concat(a.slice(n+1)))}else e.$set(e.acl,"owneDelete",t)}}}),a("label",[e._v("Löschen")])])])])]),a("button",{on:{click:e.handlerSubmit}},[e._v("Speichern")])])])},i=[],o=(a("ac6a"),a("456d"),a("c5f6"),a("bc3a").default),s={name:"app",props:{moduleID:Boolean||Number,moduleName:String,childID:Boolean||Number},components:{},data:function(){return{loading:!0,error:!1,module:!1,acl:{id:0,schuelerRead:0,schuelerWrite:0,schuelerDelete:0,elternRead:0,elternWrite:0,elternDelete:0,lehrerRead:0,lehrerWrite:0,lehrerDelete:0,noneRead:0,noneWrite:0,noneDelete:0,owneRead:0,owneWrite:0,owneDelete:0}}},watch:{acl:{handler:function(e){var l=this;EventBus.$emit("acl--changed",{acl:l.acl,moduleName:l.moduleName,childID:l.childID})},deep:!0}},created:function(){this.loadAcl()},methods:{loadAcl:function(){if(this.moduleName&&!this.moduleID?this.module=this.moduleName:this.module=this.moduleID,null==this.moduleID)for(var e=Object.keys(this.acl),l=0,a=e;l<a.length;l++){var r=a[l];this.acl[r]=0}else{console.log("load");var t=this;t.error=!1,t.ajaxGet("rest.php/GetAcl/"+this.module,{},(function(e,l){1==e.data.error&&e.data.msg?l.error=e.data.msg:e.data.acl&&(l.acl=e.data.acl)}))}},handlerSubmit:function(){var e=this;e.error=!1,e.ajaxPost("rest.php/SetAcl/"+this.module,{acl:this.acl},{},(function(e,l){1==e.data.error&&e.data.msg?l.error=e.data.msg:1==e.data.done&&(l.error=!1)}))},ajaxGet:function(e,l,a,r,t){this.loading=!0;var c=this;o.get(e,{params:l}).then((function(e){a&&"function"===typeof a&&a(e,c)})).catch((function(e){e&&"function"===typeof r&&r(e)})).finally((function(){t&&"function"===typeof t&&t(),c.loading=!1}))},ajaxPost:function(e,l,a,r,t,c){this.loading=!0;var n=this;o.post(e,l,{params:a}).then((function(e){r&&"function"===typeof r&&r(e,n)})).catch((function(e){e&&"function"===typeof t&&t(e)})).finally((function(){c&&"function"===typeof c&&c(),n.loading=!1}))}}},u=s,d=a("2877"),h=Object(d["a"])(u,n,i,!1,null,null,null),v=h.exports,p={name:"app",components:{Acl:v},data:function(){return{moduleName:"mensaSpeiseplan"}},created:function(){},methods:{}},m=p,f=Object(d["a"])(m,t,c,!1,null,null,null),y=f.exports,_=a("5a0c"),A=a.n(_);a("6210");window.EventBus=new r["a"],A.a.locale("de"),Object.defineProperties(r["a"].prototype,{$date:{get:function(){return A.a}}}),r["a"].config.productionTip=!1;var b=!1;b=b||{objekt:!1},new r["a"]({render:function(e){return e(y)}}).$mount("#app")}});
//# sourceMappingURL=app.js.map