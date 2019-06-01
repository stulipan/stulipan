<template>
    <product-manager
            :productId="productId">
    </product-manager>
</template>

<script>
    import ProductManager from './ProductManager'

    export default {
        props: [
            'productId',
        ],
        components: {
            ProductManager
        },
    }
</script>

<style scoped>
</style>