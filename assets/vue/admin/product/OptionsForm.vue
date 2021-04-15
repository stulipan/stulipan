<template>
    <div>
        <div v-if="isOptionNameDuplicated" class="form-group row">
            <div class="col-sm-12">
                <div class="alert alert-danger">A változó neve nem egyedi!</div>
            </div>
        </div>

        <template v-for="(option, index) in options">
            <div class="form-group form-row">
                <div class="col-md-12 mb-2">
                    <div class="d-flex justify-content-between">
                        <div class="">Option {{index + 1}}</div>
                        <div v-if="!option.values.length" class="admin-card-topright">
                            <a @click.prevent="removeOption(index)" role="button" href="" class="">Remove</a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 form-group">
                    <input v-model="options[index].name" type="text" :id="`option_modal_${index}`" required="required" placeholder="Eg.: Size" class="form-control" ref="optionFields">
                </div>
                <div class="col-md-8 form-group">
                    <multiselect
                            v-model="options[index].values"
                            :options="options[index].values"
                            :selected="options[index].values"
                            label="value"
                            :taggable="true"
                            :multiple="true"
                            :hideSelected="true"
                            track-by="value"
                            @tag="addOptionItem"
                            @remove="removeOptionItem"
                            :id="index"
                            :placeholder="!options[index].values.length ? 'Type the value then press Enter' : null "
                            :max-height="0"
                            :closeOnSelect="false"
                            class="taggable"
                            :disabled="isOptionNameDuplicated"
                    >
                    </multiselect>
                </div>
            </div>
        </template>
        <template v-if="options.length < 3">
            <div class="form-group form-row">
                <div class="col-md-12">
                    <button @click.prevent="createOption" class="btn btn-secondary" role="button">Add another option</button>
                </div>
            </div>
        </template>
    </div>
</template>

<script>
    import Multiselect from 'vue-multiselect'

    export default {
        components: { Multiselect },
        props: [
            'options',
            // 'hasOptions',
        ],
        data: function () {
            return {
            }
        },
        computed: {
            isOptionNameDuplicated: function () {
                let names = [];
                // returns position of duplicate, -1 if not found
                const firstDuplicateIndex = list => list.findIndex(
                    (item, index) => list.lastIndexOf(item) !== index
                );

                if (this.options.length > 1) {
                    this.options.forEach(o => names.push(o.name))
                    return (firstDuplicateIndex(names) === -1) ? false : true;
                }
                return false;
            }
        },
        watch: {
            // options: {
            //     immediate: true,
            //     deep: true,
            //     handler() {
            //         this.extractOptionListFromVariants();
            //     }
            // },
        },
        methods: {
            addOptionItem(value, index) {
                let optionItem = {
                    id: null,
                    value: value,
                    position: this.options[index].values.length + 1,
                };
                this.options[index].values.push(optionItem);
                this.$emit('addOptionItem', {
                    option: this.options[index],
                    optionValue: optionItem,
                });
            },
            removeOptionItem(value, index) {
                this.$emit('removeOptionItem', {
                    option: this.options[index],
                    optionValue: value,
                });
            },
            createOption () {
                if (this.options.length < 3) {
                    let option = {
                        name: '',
                        position: null,
                        values: [],
                    };
                    option.position = this.options.length + 1;

                    // Es csak most bovitem a options listat, az uj ures option-nal
                    this.options.push(option);
                    this.$nextTick(() => {this.$refs.optionFields[this.options.length-1].focus();});
                }
            },
            removeOption (index) {
                this.options.splice(index, 1);
            },
            removeEmptyOptions() {
                while(this.options.findIndex((el) => el.values.length === 0) !== -1) {
                    this.options.splice(this.options.findIndex((el) => el.values.length === 0), 1)
                }
            },
        },
        created () {
            !this.options.length && this.createOption();
        },
       mounted() {
       }
    }
</script>

<style scoped="">
</style>