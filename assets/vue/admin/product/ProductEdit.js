import Vue from 'vue'
import VueResource from 'vue-resource'

import VueClip from 'vue-clip'

Vue.use(VueResource);
Vue.use(VueClip);   // Simple and hackable file uploader

Vue.http.options.root = 'http://stulipan.dfr';
const uploadUrls = {
    productImage: '/hu/admin/api/images/product/',
};

// if (document.getElementById('v--product')) {
    Vue.component('product', require('./ProductEdit.vue').default );

    new Vue({
        el: '#v--product',
        // template: '<Product/>',
        // components: { Product },
    });
// }