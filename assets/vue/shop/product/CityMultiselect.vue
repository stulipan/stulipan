<template>
    <div>
        <multiselect v-model="value" :options="options"></multiselect>
    </div>
</template>

<!--NINCS HASZNALATBAN-->
<!--NINCS HASZNALATBAN-->
<!--NINCS HASZNALATBAN-->

<script>
    import Multiselect from 'vue-multiselect'

    export default {
        components: {
            Multiselect
        },
        data () {
            return {
                value: null,
                options: ['list', 'of', 'options']
            }
        }
    }
</script>

<style scoped>
</style>