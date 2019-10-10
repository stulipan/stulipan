import Vue from 'vue'
import VueResource from 'vue-resource'
import Vuelidate from 'vuelidate'

import Product from './product/Product.vue'

// import BootstrapVue from 'bootstrap-vue'
//
// Vue.use(BootstrapVue)

Vue.use(VueResource)
Vue.use(Vuelidate)

Vue.http.options.root = 'http://stulipan.dfr';

if (document.getElementById('v--checkout')) {
    Vue.component('checkout', require('./../shop/checkout/Checkout.vue').default );

    new Vue({
        el: '#v--checkout',
    });
}

if (document.getElementById('v--whereTo')) {
    new Vue({
        el: '#v--whereTo',
        template: '<Product/>',
        components: { Product },
    });
}

// var product = new Vue({
//     el: '#V--whereTo',
//     template: '<Product/>',
//     components: { Product },
// });