import Vue from 'vue'
import VueResource from 'vue-resource'
import Product from './product/Product.vue'

Vue.use(VueResource)

Vue.http.options.root = 'http://stulipan.dfr';

var product = new Vue({
    el: '#V--whereTo',
    template: '<Product/>',
    components: { Product },
});