<template>
    <div class="">
        <div class="form-group row">
            <div class="col-sm-12">
                <span class="mr-3">Select:</span>
                <template v-for="(option, i) in options">
                    <template v-for="(optionValue, i) in option.values">
                        <a href="javascript: void(0);" class="mr-3">{{ optionValue.value }}</a>
                    </template>
                </template>
            </div>
        </div>
        <div class="grid-preview grid-shadowX">
            <!--                                                        <div class="no-margins">-->
            <div class="grid-scroll">
                <div class="position-relative">
                    <div class="grid-template-container py-3 font-weight-bold" :style="{ '--variant-columns': variantColumns }">

                        <template v-if="isEditable">
                            <div v-for="(option, i) in options" class="grid-cell">{{ option.name }}</div>
                        </template>
                        <template v-else>
                            <div class="grid-cell">Variant</div>
                        </template>

                        <div class="grid-cell">Price</div>
                        <div class="grid-cell">Quantity</div>
                        <div class="grid-cell">SKU</div>
                        <div class="grid-cell">Barcode</div>
                    </div>
                    <div class="grid-shadowXXXX">
                        <ul class="">
                            <template v-for="(variant, i) in variants">
                                <li class="">
                                    <div class="variant-item">
                                        <div class="grid-template-container py-3" :style="{ '--variant-columns': variantColumns }">
                                            <template v-if="isEditable">
                                                <div v-for="(selected, j) in variant.selectedOptions" class="grid-cell mr-2">
                                                    <input v-model="selected.optionValue.value" :id="`${variant.name}_${i}_${selected.option}_${j}`" type="text" required="required" placeholder="" class="form-control">
                                                </div>
                                            </template>
                                            <template v-else>
                                                <div class="">
                                                    {{ variant.name }}
                                                </div>
                                            </template>

                                            <div class="grid-cell mr-2">
                                                <div class="field-item">
                                                    <div class="field-prepend">
                                                        <div class="field-addon-prepend w-addon-sm">HUF</div>
                                                    </div>
                                                    <input v-model="variant.price.numericValue" type="number" required="required" placeholder="0.00" class="pl-addon-sm form-control">
                                                </div>
                                            </div>
                                            <div class="grid-cell mr-2">
                                                <input v-model="variant.position" :id="`${variant.name}_quantity_${i}`" type="number" required="required" class="form-control">
                                            </div>
                                            <div class="grid-cell mr-2">
                                                <input v-model="variant.sku" :id="`${variant.name}_sku_${i}`" type="text" required="required" class="form-control">
                                            </div>
                                            <div class="grid-cell mr-2">

                                            </div>
                                            <div v-if="isEditable" class="grid-cell mr-2">
                                                <div class="btn-group" role="group" aria-label="Variant buttons">
                                                    <a href="#" class="btn btn-secondary">Edit</a>
                                                    <a href="#" class="btn btn-secondary"><i class="far fa-trash-alt"></i></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            </template>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        components: {  },
        props: [
            'variants',
            'options',
            'optionItems',
            'lastOptionItem',
            'price',
            'isEditable',

        ],
        data: function () {
            return {
                variantOptions: [],
                productOptions: this.options,
            }
        },
        computed: {
            optionItemsCount: function () {
                return this.optionItems.length;
            },
            variantColumns: function() {
                return this.isEditable
                    ? this.options.length ? this.options.length : 1
                    : 1 ;
            },
            // tempVariants: function () {
            //     return this.generateVariants()
            // }
        },
        watch: {
            // productOptions: {
            //     immediate: true,
            //     deep: true,
            //     handler() {
            //         this.extractOptionListFromVariants();
            //     }
            // },
            optionItemsCount: {
                handler() {
                    this.generateVariants();
                },
            },
        },
        methods: {
            extractOptionListFromVariants() {
                this.variantOptions.length = 0;
                if (this.variants.length) {
                    let v = this.variants[0];
                    for (let s of v.selectedOptions) {
                        this.variantOptions.push(this.productOptions.find(el => el.name === s.option.name));
                    }
                } else {
                    for (let o of this.productOptions) {
                        this.variantOptions.push(o);
                    }
                }
            },
            // isOptionInSelectedOptions(selectedOptions, selectedOption) {
            //     for (let i=0; i<selectedOptions.length; ++i) {
            //         if (selectedOptions[i].option.name === selectedOption.option.name) {
            //             return true;
            //         }
            //     }
            //     return false;
            // },
            // isOptionValueInSelectedOptions(selectedOptions, selectedOption) {
            //     for (let i=0; i<selectedOptions.length; ++i) {
            //         if (selectedOptions[i].optionValue.value === selectedOption.optionValue.value) {
            //             return true;
            //         }
            //     }
            //     return false;
            // },
            generateVariantName(selectedOptions) {
                let name = '';
                for (let s of selectedOptions) { name += name ? ' / ' + s.optionValue.value : s.optionValue.value; }
                return name;
            },
            generateVariants() {
                // function that returns all element combinations of n arrays
                const combine = function(args) {
                    var r = [], max = args.length-1;
                    function helper(arr, i) {
                        for (var j=0, l=args[i].length; j<l; j++) {
                            var a = arr.slice(0); // clone arr
                            a.push(args[i][j]);
                            if (i==max)
                                r.push(a);
                            else
                                helper(a, i+1);
                        }
                    }
                    helper([], 0);
                    return r;
                }

                let arrays = [];

                // the items of an option must go into distinct arrays
                let options = this.options.filter(o => o.values.length > 0);
                options.forEach(o => arrays.push(
                    this.optionItems.filter(oi=> oi.option.name === o.name)
                ));

                if (!options.length) {
                    // var tempVariants = [];
                    this.variants.length=0; 
                } else {
                    var tempVariants = combine(arrays); // array of arrays
                    var variants =  []; // array of objects

                    tempVariants.forEach(function(variant) {
                        let optionItems = [];
                        variant.forEach(optionItem => optionItems.push(optionItem));
                        variants.push({
                            name: this.generateVariantName(optionItems),
                            // name: 'sdf',
                            selectedOptions: optionItems,
                            position: this.variants.length + 1,
                            price: { numericValue: this.price ? this.price.numericValue : 0 },
                        });
                    }.bind(this));

                    this.variants.length=0;
                    this.variants.push(...variants);
                }
                // return tempVariants;
            },
        },
        created () {
            this.extractOptionListFromVariants();
        },
       mounted() {
       }
    }
</script>

<style scoped="">
</style>