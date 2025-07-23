export default {
    methods: {
        async createdComponent() {
            Shopware.Store.get('adminMenu').collapseSidebar();

            const isSystemDefaultLanguage = Shopware.Store.get('context').isSystemDefaultLanguage;
            if (!isSystemDefaultLanguage) {
                Shopware.Store.get('context').resetLanguageToDefault();
                this.$store.commit('cmsPageState/setIsSystemDefaultLanguage', isSystemDefaultLanguage);
            }

            if (Shopware.Store.get('context').api.languageId !== Shopware.Store.get('context').api.systemLanguageId) {
                this.$store.commit('context/setApiLanguageId', Shopware.Store.get('context').api.languageId);
            }

            this.resetCmsPageState();

            this.createPage();
            this.createBlog(this.page.id);
            this.loadCMSDataResolver();
            this.isLoading = false;

            this.setPageContext();
        },

        createBlog(pageId) {
            this.blog = this.blogRepository.create();
            this.blog.cmsPageId = pageId;
            this.blogId = this.blog.id;
        },
    },
};
