<template>
    <div class="py-2 fixed-bottom bg-admin--saveToolbar">
        <div class="container d-flex justify-content-end">
            <div class="mr-2">
                <!--@click.prevent="onCancel" -->
                <a :href="entityToWatch.backToList" v-if="entityToWatch" class="btn" :class="[options.buttonCancelCss]">{{ options.cancelText }}</a>
                <button @click.prevent="onSubmit"
                
                        type="submit" class="btn" :class="[options.buttonSubmitCss]" formnovalidate="">
                    {{ entityToWatch ? options.updateText : options.createText }} <i v-if="formIsPosting" class="fas fa-lg fa-spinner fa-spin ml-1"></i>
                </button>
            </div>
        
        </div>
    </div>
</template>

<script>
    const initialData = () => {
        return {
            options: {
                createText: 'Hozzáadás',
                updateText: 'Adatok mentése',
                cancelText: 'Mégse',
                buttonSubmitCss: 'btn-info',
                buttonCancelCss: 'btn-secondary',
            }
        }
    }
    
    export default {
        props: [
            'formIsPosting',    //mandatory
            'entityToWatch',    //mandatory
            'cancelUrl',        //mandatory
            'createText',
            'updateText',
            'cancelText',
            'buttonSubmitCss',
            'buttonCancelVariant',
        ],
        data: initialData,
        methods: {
            onSubmit (e) {
                this.$emit('submit');
            },
            onCancel () {
                this.$emit('click')
            }
        },
        mounted () {
            if (this.createText) { this.options.createText = this.createText; }
            if (this.updateText) { this.options.updateText = this.updateText; }
            if (this.cancelText) { this.options.cancelText = this.cancelText; }
            if (this.buttonSubmitCss) { this.options.buttonSubmitCss = this.buttonSubmitCss; }
            if (this.buttonCancelCss) { this.options.buttonCancelCss = this.buttonCancelCss; }
        }
    }
</script>

<style scoped>
</style>