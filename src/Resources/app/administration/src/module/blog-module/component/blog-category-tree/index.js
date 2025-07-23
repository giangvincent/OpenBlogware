import template from './werkl-blog-category-tree.html.twig';

export default {
    template,

    data() {
        return {
            blogCategory: null,
            translationContext: 'werkl-blog-category',
        };
    },

    methods: {
        changeCategory(category) {
            this.$emit('change-category-id', category.id);
        },
    },

    computed: {
        category() {
            return this.blogCategory;
        },

        categoryRepository() {
            return this.repositoryFactory.create('werkl_blog_category');
        },

        disableContextMenu() {
            if (!this.allowEdit) {
                return true;
            }

            return this.currentLanguageId !== Shopware.Store.get('context').api.systemLanguageId;
        },
        syncProducts() {
            return;
        },
    },
};
