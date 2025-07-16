const{Criteria:t}=Shopware.Data,o={computed:{globalCategoryRepository(){return this.repositoryFactory.create("werkl_blog_category")}},methods:{searchCategories(r){const e=new t(1,500);return e.setTerm(r),this.globalCategoryRepository.search(e,Shopware.Context.api)}}};export{o as default};
//# sourceMappingURL=index-CRZB28V4.js.map
