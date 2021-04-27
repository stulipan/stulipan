<template>
    <div class="mt-3">
        <div v-if="product && product.hasVariants" class="modal fade" id="modal--editOptions" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
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
                        <a href="" class="btn btn-secondary mr-2" data-dismiss="modal">Cancel</a>
                        <button type="submit" class="JS--btn-submitStatus btn btn-primary">Done</button>
                    </div>
                </div>
            </div>
        </div>

        <form novalidate="true" ref="form" enctype="multipart/form-data">
            <div v-if="localErrors.length" class="alert alert-danger mb-3">
                <b>Please correct the following error(s):</b>
                <ul>
                    <li v-for="error in localErrors">{{ error }}</li>
                </ul>
            </div>
            <div v-else-if="errors.length" class="alert alert-danger mb-3">
                Javítsd a lenti hibákat.
<!--                <ul>-->
<!--                    <li v-for="error in errors">{{ error }}</li>-->
<!--                </ul>-->
            </div>

            <fieldset :disabled="formIsPosting" :class="{ disabled: formIsPosting }">
                <div class="row">
                    <div class="col-md-8 correction-no-padding">

                        <!--    NAME___AND___DESCRIPTION  -->
                        <div class="card mb-20px">
                            <div class="card-body">
                                <div class="form-group" :class="{ 'group-validation': showError('name') }">
                                    <label class="" for="productName">Termék neve</label>
                                    <input v-model="product.name" type="text" id="productName" required="required" maxlength="100" class="form-control">
                                    <span class="invalid-feedback">{{ showError('name') }}</span>
                                    <div v-if="product.slug" class="form-text">
                                        Product's slug: <code>{{product.slug}}</code>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="" for="description">Rövid ismertető</label>
                                    <textarea v-model="product.description" id="description" maxlength="65535" rows="5" class="form-control"></textarea>
                                </div>
                            </div>
                        </div>
                        
                        <!--    VARIANTS___BLOCK     -->
                        <div class="card mb-20px">
                            <div class="card-body">
                                <div class="d-flex justify-content-between">
                                    <div class="h5 mb-0">Termékvariációk</div>
                                    <div v-if="product && product.hasVariants" class="">
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
                                <!--    VARIANTS__EDIT -->
                                <div class="mt-3">
                                    <!--  If hasVariants checkbox is checked and the Product has variants  -->
                                    <!--  Lists the variants  -->
                                    <template v-if="product && product.hasVariants">
                                        <div class="form-group">
                                            <variant-list
                                                    :variants="product.variants"
                                                    :options=productOptions
                                                    :option-items="optionItems"
                                                    :last-option-item="lastOptionItem"
                                                    :price="product.price"
                                                    :isEditable="true"
                                            ></variant-list>
                                        </div>
                                    </template>

                                    <template v-else>
                                        <template v-if="product">
                                            <div class="form-group">
                                                <div class="custom-control custom-checkbox">
                                                    <input type="checkbox" v-model="isVariantsCheckboxOn" id="hasVariants" required="required" class="custom-control-input">
                                                    <label class="custom-control-label required" for="hasVariants">This product has multiple options, like different sizes or colors</label>
                                                </div>
                                            </div>
                                            <div class="form-group">
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
                                                    <div class="form-group">
                                                        <variant-list
                                                            :variants="product.variants"
                                                            :options=productOptions
                                                            :option-items="optionItems"
                                                            :last-option-item="lastOptionItem"
                                                            :price="product.price"
                                                            :is-editable="false"
                                                        ></variant-list>
                                                    </div>
                                                </template>
                                                <template v-else>
                                                        <!--    TERMEK ARA    -->
                                                        <div class="form-group" :class="{ 'group-validation': showError('price') }"
                                                        >
                                                            <label class="required" for="price_grossPrice">Termék ár</label>
                                                            <div class="field-item">
                                                                <div class="field-prepend">
                                                                    <div class="field-addon-prepend w-addon-sm">HUF</div>
                                                                </div>
                                                                <input v-model="product.price.numericValue" type="number" id="price_grossPrice" required="required" placeholder="0.00" class="form-control pl-addon-sm">
                                                            </div>
                                                            <span class="invalid-feedback">{{ showError('price') }}</span>
                                                            <div class="form-text text-muted mt-2">
                                                                Ez lesz a Standard méret ára, amennyiben 3 meretű termékről van szó.
                                                            </div>
                                                        </div>
                                                    </template>
                                            </div>
                                        </template>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!--    KEPEK     -->
                        <div class="card mb-20px">
                            <div class="card-body">
                                <div class="h5 mb-0">Képek</div>
                                <div v-if="product && product.images" class="mt-3">
                                    <div class="row vertical-row">
                                        <div v-for="item in product.images" :key="item.id" class="col-md-4 col-6">
                                            <div class="vertical-col pb-3">
                                                <div class="product-image">
                                                    <div v-if="item.image" :style="{ backgroundImage: 'url(' + item.image.file + ')' }" style="background: center center no-repeat;background-size: cover; height: 200px;" class="w-100"></div>
                                                    <div v-else :style="{ backgroundImage: 'url(' + item.thumbnailUrl + ')' }" style="background: center center no-repeat;background-size: cover; height: 200px;" class="w-100"></div>
                                                    <div v-if="item.ordering === '0' || item.ordering === 0" class="card-img-overlay d-flex align-items-end p-0">
                                                        <div class="text-center w-100 overlay--productCover">
                                                            <i class="fas fa-camera"></i> Borítókép
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="pt-2 text-center">
                                                    <p class="productList--name mb-1">
                                                        <a @click.prevent="onRemoveImage(item)" href="#" class="btn btn-sm btn-secondary" title="Töröl">
                                                            <i class="far fa-trash-alt mr-1"></i> Töröl
                                                        </a>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
<!--                                :images=productImages-->
                                <product-image-upload class="w-100 mt-3"
                                                      @success="onImageUploadSuccess"
                                                      @error="onImageUploadError"
                                >
                                </product-image-upload>
                            </div>
                        </div>
                        
                        <!-- ___SKU___STOCK -->
                        <div class="card mb-20px">
                            <div class="card-body">
                                    <div class="h5 mb-0">Készlet</div>
                                    <div class="form-group row mt-3" :class="{ 'group-validation': showError('sku') }">
                                        <div class="col-sm-12">
                                            <label class="required" for="sku">SKU (Stock Keeping Unit)</label>
                                            <input v-model="product.sku" type="text" id="sku" required="required" placeholder="Pl: DF100172" class="form-control">
                                            <span class="invalid-feedback">{{ showError('sku') }}</span>
                                        </div>
                                    </div>
                                    <div class="form-group row" :class="{ 'group-validation': showError('stock') }">
                                        <div class="col-sm-12">
                                            <label class="required" for="stock">Készlet a raktáron</label>
                                            <input v-model="product.stock" type="number" id="stock" required="required" placeholder="" class="form-control">
                                            <span class="invalid-feedback">{{ showError('stock') }}</span>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <!--    __SIDEBAR   -->
                    <div class="col-md-4 correction-no-padding">
                        <div class="card mb-20px">
                            <div class="card-body">
                                <div class="h5 mb-0">Kategorizálás</div>
                                <div class="form-group mt-3" :class="{ 'group-validation': showError('status') }">
                                    <legend class="col-form-label">Állapot</legend>
                                    <ul class="list-group list-group-flush list-group--status">
                                        <li v-for="status in statuses" :key="status.id" class="list-group-item">
                                            <div class="custom-control custom-radio">
                                                <input type="radio" v-model="productStatus.id" :value="status.id" :id="`status_${status.id}`" required="required" class="custom-control-input">
                                                <label class="custom-control-label" :for="`status_${status.id}`">
                                                    {{ status.name }}
                                                </label>
                                            </div>
                                        </li>
                                    </ul>
                                    <span class="invalid-feedback">{{ showError('status') }}</span>
                                    <div class="form-text">
                                        <em>Kifutott</em> állapotban a termék továbbra is látszik a weboldalon, csupán nem lesz neki <em>Kosárba rakom</em> gomb.
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-group" :class="{ 'group-validation': showError('categories') }">
                                    <label class="">Kategóriák</label>
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
                                    <span class="invalid-feedback">{{ showError('categories') }}</span>
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-group" :class="{ 'group-validation': showError('kind') }">
                                    <label class="">Termék típus</label>
                                    <multiselect
                                            v-model="productKind"
                                            placeholder="Válassz..."
                                            label="name"
                                            
                                            track-by="id"
                                            :options="productKinds"
                                            :multiple="false"
                                            :close-on-select="true"
                                            :show-labels="false"
                                            :show-no-results="false"
                                            :class="[ {'detached': true}, {'mb-2': true}]"
                                            open-direction="bottom"
                                            :max-height="150"
                                            :allow-empty="false"
                                    >
<!--                                        <span slot="noResult">Nincs ilyen opció...</span>-->
                                        <!--    A showLabels=false miatt kell ez, valamiert ez esetben nem mutatja a placeholdert.     -->
<!--                                        <template slot="singleLabel" slot-scope="props">Válassz...</template>-->
                                    </multiselect>
                                    <span class="invalid-feedback">{{ showError('kind') }}</span>
                                    
                                </div>
                            </div>
                            <div class="card-footer">
                                <div class="form-group">
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
    import Multiselect from "vue-multiselect";
    import OptionsForm from "./OptionsForm";
    import SaveToolbar from './../_components/save-toolbar/SaveToolbar.vue'
    import ProductImageUpload from "./../_components/ProductImageUpload";
    import VariantList from "./VariantList";
    import Notify from "../../../js/alerts/notify";

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
            Multiselect,
            ProductImageUpload,
        },
        props: [
            'formIsPosting',
            'product',
            'categories',
            'statuses',
            'badges',
            'productKinds',
            'errors',
            'resetForm',
        ],
        data () {
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

                productStatus: {
                    id: null,
                },
                productKind: {
                    id: null,
                },
                productImages: [],
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
            showProductKindName(obj) {
                return `${obj.name}`
            },
            showError(field) {
                const index = this.errors.findIndex( (el) => el.propertyName === field );
                if (index !== -1) {
                    console.log(index);
                    console.log(this.errors[index].message);
                    return this.errors[index].message;
                }
                return null;
            },
            onSubmit(e) {
                // Array.prototype.push.apply(this.product.variants, this.variants);
                // this.product.variants.splice(0, this.product.variants.length, ...this.variants);

                // this.removeEmptyOptions();
                this.product.options.splice(0, this.product.options.length, ...this.productOptions);
                this.product.status = this.productStatus;
                this.product.kind = this.productKind;
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
            onImageUploadSuccess(imageEntity) {
                let productImage = {
                    id: null,
                    imageUrl: imageEntity.file,  // imageEntity.file holds only the relative path to the image
                    thumbnailUrl: imageEntity.file,
                    ordering: this.product.images.length,
                    image: imageEntity,  // with this we pass on the imageEntity too
                }
                this.product.images.push(productImage);
            },
            onImageUploadError(errors) {
                errors.forEach(function (error, index) {
                    this.localErrors.push(error);
                }.bind(this));
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

            if (this.product.status !== null) {
              this.productStatus = this.product.status;
            }
            if (this.product.kind !== null) {
                this.productKind = this.product.kind;
            }
            if (this.product.images.length) {
                this.productImages = this.product.images;
            } else {
                this.productImages = {
                    id: null,
                }
            }
          
            // this.extractOptionListFromVariants();
        },
        mounted() {
        }
    }
</script>

<style scoped="">
</style>