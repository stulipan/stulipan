<template>
    <div>
        <geo-price-manager
                :backUrl="backUrl"
        ></geo-price-manager>
    </div>
</template>

<script>
    import GeoPriceManager from './GeoPriceManager.vue'

    export default {
        props: [
            'backUrl',
        ],
        components: {
            GeoPriceManager
        },
    }
</script>

<style scoped>
</style>