import template from './sw-cms-el-config-blog.html.twig';
import './sw-cms-el-config-blog.scss';

const { Mixin } = Shopware;
const { EntityCollection, Criteria } = Shopware.Data;

export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    data() {
        return {
            categories: [],
            selectedCategories: null,
        };
    },
    computed: {
        blogCategoryRepository() {
            return this.repositoryFactory.create('werkl_blog_category');
        },

        blogListingSelectContext() {
            const context = Object.assign({}, Shopware.Store.get('context').api);
            context.inheritance = true;

            return context;
        },

        blogCategoriesConfigValue() {
            return this.element.config.blogCategories.value;
        },
    },

    watch: {
        selectedCategories: {
            handler(value) {
                this.element.config.blogCategories.value = value.getIds();
                this.$set(this.element.data, 'blogCategories', value);
                this.$emit('element-update', this.element);
            },
        },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.initElementConfig('blog');
            await this.getSelectedCategories();
        },

        getSelectedCategories() {
            if (!Shopware.Utils.types.isEmpty(this.blogCategoriesConfigValue)) {
                const criteria = new Criteria();
                criteria.setIds(this.blogCategoriesConfigValue);

                this.blogCategoryRepository
                    .search(criteria, Shopware.Store.get('context').api)
                    .then((result) => {
                        this.selectedCategories = result;
                    });
            } else {
                this.selectedCategories = new EntityCollection(
                    this.blogCategoryRepository.route,
                    this.blogCategoryRepository.schema.entity,
                    Shopware.Store.get('context').api,
                    new Criteria(),
                );
            }
        },
    },
};
