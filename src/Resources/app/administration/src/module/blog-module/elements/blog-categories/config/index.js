import template from './sw-cms-el-config-categories.html.twig';

const { Mixin } = Shopware;
// const { EntityCollection, Criteria } = Shopware.Data;

export default {
    template,

    inject: ['repositoryFactory'],

    mixins: [
        Mixin.getByName('cms-element'),
    ],

    data() {
        return {
            // categories: [],
            // selectedCategories: null,
        };
    },
    computed: {
      
        // blogListingSelectContext() {
        //     const context = Object.assign({}, Shopware.Context.api);
        //     context.inheritance = true;

        //     return context;
        // },

        // blogCategoriesConfigValue() {
        //     return this.element.config.blogCategories.value;
        // },
    },

    watch: {
        // selectedCategories: {
        //     handler(value) {
        //         this.element.config.blogCategories.value = value.getIds();
        //         this.$set(this.element.data, 'blogCategories', value);
        //         this.$emit('element-update', this.element);
        //     },
        // },
    },

    created() {
        this.createdComponent();
    },

    methods: {
        async createdComponent() {
            this.initElementConfig('blog-categories');
            // await this.getSelectedCategories();
        },

        getSelectedCategories() {
            // if (!Shopware.Utils.types.isEmpty(this.blogCategoriesConfigValue)) {
            //     const criteria = new Criteria();
            //     criteria.setIds(this.blogCategoriesConfigValue);

            //     this.blogCategoryRepository
            //         .search(criteria, Shopware.Context.api)
            //         .then((result) => {
            //             this.selectedCategories = result;
            //         });
            // } else {
            //     this.selectedCategories = new EntityCollection(
            //         this.blogCategoryRepository.route,
            //         this.blogCategoryRepository.schema.entity,
            //         Shopware.Context.api,
            //         new Criteria(),
            //     );
            // }
        },
    },
};
