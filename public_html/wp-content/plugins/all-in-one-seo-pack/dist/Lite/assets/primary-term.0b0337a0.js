import{_}from"./js/_plugin-vue_export-helper.abeb2ae0.js";import{x as m,o as w,c as h,C as p,a as u,H as g,b as T,Y as S}from"./js/vue.runtime.esm-bundler.4a881941.js";import{l as x}from"./js/index.5d3e32c8.js";import{l as $}from"./js/index.43a192b7.js";import{l as v}from"./js/index.0b123ab1.js";import{l as d,u as E,q as f,b as k,v as b}from"./js/links.7c59a081.js";import{e as P}from"./js/elemLoaded.9a6eb745.js";import{s as A}from"./js/metabox.09bcc5b8.js";import{S as B}from"./js/Information.8e84b099.js";import{S as L}from"./js/Caret.02d7c74a.js";import"./js/translations.6e7b2383.js";import"./js/default-i18n.3881921e.js";import"./js/constants.238e5b7b.js";import"./js/isArrayLikeObject.ab8f4241.js";const C={getTerms:async e=>{const{apiFetch:t}=window.wp,{addQueryArgs:o}=window.wp.url,r=c(e);return await t({path:o(`/wp/v2/${r.restBase}`,{per_page:-1,orderby:"count",order:"desc",_fields:"id,name"})})},getSelectedTerms:e=>{const t=c(e);return window.wp.data.select("core/editor").getEditedPostAttribute(t.restBase)||[]}},D={getTerms:async e=>{var n;const t=[],o=c(e);return(((n=document.getElementById(`${o.name}checklist`))==null?void 0:n.querySelectorAll("li"))||[]).forEach(s=>{const a=s.querySelector("input").value,i=s.querySelector("label").innerText;t.push({id:parseInt(a,10),name:i.trim()})}),new Promise(s=>{s(t)})},getSelectedTerms:e=>{var n;const t=[],o=c(e);return(((n=document.getElementById(`${o.name}checklist`))==null?void 0:n.querySelectorAll("input:checked"))||[]).forEach(s=>{t.push(parseInt(s.value,10))}),t}},l=()=>{var o;return d(),(((o=E().aioseo.postData)==null?void 0:o.taxonomies)||[]).filter(r=>r.primaryTermSupport===!0)},I=e=>l().some(t=>e===t.name),c=e=>{const t=l().filter(o=>e===o.name);return t.length?t[0]:{}},M=e=>f()?C.getSelectedTerms(e):D.getSelectedTerms(e);const H={setup(){return{postEditorStore:k()}},components:{SvgCircleInformation:B,SvgClose:L},data(){return{selectedTerms:[],strings:{didYouKnow:this.$t.sprintf(this.$t.__("Did you know that %1$s Pro allows you to choose a %2$sprimary category%3$s for your posts? This feature works hand in hand with our powerful Breadcrumbs template to give you full navigational control to help improve your search rankings!",this.$td),"AIOSEO","<strong>","</strong>"),learnMoreLink:this.$t.sprintf('<a href="%1$s" target="_blank" rel="noreferrer nofollow">%2$s<span class="link-right-arrow">&nbsp;&rarr;</span></a>',this.$links.getDocUrl("primaryTerm"),this.$t.__("Learn more",this.$td))}}},props:{taxonomy:String},methods:{updateSelectedTerms(){this.selectedTerms=M(this.taxonomy)}},computed:{canShowUpsell(){const{options:e}=this.postEditorStore.currentPost;return!e.primaryTerm.productEducationDismissed&&1<this.selectedTerms.length}},mounted(){this.updateSelectedTerms(),window.aioseoBus.$on("updateSelectedTerms",this.updateSelectedTerms)},beforeUnmount(){window.aioseoBus.$off("updateSelectedTerms",this.updateSelectedTerms)}},q={key:0,class:"aioseo-primary-term-cta"},N=["innerHTML"],U=["innerHTML"];function V(e,t,o,r,n,s){const a=m("svg-circle-information"),i=m("svg-close");return s.canShowUpsell?(w(),h("div",q,[p(a,{width:"15",height:"15"}),u("p",{innerHTML:n.strings.didYouKnow},null,8,N),u("p",{innerHTML:n.strings.learnMoreLink},null,8,U),p(i,{onClick:g(r.postEditorStore.disablePrimaryTermEducation,["stop"])},null,8,["onClick"])])):T("",!0)}const F=_(H,[["render",V]]);const Y={components:{PrimaryTerm:F},props:{taxonomy:String}},K={class:"aioseo-app aioseo-primary-term"};function O(e,t,o,r,n,s){const a=m("primary-term");return w(),h("div",K,[p(a,{taxonomy:o.taxonomy},null,8,["taxonomy"])])}const Q=_(Y,[["render",O]]);if(f()&&window.wp){const{createElement:e,Fragment:t}=window.wp.element,{addFilter:o}=window.wp.hooks,{createHigherOrderComponent:r}=window.wp.compose,{subscribe:n}=window.wp.data;o("editor.PostTaxonomyType","aioseo/primary-term",r(s=>a=>{const{slug:i}=a;return I(i)?e(t,{},e(s,a),e("div",{id:`aioseo-primary-term-${i}`},e("div",{className:"aioseo-primary-term-app",taxonomy:i}))):e(s,a)},"withInspectorControls")),n(()=>{window.aioseoBus.$emit("updateSelectedTerms")})}b()&&(d(),l().forEach(e=>{const t=document.querySelector(`#${e.name}div .inside`);if(!t)return;const o=document.createElement("div");o.setAttribute("id",`aioseo-primary-term-${e.name}`),o.setAttribute("class","aioseo-primary-term-app"),o.setAttribute("taxonomy",e.name),t.append(o),function(r){r(`#${e.name}checklist`).on("change",'input[type="checkbox"]',()=>{window.aioseoBus.$emit("updateSelectedTerms")}),r(`#${e.name}checklist`).on("wpListAddEnd",()=>{window.aioseoBus.$emit("updateSelectedTerms")})}(window.jQuery)}));const y=e=>{if(!e)return;const t=e.getAttribute("taxonomy");let o=S({...Q,name:"Standalone/PrimaryTerm"},{taxonomy:t});o=x(o),o=$(o),o=v(o),d(o),o.mount(e)};if(A()&&window.aioseo&&window.aioseo.currentPost&&window.aioseo.currentPost.context==="post"){const e=document.getElementsByClassName("aioseo-primary-term-app");Array.prototype.forEach.call(e,t=>y(t)),P(".aioseo-primary-term-app","aioseoPrimaryTerm"),document.addEventListener("animationstart",function(t){t.animationName==="aioseoPrimaryTerm"&&y(t.target)},{passive:!0})}
