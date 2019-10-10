<template>
    <div>
        <cart-manager
                :orderId="orderId"
                :giftCategoryId="giftCategoryId"
        ></cart-manager>
    </div>
</template>

<script>
    import CartManager from './CartManager.vue'

    export default {
        props: [
            'orderId',
            'giftCategoryId',
        ],
        components: {
            CartManager
        },
        data: function () {
            return {
//                name: "welcome"
            }
        },
    }
</script>

<style scoped>
</style>