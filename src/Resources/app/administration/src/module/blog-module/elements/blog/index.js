const { Component } = Shopware;

Component.register('sw-cms-el-blog', () => import('./component'));
Component.register('sw-cms-el-config-blog', () => import('./config'));
Component.register('sw-cms-el-preview-blog', () => import('./preview'));

Shopware.Service('cmsService').registerCmsElement({
    name: 'blog',
    label: 'Blog',
    component: 'sw-cms-el-blog',
    configComponent: 'sw-cms-el-config-blog',
    previewComponent: 'sw-cms-el-preview-blog',
    defaultConfig: {
        paginationCount: {
            source: 'static',
            value: 5,
        },
        showType: {
            source: 'static',
            value: 'all',
        },
        showCategoryFilter: {
            source: 'static',
            value: true,
        },
        showAuthorFilter: {
            source: 'static',
            value: true,
        },
        blogCategories: {
            source: 'static',
            value: null,
            entity: {
                name: 'werkl_blog_categories',
            },
        },
        showTags: {
            source: 'static',
            value: 'all',
        },
        blogTags: {
            source: 'static',
            value: null,
            entity: {
                name: 'tag',
            },
        },
    },
});
