const cmsPageTypeService = Shopware.Service('cmsPageTypeService');
import BLOG from '../module/blog-module/constant/open-blogware.constant';

cmsPageTypeService.register({
    name: BLOG.PAGE_TYPES.BLOG_DETAIL,
    icon: 'regular-file-text',
});
