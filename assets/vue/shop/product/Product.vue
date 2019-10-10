<template>
    <div>
        <product-manager></product-manager>
    </div>
</template>

<script>
    import ProductManager from './ProductManager.vue'

    export default {
        components: {
            ProductManager
        },
//        computed: {
//            name: function () {
//                return "Ez egy Vue welcome!";
//            }
//        },
        data: function () {
            return {
                name: "welcome"
            }
        },
    }
</script>

<style scoped>
</style>