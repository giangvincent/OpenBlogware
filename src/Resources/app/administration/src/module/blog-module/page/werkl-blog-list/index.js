import template from './werkl-blog-list.twig';
import './werkl-blog-list.scss';

const { Mixin } = Shopware;
const { Criteria } = Shopware.Data;

export default {
    template,

    inject: [
        'repositoryFactory',
        'acl',
        'feature',
        'systemConfigApiService',
        'cmsPageTypeService',
    ],

    mixins: [
        Mixin.getByName('salutation'),
        Mixin.getByName('listing'),
        Mixin.getByName('notification'),
        Mixin.getByName('user-settings'),
    ],

    data() {
        return {
            categoryId: null,
            blogEntries: null,
            total: 0,
            currentLanguageId: Shopware.Store.get('context').api.languageId,
            isLoading: false,
            cardViewIdentifier: 'grid.cms.sw-cms-list-grid',
            sortBy: 'createdAt',
            sortDirection: 'DESC',
            associationLimit: 25,
            term: '',
        };
    },

    metaInfo() {
        return {
            title: this.$createTitle(),
        };
    },

    created() {
        this.getList();
    },

    computed: {
        blogEntriesRepository() {
            return this.repositoryFactory.create('werkl_blog_entries');
        },

        blogCategoryRepository() {
            return this.repositoryFactory.create('werkl_blog_category');
        },

        dateFilter() {
            return Shopware.Filter.getByName('date');
        },

        columns() {
            return [
                {
                    property: 'title',
                    dataIndex: 'title',
                    label: this.$t('werkl-blog.list.table.title'),
                    routerLink: 'blog.module.detail',
                    primary: true,
                    inlineEdit: 'string',
                },
                {
                    property: 'author',
                    label: this.$t('werkl-blog.list.table.author'),
                    inlineEdit: false,
                },
                {
                    property: 'publishedAt',
                    label: this.$t('werkl-blog.list.table.publishedAt'),
                    inlineEdit: false,
                },
                {
                    property: 'active',
                    label: this.$t('werkl-blog.list.table.active'),
                    inlineEdit: 'boolean',
                },
            ];
        },
    },

    methods: {
        changeLanguage(newLanguageId) {
            this.currentLanguageId = newLanguageId;
            this.getList();
        },

        changeCategoryId(categoryId) {
            if (categoryId && categoryId !== this.categoryId) {
                this.categoryId = categoryId;
                this.getList();
            }
        },

        async getList() {
            this.isLoading = true;
            const listCriteria = new Criteria(this.page, this.limit);
            listCriteria.addAssociation('blogAuthor');
            listCriteria.addAssociation('blogCategories');
            listCriteria.addAssociation('tags');

            listCriteria.addSorting(Criteria.sort('publishedAt', 'DESC', false));

            if (this.categoryId) {
                listCriteria.addFilter(Criteria.equals('blogCategories.id', this.categoryId));
            }
            const criteria = await this.addQueryScores(this.term, listCriteria);


            return this.blogEntriesRepository.search(criteria, Shopware.Store.get('context').api).then((result) => {
                this.total = result.total;
                this.blogEntries = result;
                this.isLoading = false;

                return this.blogEntries;
            }).catch(() => {
                this.isLoading = false;
            });
        },
    },
};
