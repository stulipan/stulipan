<template>
    <a @click.prevent="onRemove" href="" class="cart-remove text--toolLink">
        <template v-if="isDeleting">
            <i class="fas fa-lg fa-spinner fa-spin"></i>
        </template>
        <template v-else>
            <i class="far fa-trash-alt"></i>
        </template>
        
    </a>
</template>

<script>
    const initialData = () => {
        return {
            options: {
                createText: 'Hozzáadás',
                updateText: 'Adatok mentése',
                cancelText: 'Mégse',
                buttonSubmitCss: 'btn-success',
                buttonCancelCss: 'btn-outline-secondary',
            },
            isDeleting: false,
        }
    }
    
    export default {
        props: [
            'isEntryDeleting',  //mandatory
        ],
        data: initialData,
        methods: {
            onRemove () {
                this.isDeleting = true;
                this.$emit('onRemove');
            },
        },
        mounted () {
//            if (this.createText) { this.options.createText = this.createText; }
//            if (this.updateText) { this.options.updateText = this.updateText; }
//            if (this.cancelText) { this.options.cancelText = this.cancelText; }
//            if (this.buttonSubmitCss) { this.options.buttonSubmitCss = this.buttonSubmitCss; }
//            if (this.buttonCancelCss) { this.options.buttonCancelCss = this.buttonCancelCss; }
        }
    }
</script>

<style scoped>
</style>