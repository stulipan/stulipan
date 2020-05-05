import Vue from 'vue'
import VueResource from 'vue-resource'

import VueClip from 'vue-clip'
import CmsImageUpload from './CmsImageUpload.vue'

Vue.use(VueResource);
Vue.use(VueClip);   // Simple and hackable file uploader

Vue.http.options.root = 'http://stulipan.dfr';
const uploadUrls = {
    productImage: '/hu/admin/api/images/product/',
};

new Vue({
    el: '#v--imageUpload',
    template: '<CmsImageUpload/>',
    components: {CmsImageUpload},
});