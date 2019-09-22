(function(t){function e(e){for(var n,i,s=e[0],c=e[1],u=e[2],d=0,p=[];d<s.length;d++)i=s[d],Object.prototype.hasOwnProperty.call(o,i)&&o[i]&&p.push(o[i][0]),o[i]=0;for(n in c)Object.prototype.hasOwnProperty.call(c,n)&&(t[n]=c[n]);l&&l(e);while(p.length)p.shift()();return a.push.apply(a,u||[]),r()}function r(){for(var t,e=0;e<a.length;e++){for(var r=a[e],n=!0,s=1;s<r.length;s++){var c=r[s];0!==o[c]&&(n=!1)}n&&(a.splice(e--,1),t=i(i.s=r[0]))}return t}var n={},o={app:0},a=[];function i(e){if(n[e])return n[e].exports;var r=n[e]={i:e,l:!1,exports:{}};return t[e].call(r.exports,r,r.exports,i),r.l=!0,r.exports}i.m=t,i.c=n,i.d=function(t,e,r){i.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:r})},i.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},i.t=function(t,e){if(1&e&&(t=i(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var r=Object.create(null);if(i.r(r),Object.defineProperty(r,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var n in t)i.d(r,n,function(e){return t[e]}.bind(null,n));return r},i.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return i.d(e,"a",e),e},i.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},i.p="/";var s=window["webpackJsonp"]=window["webpackJsonp"]||[],c=s.push.bind(s);s.push=e,s=s.slice();for(var u=0;u<s.length;u++)e(s[u]);var l=c;a.push([0,"chunk-vendors"]),r()})({0:function(t,e,r){t.exports=r("56d7")},"56d7":function(t,e,r){"use strict";r.r(e);r("cadf"),r("551c"),r("f751"),r("097d");var n=r("2b0e"),o=r("5c96"),a=r.n(o),i=r("b2d6"),s=r.n(i),c=(r("0fae"),function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:!t.pageReady,expression:"!pageReady"}],attrs:{id:"main-container"}},[t.storeList&&t.storeList.length>1?r("el-select",{attrs:{value:t.currentStoreId,placeholder:"Please choose a store"},on:{input:t.setCurrentStoreId}},t._l(t.storeList,(function(t){return r("el-option",{key:t.website_id,attrs:{label:t.name,value:t.website_id}})})),1):t._e(),r("el-tabs",{staticStyle:{"margin-top":"20px"},attrs:{type:"border-card"},on:{"tab-click":t.handleClick},model:{value:t.activeTab,callback:function(e){t.activeTab=e},expression:"activeTab"}},[r("el-tab-pane",{attrs:{label:"Settings",name:"settings"}},[r("settings",{key:t.currentStoreId})],1),t.currentStoreId?r("el-tab-pane",{attrs:{label:"Recommendation",name:"recommendations"}},[r("recommendations",{key:t.currentStoreId})],1):t._e(),t.currentStoreId?r("el-tab-pane",{attrs:{label:"Custom CSS",name:"custom_css"}},[r("custom-css",{key:t.currentStoreId})],1):t._e(),t.currentStoreId?r("el-tab-pane",{attrs:{label:"Sync Status",name:"sync_status"}},[r("sync-status",{key:t.currentStoreId})],1):t._e(),r("el-tab-pane",{attrs:{label:"Logs",name:"logs"}},[r("log")],1)],1)],1)}),u=[],l=(r("8e6e"),r("ac6a"),r("456d"),r("bd86")),d=r("2f62"),p=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],attrs:{id:"tab-settings"}},[t.currentStoreId?[r("el-form",{ref:"form",attrs:{model:t.form,rules:t.rules,"label-width":"100px"}},[r("el-form-item",{attrs:{label:"API key",prop:"api_key"}},[r("el-input",{staticStyle:{width:"300px"},attrs:{disabled:t.existing},model:{value:t.form.api_key,callback:function(e){t.$set(t.form,"api_key",e)},expression:"form.api_key"}})],1),r("el-form-item",{attrs:{label:"API secret",prop:"api_secret"}},[r("el-input",{staticStyle:{width:"300px"},attrs:{disabled:t.existing,type:"password"},model:{value:t.form.api_secret,callback:function(e){t.$set(t.form,"api_secret",e)},expression:"form.api_secret"}})],1),r("el-form-item",[t.existing?r("el-button",{attrs:{type:"danger",round:""},on:{click:t.handleDisconnect}},[t._v("DISCONNECT FROM DATACUE")]):r("el-button",{attrs:{type:"primary",round:""},on:{click:t.handleSave}},[t._v("Save")])],1)],1),r("el-divider")]:t._e(),r("h4",[t._v("Here are some resources you might find helpful")]),t._m(0),r("el-divider"),r("el-button",{staticStyle:{"margin-bottom":"20px"},attrs:{type:"success",round:""},on:{click:t.loginToDataCue}},[t._v("LOGIN TO MY DATACUE DASHBOARD")]),t._m(1),r("el-divider"),r("h4",[t._v("Support Center")]),t._m(2)],2)},f=[function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("ul",{staticClass:"list",staticStyle:{"margin-left":"20px"}},[r("li",[r("a",{staticStyle:{color:"#8c5c85","font-weight":"600"},attrs:{href:"https://help.datacue.co/install/magento.html#add-recommendations",target:"_blank"}},[t._v("Add banners and products to your site")])])])},function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("p",[t._v("No account yet? Sign up "),r("a",{staticStyle:{color:"#8c5c85","font-weight":"600"},attrs:{href:"https://app.datacue.co/en/sign-up",target:"_blank"}},[t._v("here")])])},function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("p",[t._v("Questions? Need help? Email us at "),r("a",{staticStyle:{color:"#8c5c85","font-weight":"600"},attrs:{href:"mailto:support@datacue.co",target:"_blank"}},[t._v("support@datacue.co")]),t._v(" to speak to a real person")])}],m=r("bc3a"),g=r.n(m),b=r("4328"),h=r.n(b),y=g.a.create({withCredentials:!0,timeout:2e4,headers:{"Content-Type":"application/x-www-form-urlencoded"}});y.interceptors.request.use((function(t){return"post"===t.method&&(t.data||(t.data={}),t.data.form_key=window.FORM_KEY,t.data=h.a.stringify(t.data)),t}),(function(t){return console.log("Request error",t),Promise.reject(t)})),y.interceptors.response.use((function(t){return t.status>=200&&t.status<300?t.data:Promise.reject(t)}),(function(t){return console.log("Response error",t),Promise.reject(t)}));var _=function(t){return y({url:t.url,method:t.method,data:t.data})};function v(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function w(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?v(r,!0).forEach((function(e){Object(l["a"])(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):v(r).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}var O={data:function(){return{loading:!1,form:{api_key:"",api_secret:""},existing:!1,rules:{api_key:[{required:!0,message:"API key is required",trigger:"blur"}],api_secret:[{required:!0,message:"API secret is required",trigger:"blur"}]}}},computed:w({},Object(d["d"])({currentStoreId:function(t){return t.currentStoreId}})),methods:{fetchApiKeyAndApiSecret:function(){var t=this;window.datacueURLs&&(this.loading=!0,this.form={api_key:"",api_secret:""},_({url:window.datacueURLs.getApiKeyAndApiSecret,method:"post",data:{website_id:this.currentStoreId}}).then((function(e){e.data?(t.existing=!0,t.form=w({},e.data)):t.existing=!1})).finally((function(){t.loading=!1})))},handleSave:function(){var t=this;this.$refs.form.validate((function(e){e&&(t.loading=!0,_({url:window.datacueURLs.setApiKeyAndApiSecret,method:"post",data:{website_id:t.currentStoreId,api_key:t.form.api_key,api_secret:t.form.api_secret}}).then((function(e){"ok"===e.status?(t.$notify({title:"Success",message:"The API key and API secret are saved.",type:"success"}),t.existing=!0):t.$notify.error({title:"Error!",message:e.msg})})).finally((function(){t.loading=!1})))}))},handleDisconnect:function(){var t=this;this.$confirm("Are you sure to disconnect?","Hint",{type:"warning"}).then((function(){t.loading=!0,_({url:window.datacueURLs.disconnect,method:"post",data:{website_id:t.currentStoreId}}).then((function(e){"ok"===e.status?(t.$notify({title:"Success",message:"The current website has already been disconnected.",type:"success"}),t.form={api_key:"",api_secret:""},t.existing=!1):t.$notify.error({title:"Error!",message:e.msg})})).finally((function(){t.loading=!1}))})).catch((function(){t.$message({type:"info",message:"Cancelled!"})}))},loginToDataCue:function(){window.open("https://app.datacue.co","_blank")}},mounted:function(){this.currentStoreId>0&&this.fetchApiKeyAndApiSecret()}},S=O,j=r("2877"),P=Object(j["a"])(S,p,f,!1,null,null,null),k=P.exports,L=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],attrs:{id:"recommendations"}},[r("el-form",{ref:"form",attrs:{model:t.form,rules:t.rules,"label-width":"250px"}},[r("el-form-item",{attrs:{label:"Add products to product page",prop:"products_status_for_product_page"}},[r("el-select",{model:{value:t.form.products_status_for_product_page,callback:function(e){t.$set(t.form,"products_status_for_product_page",e)},expression:"form.products_status_for_product_page"}},[r("el-option",{attrs:{label:"Disabled",value:"0"}}),r("el-option",{attrs:{label:"Enabled",value:"1"}})],1)],1),r("el-form-item",{attrs:{label:"Recommendation type",prop:"products_type_for_product_page"}},[r("el-select",{model:{value:t.form.products_type_for_product_page,callback:function(e){t.$set(t.form,"products_type_for_product_page",e)},expression:"form.products_type_for_product_page"}},[r("el-option",{attrs:{label:"All",value:"all"}}),r("el-option",{attrs:{label:"Recently Viewed",value:"recent"}}),r("el-option",{attrs:{label:"Similar to current product",value:"similar"}}),r("el-option",{attrs:{label:"Related Products",value:"related"}})],1)],1),r("el-form-item",[r("el-button",{attrs:{type:"primary",round:""},on:{click:t.handleSave}},[t._v("Save")])],1)],1)],1)},x=[];function D(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function I(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?D(r,!0).forEach((function(e){Object(l["a"])(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):D(r).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}var R={data:function(){return{form:{products_status_for_product_page:"0",products_type_for_product_page:"all"},loading:!1}},computed:I({},Object(d["d"])({currentStoreId:function(t){return t.currentStoreId}}),{rules:function(){return"1"===this.form.products_status_for_product_page?{products_status_for_product_page:[{required:!0,message:"this is required",trigger:"blur"}],products_type_for_product_page:[{required:!0,message:"this is required",trigger:"blur"}]}:{products_status_for_product_page:[{required:!0,message:"this is required",trigger:"blur"}]}}}),methods:{fetchData:function(){var t=this;window.datacueURLs&&(this.loading=!0,_({url:window.datacueURLs.getRecommendations,method:"post",data:{website_id:this.currentStoreId}}).then((function(e){var r=I({},t.form);e.data.products_status_for_product_page&&(r.products_status_for_product_page=e.data.products_status_for_product_page),e.data.products_type_for_product_page&&(r.products_type_for_product_page=e.data.products_type_for_product_page),t.form=r})).finally((function(){t.loading=!1})))},handleSave:function(){var t=this;this.$refs.form.validate((function(e){e&&(t.loading=!0,_({url:window.datacueURLs.setRecommendations,method:"post",data:I({website_id:t.currentStoreId},t.form)}).then((function(e){"ok"===e.status?t.$notify({title:"Success",message:"The recommendations are saved.",type:"success"}):t.$notify.error({title:"Error!",message:e.msg})})).finally((function(){t.loading=!1})))}))}},mounted:function(){this.fetchData()}},C=R,E=Object(j["a"])(C,L,x,!1,null,null,null),A=E.exports,$=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],attrs:{id:"tab-custom-css"}},[r("el-input",{attrs:{type:"textarea",autosize:{minRows:15,maxRows:30},placeholder:"Please enter your custom CSS"},model:{value:t.css,callback:function(e){t.css=e},expression:"css"}}),r("el-button",{staticStyle:{"margin-top":"10px"},attrs:{type:"primary",round:""},on:{click:t.handleSave}},[t._v("Save")])],1)},T=[];function U(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function N(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?U(r,!0).forEach((function(e){Object(l["a"])(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):U(r).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}var q={data:function(){return{loading:!1,css:""}},computed:N({},Object(d["d"])({currentStoreId:function(t){return t.currentStoreId}})),methods:{fetchCss:function(){var t=this;window.datacueURLs&&(this.loading=!0,_({url:window.datacueURLs.getCustomCss,method:"post",data:{website_id:this.currentStoreId}}).then((function(e){t.css=e.data.content})).finally((function(){t.loading=!1})))},handleSave:function(){var t=this;this.loading=!0,_({url:window.datacueURLs.setCustomCss,method:"post",data:{website_id:this.currentStoreId,css:this.css}}).then((function(e){"ok"===e.status?t.$notify({title:"Success",message:"The custom CSS are saved.",type:"success"}):t.$notify.error({title:"Error!",message:e.msg})})).finally((function(){t.loading=!1}))}},mounted:function(){this.currentStoreId>0&&this.fetchCss()}},M=q,K=Object(j["a"])(M,$,T,!1,null,null,null),V=K.exports,H=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],attrs:{id:"tab-sync-status"}},[r("el-table",{staticStyle:{width:"100%"},attrs:{data:t.data}},[r("el-table-column",{attrs:{prop:"type",label:"Data Type",width:"180"}}),r("el-table-column",{attrs:{prop:"total",label:"Total",width:"180"}}),r("el-table-column",{attrs:{label:"Number of pending",width:"280"},scopedSlots:t._u([{key:"default",fn:function(e){return[t._v(t._s(e.row.total-e.row.completed-e.row.failed))]}}])}),r("el-table-column",{attrs:{prop:"completed",label:"Number of successes",width:"280"}}),r("el-table-column",{attrs:{prop:"failed",label:"Number of failures",width:"280"}})],1)],1)},F=[];function J(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function Y(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?J(r,!0).forEach((function(e){Object(l["a"])(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):J(r).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}var z={data:function(){return{data:[],timer:null,loading:!1}},computed:Y({},Object(d["d"])({currentStoreId:function(t){return t.currentStoreId}})),methods:{fetchData:function(){var t=this;window.datacueURLs&&(this.loading=!0,_({url:window.datacueURLs.getSyncStatus,method:"post",data:{website_id:this.currentStoreId}}).then((function(e){t.data=Object.keys(e.data).map((function(t){return Y({},e.data[t],{type:t})}))})).finally((function(){t.loading=!1})))}},mounted:function(){var t=this;this.fetchData(),this.timer=setInterval((function(){t.fetchData()}),3e4)},destroyed:function(){this.timer&&clearInterval(this.timer)}},B=z,G=Object(j["a"])(B,H,F,!1,null,null,null),Q=G.exports,W=function(){var t=this,e=t.$createElement,r=t._self._c||e;return r("div",{directives:[{name:"loading",rawName:"v-loading",value:t.loading,expression:"loading"}],staticStyle:{"min-height":"200px"},attrs:{id:"tab-log"}},[t.dateList&&t.dateList.length>0?[r("el-select",{attrs:{placeholder:"Please choose a store"},model:{value:t.currentDate,callback:function(e){t.currentDate=e},expression:"currentDate"}},t._l(t.dateList,(function(t){return r("el-option",{key:t,attrs:{label:t,value:t}})})),1),t.currentDate?r("iframe",{staticStyle:{width:"100%",height:"500px",border:"0.5px solid #eee","margin-top":"10px"},attrs:{src:t.logViewSrc}}):t._e()]:t._e(),t.dateList&&0===t.dateList.length?r("div",[t._v("There's not any logs yet.")]):t._e()],2)},X=[],Z={data:function(){return{loading:!1,dateList:null,currentDate:null}},computed:{logViewSrc:function(){return this.currentDate?"".concat(window.datacueURLs.getLogView,"?date=").concat(this.currentDate):""}},methods:{fetchDateList:function(){var t=this;window.datacueURLs&&(this.loading=!0,_({url:window.datacueURLs.getLogDateList,method:"get"}).then((function(e){t.dateList=e.data,t.dateList.length>0&&(t.currentDate=t.dateList[0])})).finally((function(){t.loading=!1})))}},mounted:function(){this.fetchDateList()}},tt=Z,et=Object(j["a"])(tt,W,X,!1,null,null,null),rt=et.exports;function nt(t,e){var r=Object.keys(t);if(Object.getOwnPropertySymbols){var n=Object.getOwnPropertySymbols(t);e&&(n=n.filter((function(e){return Object.getOwnPropertyDescriptor(t,e).enumerable}))),r.push.apply(r,n)}return r}function ot(t){for(var e=1;e<arguments.length;e++){var r=null!=arguments[e]?arguments[e]:{};e%2?nt(r,!0).forEach((function(e){Object(l["a"])(t,e,r[e])})):Object.getOwnPropertyDescriptors?Object.defineProperties(t,Object.getOwnPropertyDescriptors(r)):nt(r).forEach((function(e){Object.defineProperty(t,e,Object.getOwnPropertyDescriptor(r,e))}))}return t}var at={name:"app",components:{Settings:k,Recommendations:A,CustomCss:V,SyncStatus:Q,Log:rt},data:function(){return{activeTab:"settings"}},computed:ot({},Object(d["d"])({storeList:function(t){return t.storeList},currentStoreId:function(t){return t.currentStoreId}}),{},Object(d["c"])({pageReady:"pageReady"})),methods:ot({},Object(d["b"])(["fetchStoreList","setCurrentStoreId"]),{handleClick:function(){}}),mounted:function(){this.fetchStoreList()}},it=at,st=(r("aca3"),Object(j["a"])(it,c,u,!1,null,"2cbad317",null)),ct=st.exports;n["default"].use(d["a"]);var ut=new d["a"].Store({state:{storeList:null,currentStoreId:null},mutations:{setStoreList:function(t,e){t.storeList=e,t.storeList.length>0&&(t.currentStoreId=t.storeList[0].website_id)},setCurrentStoreId:function(t,e){t.currentStoreId=e}},actions:{fetchStoreList:function(t){var e=t.commit;window.datacueURLs&&_({url:window.datacueURLs.getWebsiteList,method:"post"}).then((function(t){e("setStoreList",t.data)}))},setCurrentStoreId:function(t,e){var r=t.commit;r("setCurrentStoreId",e)}},getters:{pageReady:function(t){return null!==t.storeList}}});n["default"].config.productionTip=!1,n["default"].use(a.a,{locale:s.a});var lt=function(){new n["default"]({store:ut,render:function(t){return t(ct)}}).$mount("#datacue-app")};document.ready?document.ready(lt):lt()},aca3:function(t,e,r){"use strict";var n=r("ba2c"),o=r.n(n);o.a},ba2c:function(t,e,r){}});