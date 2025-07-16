const t='<div> <template v-for="componentSection in componentSections"> <slot v-bind="{ componentSection }"/> </template> </div>',{State:e}=Shopware,n={template:t,props:{positionIdentifier:{type:String,required:!0}},computed:{componentSections(){return e.get("extensionComponentSections").identifier[this.positionIdentifier]??[]}}};export{n as default};
//# sourceMappingURL=index-Coyunyny.js.map
