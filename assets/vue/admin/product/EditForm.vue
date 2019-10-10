<template>
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
            <div class="mb-2" id="---NAME">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <label class="col-form-label required" for="productName">Termék neve</label>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-6 mr-auto">
                        <input v-model="product.name" type="text" id="productName" required="required" maxlength="100" class="form-control">
                    </div>
                </div>
            </div>
            <div class="mb-2" id="---SKU">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <label class="col-form-label required" for="sku">SKU</label>
                    </div>
                    <div class="col-sm-12 col-md-5 col-xl-4 mr-auto">
                        <input v-model="product.sku" type="text" id="sku" required="required" placeholder="Pl: DF100172" class="form-control">
                    </div>
                </div>
            </div>
            
            <div v-if="product.images" class="mb-2" id="---imagePreview">
                
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                    </div>
                    <div class="col-sm-12 col-md-8 col-xl-8 mr-auto">
       
                        <div class="row row-narrow">
                            <div v-for="item in product.images" class="col-xl-2 col-lg-3 col-md-4 col-6 col-narrow">
                                <div class="product">
                                    <div class="product-image">
                                        <div :style="{ backgroundImage: 'url(' + item.imageUrl + ')' }" style="background: center center no-repeat;background-size: cover; height: 150px;" class="d-flexX w-100">
    
                                        </div>
                                        <!--<img :src="item.imageUrl" class="img-thumbnailX img-fluid" style="max-height: 200px;" />-->
                                        
                                        <!--<a href="/termek/1" class="tile-link product-hover-overlay-link">-->
                                        <!--</a>-->
                                        <div v-if="item.ordering === '0' || item.ordering === 0" class="card-img-overlay d-flex align-items-end p-0">
                                            <div class="text-center w-100 overlay--bestseller">
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
            
            <div class="mb-2" id="---KEP_FELTOLTES">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <div class="col-form-label">Kép</div>
                    </div>
                    <div class="col-sm-12 col-md-5 col-xl-4 mr-auto">
                        <!--:on-max-files="maxFilesReached"-->
                        <vue-clip
                                :options="options"
                                
                                :on-complete="complete"
                                :on-added-file="addedFile"
                                class="uploader"
                                :key="componentKey"
                                ref="vueclip"
                        >
                            <template v-slot:clip-uploader-action="props">
                                <div class="uploader-action" v-bind:class="{dragging: props.dragging}">
                                    <div class="dz-message"><h5> Képfeltöltés... </h5></div>
                                </div>
                            </template>
                        
                            <template v-slot:clip-uploader-body="props">
                                <div class="uploader-files">
                                    <div v-for="file in uploadedFiles" class="uploader-file d-flex justify-content-start align-items-center mb-2X">
                                        <div class="file-avatar mr-3">
                                            <img v-bind:src="file.dataUrl" class="img-thumbnail" />
                                        </div>
                                        <div class="file-details">
                                            <div class="file-name">
                                                {{ file.name }} {{ file.customAttributes.id }}
                                                <!--{{ file.status }}-->
                                            </div>
                                            <div class="file-progress" v-if="file.status !== 'error' && file.status !== 'success'">
                                                <span class="progress-indicator" v-bind:style="{width: file.progress + '%'}"></span>
                                            </div>
                                            <div class="file-meta" v-else>
                                                <span class="file-size">{{ file.size }} KB</span> |
                                                <span class="file-status text-success">{{ file.progress }}%</span>
                                                <span class="file-status text-danger" v-if="file.progress === 0 && file.errorMessage">{{ file.errorMessage }}</span>
                                            </div>
                                            <!--<div class="file-meta" v-if="error">-->
                                            <!--<span class="file-size">{{ error }} !!</span>-->
                                            <!--</div>-->
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </vue-clip>
                    </div>
                </div>
            </div>
            
            <div class="mb-2" id="---DESCRIPTION">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <label class="col-form-label" for="description">Rövid ismertető</label>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-6 mr-auto">
                        <textarea v-model="product.description" id="description" maxlength="65535" rows="5" class="form-control"></textarea>
                    </div>
                </div>
            </div>
            <div class="mb-2" id="---CATEGORIES">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <label class="col-form-label required" for="category">Kategóriák</label>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-6 mr-auto">
                        <multiselect
                                v-model="product.categories"
                                :options="categories"
                                :multiple="true"
                                :close-on-select="false"
                                :custom-label="showCategoryName"
                                placeholder="Válassz..."
                                track-by="id"
                                :showLabels="false"
                                :hideSelected="true"
                                class="mb-2"
                        >
                            <!--:loading="dataIsLoading"-->
                            <span slot="noResult">Upsz! Nincs ilyen kategória...</span>
                        </multiselect>
                        <!--<select v-model="product.category" id="category" required="required" class="custom-select form-control">-->
                        <!--<option value="">Válassz valamit...</option>-->
                        <!--</select>-->
            
                    </div>
        
                </div>
            </div>
            
            <div class="mb-2" id="---elvalaszto">
                <div class="row">
                    <div class="col-sm-12 mr-auto">
                        <hr>
                    </div>
                </div>
            </div>
    
            <div class="mb-2" id="---KIND">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <legend class="col-form-label required">Típusa</legend>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-6 mr-auto">
                        <div class="form-row">
                            <template v-for="kind in kinds">
                                <div class="col-md-4 col-6">
                                    <div class="visual-picker visual-picker-fluid visual-picker-sm align-top has-peekX mr-2 mb-2">
                                        <input v-model="product.kind" type="radio" :value="kind" :id="`kind_${kind.id}`" required="required">
                                        <label class="visual-picker-figure required" :for="`kind_${kind.id}`">
                                        <span class="visual-picker-content">
                                            <span class="tile tile-lg">
                                                <span>{{ kind.name }}</span>
                                            </span>
                                        </span>
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2" id="---TERMEK_AR">
                <template v-if="!product.kind.priceEnabled">
                    <div class="row" id="---termekAr">
                        <div class="col-sm-12 col-md-2 order-1">
                            <label class="col-form-label required" for="price_grossPrice">Termék ár</label>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-3 mr-auto form-group order-3 order-md-2">
                            <div class="input-group">
                                <input v-model="product.price.value" type="number" id="price_grossPrice" required="required" placeholder="" class="form-control">
                                <div class="input-group-append">
                                    <span class="input-group-text">Ft</span>
                                </div>
            
                            </div>
                        </div>
                        <div class="col-sm-12 col-md-4 order-2 order-md-3">
                            <div class="card h-100X ml-lg-2 ml-0 mb-2 mb-lg-0">
                                <div class="card-body minicard--body bg-light d-flex flex-column h-100">
                                    <div class="">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Ez lesz a Standard méret ára, amennyiben 3 meretű termékről van szó.
                                    </div>
                
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
                <template v-else>
                    <div class="form-group row" id="---Arak">
                        <div class="col-sm-12 col-md-2">
                            <legend class="col-form-label required">Termék árak</legend>
                        </div>
                        <div class="col-sm-12 col-md-6 col-xl-6 mr-auto">
                            <div class="form-row">
                
                                <template v-for="(attribute, index) in attributes">
                                    <div class="col-sm-6 col-md-4 col-6 mb-1">
                                        <!--<input v-model="product.kind" type="radio" :value="kind" :id="`kind_${kind.id}`" required="required">-->
                                        <!--<label class="visual-picker-figure required" :for="`kind_${kind.id}`">-->
                        
                                        <label :for="`attribute_${attribute.id}`">{{ attribute.name }}</label>
                                        <div class="input-group">
                                            <input v-model="prices[index]" type="number" :id="`attribute_${attribute.id}`" placeholder="" class="form-control">
                                            <div class="input-group-append">
                                                <span class="input-group-text">Ft</span>
                                            </div>
                                        </div>
                    
                                    </div>
                                </template>
                
                
                                <!--<div class="col-sm-6 col-md-4 col-6 mb-1">-->
                                <!--<label>Deluxe</label>-->
                                <!--<div class="input-group">-->
                                <!--<input type="number" id="product_form_subproducts_1_price_grossPrice" name="product_form[subproducts][1][price][grossPrice]" placeholder="" class="form-control">-->
                                <!--<div class="input-group-append">-->
                                <!--<span class="input-group-text">Ft</span>-->
                                <!--</div>-->
                                <!--</div>-->
                                <!---->
                                <!--</div>-->
                                <!--<div class="col-sm-6 col-md-4 col-6 mb-1">-->
                                <!--<label>Prémium</label>-->
                                <!--<div class="input-group">-->
                                <!--<input type="number" id="product_form_subproducts_2_price_grossPrice" name="product_form[subproducts][2][price][grossPrice]" placeholder="" class="form-control">-->
                                <!--<div class="input-group-append">-->
                                <!--<span class="input-group-text">Ft</span>-->
                                <!--</div>-->
                                <!--</div>-->
                                <!---->
                                <!--</div>-->
                            </div>
                        </div>
                    </div>
                </template>
            </div>
            
            <div class="mb-2" id="---elvalaszto">
                <div class="row">
                    <div class="col-sm-12 mr-auto">
                        <hr>
                    </div>
                </div>
            </div>
            <div class="mb-2" id="---STATUS">
                <div class="row">
                    <div class="col-sm-12 col-md-2 order-1">
                        <legend class="col-form-label required">Állapot</legend>
                    </div>
                    <div class="col-sm-12 col-md-6 col-xl-6 mr-auto order-3 order-md-2">
                        <div class="form-row">
                            <template v-for="status in statuses">
                                <div class="col-md-4 col-6">
                                    <div class="visual-picker visual-picker-fluid visual-picker-sm align-top has-peekX mr-2 mb-2">
                                        <input v-model="product.status" type="radio" :value="status" :id="`status_${status.id}`" required="required">
                                        <label class="visual-picker-figure required" :for="`status_${status.id}`">
                                            <span class="visual-picker-content">
                                                <span class="tile tile-lg">
                                                    <span>
                                                        <span class="text-nowrap"><span v-html="status.icon"></span>{{ status.name }}</span>
                                                    </span>
                                                </span>
                                            </span>
                                        </label>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    <div class="col-sm-12 col-md-4 order-2 order-md-3">
                        <div class="card h-100X ml-lg-2 ml-0 mb-2 mb-lg-0">
                            <div class="card-body minicard--body bg-light d-flex flex-column h-100">
                                <div class="">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>Kifutott</strong> állapotban a termék továbbra is látszik a weboldalon, csupán nem lesz neki <strong>Kosárba rakom</strong> gomb.
                                </div>
                        
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="mb-2" id="---STOCK">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <label class="col-form-label required" for="stock">Készlet a raktáron</label>
                    </div>
                    <div class="col-sm-12 col-md-4 col-xl-2 mr-auto input-group">
                        <input v-model="product.stock" type="number" id="stock" required="required" placeholder="" class="form-control">
                        <!--<div class="input-group-append">-->
                            <!--<span class="input-group-text">db</span>-->
                        <!--</div>-->
                    </div>
                </div>
            </div>
    
            <div class="mb-2" id="---BADGES">
                <div class="form-group row">
                    <div class="col-sm-12 col-md-2">
                        <label class="col-form-label required" for="badge">Matricák</label>
                    </div>
                    <div class="col-sm-12 col-md-5 col-xl-4 mr-auto">
                        <multiselect
                                v-model="product.badges"
                                :options="badges"
                                :multiple="true"
                                :close-on-select="true"
                                :custom-label="showBadgeName"
                                placeholder="Válassz..."
                                track-by="id"
                                :showLabels="false"
                                :hideSelected="true"
                                class="mb-2"
                        >
                            <!--:loading="dataIsLoading"-->
                            <span slot="noResult">Upsz! Nincs ilyen megye vagy elírtad...</span>
                        </multiselect>
                        <!--<select v-model="product.category" id="category" required="required" class="custom-select form-control">-->
                        <!--<option value="">Válassz valamit...</option>-->
                        <!--</select>-->
            
                    </div>
        
                </div>
            </div>
        
            <div class="mb-2" id="---elvalaszto">
                <div class="row">
                    <div class="col-sm-12 mr-auto">
                        <hr>
                    </div>
                </div>
            </div>
        
            <div class="mb-2" id="---elvalaszto">
                <div class="row">
                    <div class="col-sm-12 mr-auto">
                        &nbsp;
                    </div>
                </div>
            </div>
            <save-toolbar
                    :entityToWatch="product"
                    :formIsPosting="formIsPosting"
                    v-on:submit="onSubmit"
                    v-on:click="onCancel"
            ></save-toolbar>
        </fieldset>
    </form>
</template>

<script>
    import Multiselect from 'vue-multiselect'
    import SaveToolbar from './../_components/save-toolbar/SaveToolbar.vue'

    const initialData = () => {
        return {
            options: {
                url: '/admin/api/images/product/',
                headers: {
//                        credentials: 'same-origin',
//                        type: 'multipart/form-data',  // ??
                },
                paramName: 'imageFile',
//                uploadMultiple: false,
//                parallelUploads: 3,
                maxFiles: {
                    limit: 5,
                    message: 'You can only upload a max of 5 files'
                },
                acceptedFiles: {
                    extensions: ['image/*'],
                    message: 'You are uploading an invalid file'
                },
            },
        }
    }
    
    export default {
        components: {
            Multiselect,
            SaveToolbar,
        },
        props: [
            'formIsPosting',
            'product',
            'categories',
            'kinds',
            'attributes',
            'statuses',
            'badges',
            'errors',
            'resetForm',
        ],
        data: function () {
            return {
                prices: [],
                localErrors: [],

                imageIsUploading: false,
                imageIsSaved: false,
                
                imageUrl: null,
                options: initialData().options,
                uploadedFiles: [],
                
                componentKey: 0,  // can be somethingElse, whatever
            }
        },
        watch: {
            product: 'updateForm',
            imageUrl: {
                handler () { this.imageIsSaved = false },
                deep: true
            },
            resetForm: 'resetUploader',
        },
        methods: {
            showCategoryName (catObj) {
                return `${catObj.name}`
            },
            showBadgeName (obj) {
                return `${obj.name}`
            },
            submitFile () {
                this.imageIsUploading = true;
                setTimeout(() => this.savedImage(), 1000);
            },
            savedImage () {
                this.imageIsUploading = false;
                this.imageIsSaved = true;
            },
            onSubmit (e) {
//                if (this.validatedForm(e)) {
                    this.$emit('submit', this.product);
//                }
            },
            onCancel () {
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
            validatedForm (e) {
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
            updateForm () {
                this.localErrors = [];
                this.resetUploader();
//                this.imageUrl = this.product.imageUrl;
                let el = this.$refs.form;
                el.scrollIntoView();
            },
            resetUploader () {
                this.$refs.vueclip.removeAllFiles();
                this.uploadedFiles = [];
                this.componentKey += 1;
            },
//            maxFilesReached (file) {
//                console.log(file.errorMessage);
//            },
            complete (file, status, xhr) {
                console.log(file);
                // Adding server id to be used for deleting the file.
                if (status === 'success') {
                    let data = JSON.parse(xhr.response);
                    let image = new Object();
                    image.image = data.images[0];
                    image.imageUrl = data.images[0].file;
                    image.ordering = this.product.images.length;  // the length before push() is actually the index of the last element after push()
                    this.product.images.push(image);
                    file.addAttribute('id', image.image.id);

//                    let image = data.images[0];
//                    this.product.images.push(image);
//                    file.addAttribute('id', image.id);
//                    for (let item of Object.values(data.images)) {
//                        file.addAttribute('id', item.id);
//                        this.product.images.push(item);
//                    }
                        console.log(file.customAttributes.id);   ////////////
                } else {
                    let json = JSON.parse(xhr.response);
                    this.errors.push(json.errors.imageFile);
                    console.log(json.errors.imageFile)
                }
            },
            addedFile (file) {
                this.uploadedFiles.push(file);
            },
        },
//        // This is necessary, because of a bug in Dropzone, which ignores options: { uploadMultiple: false, }
//        mounted() {
//            this.$refs.vueclip.uploader._uploader.hiddenFileInput.removeAttribute("multiple")
//        }
    }
</script>

<style scoped="">
    ul {
        margin-bottom: 0;
    }
</style>