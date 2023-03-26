import Vue from 'vue'
import VueResource from 'vue-resource'

import VueClip from 'vue-clip'
import StoreImageUpload from './StoreImageUpload.vue'

Vue.use(VueResource);
Vue.use(VueClip);   // Simple and hackable file uploader

Vue.http.options.root = 'http://stulipan.dfr';
const uploadUrls = {
    productImage: '/admin/api/upload/storeImage/',
};

new Vue({
    el: '#v--imageUpload',
    template: '<StoreImageUpload/>',
    components: {StoreImageUpload},
});
