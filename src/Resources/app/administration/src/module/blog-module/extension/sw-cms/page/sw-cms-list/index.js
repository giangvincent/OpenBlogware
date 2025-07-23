export default {
    computed: {
        sortPageTypes() {
            const sortPageTypes = this.$super('sortPageTypes');

            sortPageTypes.push({
                value: 'blog_detail',
                name: this.$t('sw-cms.sorting.labelSortByBlogPages'),
            });

            return sortPageTypes;
        },
    },
};
