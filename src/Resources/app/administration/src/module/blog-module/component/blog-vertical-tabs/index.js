import template from './blog-vertical-tabs.html.twig';

export default {
    template,

    props: {
        defaultItem: {
            type: String,
            default: 'blog',
        },
    },

    data() {
        return {
            blogTabs: [
                {
                    'label': this.$t('werkl-blog.general.mainMenuItemList'),
                    'name': 'blog',
                }, {
                    'label': this.$t('werkl-blog-author.general.mainMenuItemList'),
                    'name': 'author',
                }]
        };
    },

    methods: {
        onChangeTab(name) {
            this.currentTab = name;
            if (name === 'blog') {
                this.$router.push({name: 'blog.module.index'})
            }

            if (name === 'author') {
                this.$router.push({name: 'blog.module.author.index'})
            }
        },
    },
};
