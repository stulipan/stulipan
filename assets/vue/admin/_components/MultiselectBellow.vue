<template>
    <div>
        <multiselect
                :value="value"
                @input="updateValue"

                :id="id"
                :options="options"
                :multiple="multiple"
                :track-by="trackBy"
                :searchable="searchable"
                :clearOnSelect="clearOnSelect"
                :hideSelected="hideSelected"
                :placeholder="placeholder"
                :allowEmpty="allowEmpty"
                :resetAfter="resetAfter"
                :close-on-select="closeOnSelect"
                :custom-label="customLabel"
                :taggable="taggable"
                :tagPlaceholder="tagPlaceholder"
                :tagPosition="tagPosition"
                :max="max"
                :preserveSearch="preserveSearch"
                :showLabels="showLabels"
                :loading="loading"
                :disabled="disabled"
                :maxHeight="maxHeight"
                :open-direction="openDirection"
                :showNoResults="showNoResults"
                :tabindex="tabindex"
                :optionHeight="optionHeight"
                :class="[ className, {'mb-2': true}]"
        >
            <span slot="noResult">{{noResultLabel}}</span>
            <template slot="selection" slot-scope="{ values }">
                <span class="multiselect__placeholder" v-if="values.length">{{ placeholder }}</span>
            </template>
            <template slot="option" slot-scope="props">
                <div class="custom-control custom-checkbox">
                    <input type="checkbox" :checked="isSelected(props.option)" class="custom-control-input mr-2">
                    <label class="custom-control-label">{{ getOptionLabel(props.option) }}</label>
                </div>
            </template>
        </multiselect>
        <div class="">
            <div v-for="v in selectedOptions" class="filter-tag mb-1">
                {{v}}
                <a href="#" role="button" @click.prevent="removeOption" class="btn-link-secondary ml-2"><i class="fas fa-times mr-0"></i></a>
            </div>
        </div>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'
    import multiselectMixin from 'vue-multiselect'
    export default {
        name: 'multiselect-bellow',
        mixins: [multiselectMixin],
        components: {
            Multiselect,
        },
        props: {
            // id: [Number, String],
            // options: Array,
            // value: [Object, Array, String, Number],
            // multiple: Boolean,
            // trackBy: String,
            // clearOnSelect: {
            //     type: Boolean,
            //     default: true
            // },
            // hideSelected: {
            //     type: Boolean,
            //     default: false
            // },
            // placeholder: String,
            // allowEmpty: {
            //     type: Boolean,
            //     default: true
            // },
            // resetAfter: Boolean,
            // closeOnSelect: {
            //     type: Boolean,
            //     default: true
            // },
            // taggable: {
            //     type: Boolean,
            //     default: false
            // },
            // tagPlaceholder: String,
            // tagPosition: {
            //     type: String,
            //     default: 'top'
            // },
            // max: Number,
            // preserveSearch: Boolean,
            // showLabels: Boolean,
            // loading: Boolean,
            // disabled: Boolean,
            // maxHeight: Number,
            // openDirection: String,
            // showNoResults: Boolean,
            // tabindex: Number,
            // optionHeight: Number,

            noResultLabel: String,
            myClass: String,

            // /**
            //  * Function to interpolate the custom label
            //  * @default false
            //  * @type {Function}
            //  */
            // customLabel: {
            //     type: Function,
            //     default (option, label) {
            //         if (isEmpty(option)) return ''
            //         return label ? option[label] : option
            //     }
            // },
        },
        data: function () {
            return {
                className: this.myClass,
            }
        },
        computed: {
            selectedOptions() {
                return this.value.map(el => this.customLabel(el, this.label));
            },
        },
        watch: {
        },
        methods: {
            updateValue(value) {
                this.$emit('input', value);
            },
            selectOption() {
                this.$emit('select', this.value);
            },
            removeOption (option) {
                const index = typeof option === 'object'
                    ? this.value.indexOf(option[this.trackBy])
                    : this.value.indexOf(option);
                this.value.splice(index, 1);
            },
            isSelected(option) {
                const index = typeof option === 'object'
                    ? this.value.findIndex(el => el.id === option.id)
                    : this.value.indexOf(option);
                return -1 === index ? false : true;
            }
        },
       mounted() {
       }
    }
</script>

<style scoped>
</style>