import Vue from 'vue'
import VueResource from 'vue-resource'

import VueClip from 'vue-clip'
import VModal from 'vue-js-modal'

import BootstrapVue from 'bootstrap-vue'
import VueCarousel from 'vue-carousel'

import Product from './../admin/product/Product.vue'
import Category from './../admin/category/Category.vue'
import GeoPrice from './../admin/geoprice/GeoPrice.vue'


Vue.use(VueResource)
Vue.use(VueClip)
Vue.use(VModal, { dialog: true });
Vue.use(BootstrapVue)
Vue.use(VueCarousel)

// import 'bootstrap/dist/css/bootstrap.css'
// import 'bootstrap-vue/dist/bootstrap-vue.css'

Vue.http.options.root = 'http://stulipan.dfr'

if (document.getElementById('v--product')) {
    Vue.component('product', require('./../admin/product/Product.vue').default );

    new Vue({
        el: '#v--product',
        // template: '<Product/>',
        // components: { Product },
    });
}

if (document.getElementById('v--productCategory')) {
    new Vue({
        el: '#v--productCategory',
        template: '<Category/>',
        components: { Category },
    });
}

if (document.getElementById('v--geoPrice')) {
    new Vue({
        el: '#v--geoPrice',
        template: '<GeoPrice/>',
        components: {GeoPrice},
    });
}