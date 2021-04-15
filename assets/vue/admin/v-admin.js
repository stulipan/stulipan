import Vue from 'vue'
import VueResource from 'vue-resource'

import VueClip from 'vue-clip'
import VModal from 'vue-js-modal'

import Category from './../admin/category/Category.vue'
import CmsImageUpload from './../admin/_components/CmsImageUpload.vue'


Vue.use(VueResource);
Vue.use(VueClip);   // Simple and hackable file uploader
Vue.use(VModal, { dialog: true });

// import './../../css/v-theme.scss';

Vue.http.options.root = 'http://stulipan.dfr';
const uploadUrls = {
    productImage: '/hu/admin/api/images/product/',
};


if (document.getElementById('v--productCategory')) {
    new Vue({
        el: '#v--productCategory',
        template: '<Category/>',
        components: { Category },
    });
}

if (document.getElementById('v--geoPrice')) {
    Vue.component('geo-price', require('./../admin/geoprice/GeoPrice.vue').default );
    // A fentivel megegyezik
    // Vue.component('GeoPrice', require('./../admin/geoprice/GeoPrice.vue').default );

    new Vue({
        el: '#v--geoPrice',
        // template: '<GeoPrice/>',
        // components: {GeoPrice},
    });
}

// if (document.getElementById('v--imageUpload')) {
//     new Vue({
//         el: '#v--imageUpload',
//         template: '<CmsImageUpload/>',
//         components: {CmsImageUpload},
//     });
// }