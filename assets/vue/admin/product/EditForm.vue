<template>
    <div>
        <div v-if="product.hasVariants" class="modal fade" id="modal--editOptions" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="modalLabel">Edit options</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <options-form
                                :options=productOptions
                                v-on:addOptionItem=addVariant
                                v-on:removeOptionItem=removeVariant
                        ></options-form>
                    </div>
                    <div class="modal-footer">
                        <div class="loadingWrapper loadingWrapper-secondary">
                            <a href="" class="btn btn-secondary" data-dismiss="modal">Cancel</a>
                            <div class="JS--loadingOverlay d-flex align-items-stretch"></div>
                        </div>
                        <div class="loadingWrapper">
                            <button type="submit" class="JS--btn-submitStatus btn btn-primary">Done</button>
                            <div class="JS--loadingOverlay d-flex align-items-stretch"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <form novalidate="true" ref="form" enctype="multipart/form-data">
            <div v-if="localErrors.length" class="alert alert-danger mb-2">
                <b>Please correct the following error(s):</b>
                <ul>
                    <li v-for="error in localErrors">{{ error }}</li>
                </ul>
            </div>
            <div v-else-if="errors.length" class="alert alert-danger mb-2">
                <b>Please correct the following error(s):</b>
                <ul>
                    <li v-for="error in errors">{{ error }}</li>
                </ul>
            </div>

            <fieldset :disabled="formIsPosting" :class="{ disabled: formIsPosting }">
                <div class="row row-5">
                    <div class="col-md-8 px-0 px-md-10px">

                        <div class="card mb-20px">
                            <div class="card-wrapper">
                                <div class="card-body">
                                    <div id="---NAME ---DESCRIPTION">
                                        <div class="form-group">
                                            <label class="col-form-labelX required" for="productName">Termék neve</label>
                                            <input v-model="product.name" type="text" id="productName" required="required" maxlength="100" class="form-control">
                                        </div>
                                        <div class="form-group">
                                            <label class="col-form-labelX" for="description">Rövid ismertető</label>
                                            <textarea v-model="product.description" id="description" maxlength="65535" rows="5" class="form-control"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-20px">
                            <div class="card-wrapper">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between">
                                        <div class="h5 mb-4">Termékvariációk</div>
                                        <div v-if="product.hasVariants" class="admin-card-topright">
                                            <div class="dropdown">
                                                <a class="dropdown-toggle" href="javascript: void(0);" id="moreOptions" role="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">More options</a>
                                                <div class="dropdown-menu dropdown-transition mt-2" aria-labelledby="moreOptions">
                                                    <a class="dropdown-item" href="#" data-toggle="modal" data-target="#modal--editOptions" data-backdrop="static">
                                                        Edit options
                                                    </a>

                                                    <a class="dropdown-item" href="" data-toggle="modal" data-target="#modal--reorderOptions" data-backdrop="static">
                                                        Reorder options
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="---KIND ---TERMEK_OPTION">
                                        <!--  If hasVariants checkbox is checked and the Product has variants  -->
                                        <!--  Lists the variants  -->
                                        <template v-if="product.hasVariants">
                                            <div class="form-group rowX">
                                                <div class="col-sm-12X">
                                                    <variant-list
                                                            :variants="product.variants"
                                                            :options=productOptions
                                                            :option-items="optionItems"
                                                            :last-option-item="lastOptionItem"
                                                            :price="product.price"
                                                            :isEditable="true"
                                                    ></variant-list>
                                                </div>
                                            </div>
                                        </template>

                                        <template v-else>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" v-model="isVariantsCheckboxOn" id="hasVariants" name="hasVariants" required="required" class="custom-control-input">
                                                        <label class="custom-control-label required" for="hasVariants">This product has multiple options, like different sizes or colors</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <!--  If hasVariants checkbox is checked BUT the Product has NO variants  -->
                                                    <!--  Displays the product option form and the variants preview form -->
                                                    <template v-if="isVariantsCheckboxOn">
                                                        <div class="form-group">
                                                            <options-form
                                                                    :options=productOptions
                                                                    v-on:addOptionItem=addVariant
                                                                    v-on:removeOptionItem=removeVariant
                                                            ></options-form>
                                                        </div>
                                                        <div class="form-group rowX">
                                                            <div class="col-sm-12X">
                                                                <variant-list
                                                                    :variants="product.variants"
                                                                    :options=productOptions
                                                                    :option-items="optionItems"
                                                                    :last-option-item="lastOptionItem"
                                                                    :price="product.price"
                                                                    :is-editable="false"
                                                                ></variant-list>
                                                            </div>
                                                        </div>
                                                    </template>
                                                    <template v-else>
                                                        <div class="form-group row" id="---termekAr">
                                                            <div class="col-sm-12">
                                                                <label class="required" for="price_grossPrice">Termék ár</label>
                                                                <div class="field-item">
                                                                    <div class="field-prepend">
                                                                        <div class="field-addon-prepend w-addon-sm">HUF</div>
                                                                    </div>
                                                                    <input v-model="product.price.numericValue" type="number" id="price_grossPrice" required="required" placeholder="0.00" class="form-control pl-addon-sm">
                                                                </div>
                                                            </div>
                                                            <div class="col-sm-12 text-muted mt-2">
                                                                Ez lesz a Standard méret ára, amennyiben 3 meretű termékről van szó.
                                                            </div>
                                                        </div>
                                                    </template>
                                                </div>
                                            </div>
                                        </template>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="card mb-20px">
                            <div class="card-wrapper">
                                <div class="card-body">
                                    <div class="h5 admin-card-title mb-4">Képek</div>
                                    <div v-if="product.images" class="" id="---imagePreview">

                                        <div class="row">
                                            <div class="col-sm-12 mr-auto">

                                                <div class="row">
                                                    <div v-for="item in product.images" class="col-md-4 col-6">
                                                        <div class="product">
                                                            <div class="product-image">
                                                                <div :style="{ backgroundImage: 'url(' + item.imageUrl + ')' }" style="background: center center no-repeat;background-size: cover; height: 200px;" class="w-100">

                                                                </div>
                                                                <div v-if="item.ordering === '0' || item.ordering === 0" class="card-img-overlay d-flex align-items-end p-0">
                                                                    <div class="text-center w-100 overlay--productCover">
                                                                        <i class="fas fa-camera"></i> Borítókép
                                                                    </div>
                                                                </div>

                                                            </div>
                                                            <div class="py-2 text-center">
                                                                <p class="productList--name mb-1">
                                                                    <a @click.prevent="onRemoveImage(item)" href="#" class="btn btn-sm btn-secondary" title="Töröl">
                                                                        <i class="far fa-trash-alt"></i> Töröl
                                                                    </a>
                                                                </p>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                    <div class="" id="---KEP_FELTOLTES">
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <image-upload
                                                            :images=product.images
                                                            @error="onImageUploadError"
                                                    >
                                                    </image-upload>
                                                </div>
                                            </div>
                                        </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-20px">
                            <div class="card-wrapper">
                                <div class="card-body">
                                    <div class="h5 admin-card-title mb-4">Készlet</div>
                                    <div id="---SKU ---STOCK">
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <label class="required" for="sku">SKU (Stock Keeping Unit)</label>
                                                <input v-model="product.sku" type="text" id="sku" required="required" placeholder="Pl: DF100172" class="form-control">
                                            </div>
                                        </div>
                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <label class="required" for="stock">Készlet a raktáron</label>
                                                <input v-model="product.stock" type="number" id="stock" required="required" placeholder="" class="form-control">
                                                <!--<div class="input-group-append">-->
                                                <!--<span class="input-group-text">db</span>-->
                                                <!--</div>-->
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div name="__SIDEBAR" class="col-md-4 px-0 px-md-10px orderDetail--sidebarX">
                        <div class="card mb-20px">
                            <div class="">
                                <div class="card-body">
                                    <div class="h5">Kategorizálás</div>
<!--                                    <div id="-&#45;&#45;STATUS -&#45;&#45;CATEGORIES -&#45;&#45;BADGES">-->
                                        <div class="form-group row">
                                            <div class="col-sm-12 order-1">
                                                <legend class="col-form-label required">Állapot</legend>
                                            </div>
                                            <div class="col-sm-12 order-3 order-md-2">
<!--                                                <div class="form-row">-->
                                                <ul class="list-group list-group-flush list-group--status">
                                                    <template v-for="status in statuses">
<!--                                                        <div class="col-12 ">-->

                                                            <li class="list-group-item">
                                                                <div class="custom-control custom-radio">
                                                                    <input type="radio" v-model="product.status.id" :value="status.id" :id="`status_${status.id}`" required="required" class="custom-control-input">
                                                                    <label class="custom-control-label" :for="`status_${status.id}`">
                                                                        {{ status.name }}
                                                                    </label>
                                                                </div>
                                                            </li>

<!--                                                            <div class="vp vp-with-checkbox align-top mr-2 mb-2" :class="product.status.id == status.id ? 'vp-checked' : ''">-->
<!--                                                                <input type="radio" v-model="product.status.id" :value="status.id" :id="`status_${status.id}`" required="required">-->
<!--                                                                <label class="vp-figure required justify-content-start" :for="`status_${status.id}`">-->
<!--                                                                <span class="vp-content">-->
<!--                                                                    <span class="tile tile-lg">-->
<!--                                                                        <span>-->
<!--                                                                            <span class="text-nowrap"><span v-html="status.icon"></span>{{ status.name }}</span>-->
<!--                                                                        </span>-->
<!--                                                                    </span>-->
<!--                                                                </span>-->
<!--                                                                </label>-->
<!--                                                            </div>-->
<!--                                                        </div>-->
                                                    </template>
                                                </ul>
                                                    <div class="col-sm-12X text-muted">
                                                        <em>Kifutott</em> állapotban a termék továbbra is látszik a weboldalon, csupán nem lesz neki <em>Kosárba rakom</em> gomb.
                                                    </div>
<!--                                                </div>-->
                                            </div>
                                        </div>
                                </div>
                                <div class="card-footer">

                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <label class="required" for="category">Kategóriák</label>
                                                <multiselect-bellow
                                                        v-model="product.categories"
                                                        :options="categories"
                                                        :multiple="true"
                                                        :close-on-select="false"
                                                        :custom-label="showCategoryName"
                                                        placeholder="Válassz..."
                                                        track-by="id"
                                                        :showLabels="false"
                                                        myClass="detached"
                                                        open-direction="bottom"
                                                        no-result-label="Nincs ilyen opció..."
                                                        :max-height="150"
                                                >
                                                </multiselect-bellow>
                                            </div>
                                        </div>

                                </div>
                                <div class="card-footer">

                                        <div class="form-group row">
                                            <div class="col-sm-12">
                                                <label class="required" for="badge">Matricák</label>
                                                <multiselect-bellow
                                                        v-model="product.badges"
                                                        :options="badges"
                                                        :multiple="true"
                                                        :close-on-select="false"
                                                        :custom-label="showBadgeName"
                                                        placeholder="Válassz..."
                                                        track-by="id"
                                                        :showLabels="false"
                                                        myClass="detached"
                                                        open-direction="bottom"
                                                        no-result-label="Nincs ilyen opció..."
                                                        :max-height="150"
                                                >
                                                </multiselect-bellow>
                                            </div>
                                        </div>
<!--                                    </div>-->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <save-toolbar
                        :entityToWatch="product"
                        :formIsPosting="formIsPosting"
                        v-on:submit="onSubmit"
                        v-on:click="onCancel"
                        class="mb-4"
                ></save-toolbar>
            </fieldset>
        </form>
    </div>
</template>

<script>
    import MultiselectBellow from "./../_components/MultiselectBellow"
    import OptionsForm from "./OptionsForm";
    import SaveToolbar from './../_components/save-toolbar/SaveToolbar.vue'
    import ImageUpload from "./../_components/ImageUpload";
    import VariantList from "./VariantList";

    const initialData = () => {
        return {
        }
    };
    
    export default {
        components: {
            OptionsForm,
            VariantList,
            SaveToolbar,
            MultiselectBellow,
            ImageUpload,
        },
        props: [
            'formIsPosting',
            'product',
            'categories',
            'statuses',
            'badges',
            'errors',
            'resetForm',
        ],
        data: function () {
            return {
                config: {
                    btns: [
                        // ['undo', 'redo'], // Only supported in Blink browsers
                        ['formatting'],
                        ['strong', 'em', 'underline'],
                        ['unorderedList', 'orderedList'],
                        ['justifyLeft', 'justifyCenter', 'justifyRight', 'justifyFull'],
                        ['link'],
                        ['insertImage'],
                        ['removeformat'],
                        ['viewHTML'],
                    ],
                    autogrow: true,
                    changeActiveDropdownIcon: true,
                    tagsToRemove: ['script', 'link'],
                    imageWidthModalEdit: true,
                    removeformatPasted: true,
                },

                localErrors: [],

                isVariantsCheckboxOn: this.product.hasVariants,
                productOptions: [],
                variants: [],
                variantOptions: [],

                optionItems: [],
                lastOptionItem: {},
            }
        },
        computed: {
        },
        watch: {
            product: 'updateForm',
        },
        methods: {
            addVariant(optionItem) {
                this.optionItems.push(optionItem);
                this.lastOptionItem = optionItem;
                // optionItem => option
                // optionItem => optionValue
            },
            removeVariant(optionItem) {
                const index = this.optionItems.findIndex((el)=>el.option.name === optionItem.option.name && el.optionValue.value === optionItem.optionValue.value)
                this.optionItems.splice(index, 1);
                this.lastOptionItem = optionItem;
            },
            showCategoryName(catObj) {
                return `${catObj.name}`
            },
            showBadgeName(obj) {
                return `${obj.name}`
            },
            onSubmit(e) {
                // Array.prototype.push.apply(this.product.variants, this.variants);
                // this.product.variants.splice(0, this.product.variants.length, ...this.variants);

                // this.removeEmptyOptions();
                this.product.options.splice(0, this.product.options.length, ...this.productOptions);
//                if (this.validatedForm(e)) {
                this.$emit('submit', this.product);
//                }
            },
            onCancel() {
                this.$emit('click')
            },
            onRemoveImage(image) {
                const index = this.product.images.findIndex((img) => img.id === image.id);
                // if exists the image
                if (index !== -1) {
                    this.product.images.splice(index, 1);
                    for (let i in this.product.images) {
                        this.product.images[i].ordering = i;
                    }
                }
            },
            onImageUploadError(error) {
                this.localErrors.push(error);
            },
            validatedForm(e) {
                this.localErrors = [];
                if (!this.product.name) {
                    this.localErrors.push('Add meg a kategória nevét.');
                }
                if (!this.product.slug) {
                    this.localErrors.push('Add meg a slug-ot.');
                }
                if (!this.localErrors.length) {   // equals to: if length == 0
                    return true;
                }
                e.preventDefault();
            },
            updateForm() {
                this.localErrors = [];
                let el = this.$refs.form;
                el.scrollIntoView();
            },
        },
        created () {
            // Array.prototype.push.apply(this.variants, this.product.variants);
            Array.prototype.push.apply(this.productOptions, this.product.options);
            // this.extractOptionListFromVariants();
        },
        mounted() {
        }
    }
</script>

<style scoped="">
</style>