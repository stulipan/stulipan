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
                        <template v-for="(option, index) in productOptions">
                            <div class="form-group form-row">
                                <div class="col-md-12 mb-2">
                                    <div class="admin-card-title">Option {{index + 1}}</div>
                                    <div v-if="!option.values.length" class="admin-card-topright">
                                        <a @click.prevent="removeProductOption(index)" role="button" href="" class="">Remove</a>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <input v-model="productOptions[index].name" type="text" :id="`option_modal_${index}`" required="required" placeholder="Eg.: Size" class="form-control" ref="option">
                                </div>
                                <div class="col-md-8">
                                    <multiselect
                                            v-model="productOptions[index].values"
                                            :options="productOptions[index].values"
                                            :selected="productOptions[index].values"
                                            label="value"
                                            :taggable="true"
                                            :multiple="true"
                                            :hideSelected="true"
                                            track-by="value"
                                            @tag="addTag"
                                            @remove="removeTag"
                                            :id="index"
                                            :placeholder="!productOptions[index].values.length ? 'Type the value then press Enter' : null "
                                            :max-height="0"
                                            :closeOnSelect="false"
                                            class="taggable"
                                    >
                                    </multiselect>
                                    <!--                                                            <input v-model="option1Value" type="text" id="option1Value" required="required" placeholder="Separate options with a coma" class="form-control">-->
                                </div>
                            </div>
                        </template>
                        <template v-if="productOptions.length < 3">
                            <div class="form-group form-row">
                                <div class="col-md-12">
                                    <button @click.prevent="addProductOption" class="btn btn-secondary" role="button">Add another option</button>
                                </div>
                            </div>
                        </template>
                    </div>
                    <div class="modal-footer">
                        <div class="loadingWrapper loadingWrapper-secondary">
                            <a href="" class="btn btn-secondary" data-dismiss="modal">Cancel</a>
                            <div class="JS--loadingOverlay d-flex align-items-stretch"></div>
                        </div>
                        <div class="loadingWrapper">
                            <button type="submit" class="JS--btn-submitStatus btn btn-info">Done</button>
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
                                            <!--                                            <textarea v-model="product.description" id="description" maxlength="65535" rows="5" class="form-control"></textarea>-->
                                            <trumbowyg v-model="product.description" :config="config" class="form-control" name="content"></trumbowyg>
                                            <!--                                    <ckeditor :editor="editor" v-model="editorData" :config="editorConfig"></ckeditor>-->
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card mb-20px">
                            <div class="card-wrapper">
                                <div class="card-body">
                                    <div class="h5 admin-card-title mb-4">Termékvariációk</div>
                                    <div v-if="product.hasVariants" class="admin-card-topright">
                                        <div class="dropdown">
                                            <a class="dropdown-toggle" href="javascript: void(0);" id="moreOptions" role="button" data-toggle="dropdown" data-display="static" aria-haspopup="true" aria-expanded="false">
                                                <!--                                        <i class="fas fa-ellipsis-h"></i> -->
                                                More options
                                            </a>
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
                                    <div id="---KIND ---TERMEK_OPTION">
                                        <!--  If hasVariants checkbox is checked and the Product has variants  -->
                                        <!--  Lists the variants  -->
                                        <template v-if="product.hasVariants">
                                            <template>
                                                <div class="form-group row">
                                                    <div class="col-sm-12">
                                                        <span class="mr-3">Select:</span>
                                                        <template v-for="(option, i) in variantOptions">
                                                            <template v-for="(optionValue, i) in option.values">
                                                                <a href="javascript: void(0);" class="mr-3">{{ optionValue.value }}</a>
                                                            </template>
                                                        </template>
                                                    </div>
                                                </div>
                                                <div class="form-group row">
                                                    <div class="col-sm-12 grid-preview grid-shadowX">
                                                        <div class="no-margins">
                                                            <div class="grid-scroll">
                                                                <div class="position-relative">
                                                                    <div class="grid-template-container py-3 px-4 font-weight-bold">
                                                                        <template v-for="(option, i) in variantOptions">
                                                                            <div class="grid-cell">{{ option.name }}</div>
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
                                                                                        <div class="grid-template-container py-3 px-4">
                                                                                            <template v-for="(selected, j) in variant.selectedOptions">
                                                                                                <div class="grid-cell mr-2">
<!--                                                                                                    <input v-model="selected.optionValue.value" type="text" required="required" placeholder="" class="form-control">-->
                                                                                                    <input v-model="selected.optionValue.value" :id="`${variant.name}_${i}_${selected.option}_${j}`" type="text" :disabled="true" required="required" placeholder="" class="form-control">
                                                                                                </div>
                                                                                            </template>

                                                                                            <div class="grid-cell mr-2">
                                                                                                <div class="input--area-group">
                                                                                                    <div class="input--area-addon input--area-prepend">
                                                                                                        HUF
                                                                                                    </div>
                                                                                                    <input v-model="variant.price.numericValue" type="number" required="required" placeholder="0.00" class="form-control">
                                                                                                </div>
                                                                                            </div>
                                                                                            <div class="grid-cell mr-2">
                                                                                                <input v-model="variant.position" :id="`${variant.name}_quantity_${i}`" type="number" required="required" class="form-control">
                                                                                            </div>
                                                                                            <div class="grid-cell mr-2">
                                                                                                <input v-model="variant.sku" :id="`${variant.name}_sku_${i}`" type="text" required="required" class="form-control">
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
                                                </div>
                                            </template>
                                        </template>

                                        <template v-else>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <div class="custom-control custom-checkbox">
                                                        <input type="checkbox" v-model="isVariantsCheckboxOn" @change="addFirstOption" id="hasVariants" name="hasVariants" required="required" class="custom-control-input">
                                                        <label class="custom-control-label required" for="hasVariants">This product has multiple options, like different sizes or colors</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="form-group row">
                                                <div class="col-sm-12">
                                                    <!--  If hasVariants checkbox is checked BUT the Product has NO variants  -->
                                                    <!--  Displays the product option form and the variants preview form -->
                                                    <template v-if="isVariantsCheckboxOn">
                                                        <template v-for="(option, index) in productOptions">
                                                            <div class="form-group form-row">
                                                                <div class="col-md-12 mb-2">
                                                                    <div class="admin-card-title">Option {{index + 1}}</div>
                                                                    <div v-if="!option.values.length" class="admin-card-topright">
                                                                        <a @click.prevent="removeProductOption(index)" role="button" href="" class="">Remove</a>
                                                                    </div>
                                                                </div>
                                                                <div class="col-md-4">
                                                                    <input v-model="productOptions[index].name" type="text" :id="`option_${index}`" required="required" placeholder="Eg.: Size" class="form-control" ref="option">
                                                                </div>
                                                                <div class="col-md-8">
                                                                    <multiselect
                                                                            v-model="productOptions[index].values"
                                                                            :options="productOptions[index].values"
                                                                            :selected="productOptions[index].values"
                                                                            label="value"
                                                                            :taggable="true"
                                                                            :multiple="true"
                                                                            :hideSelected="true"
                                                                            track-by="value"
                                                                            @tag="addTag"
                                                                            @remove="removeTag"
                                                                            :id="index"
                                                                            :placeholder="!productOptions[index].values.length ? 'Type the value then press Enter' : null "
                                                                            :max-height="0"
                                                                            :closeOnSelect="false"
                                                                            class="taggable"
                                                                    >
                                                                    </multiselect>
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template v-if="productOptions.length < 3">
                                                            <div class="form-group form-row">
                                                                <div class="col-md-12">
                                                                    <a @click="addProductOption" class="btn btn-secondary" role="button" tabindex="0">Add another option</a>
                                                                    <!--                                                            <div class="admin-card-topright">-->
                                                                    <!--                                                                <button @click.prevent="createVariants" class="btn btn-info" :disabled="!hasProductOptions">Create variants</button>-->
                                                                    <!--                                                            </div>-->
                                                                </div>
                                                            </div>
                                                        </template>
                                                        <template>
                                                            <div class="form-group row">
                                                                <div class="col-sm-12 grid-preview grid-shadowX">
                                                                    <div class="no-margins">
                                                                        <div class="grid-scroll">
                                                                            <div class="position-relative">
                                                                                <div class="grid-template-container py-3 px-4 font-weight-bold">
                                                                                    <template v-for="(option, i) in productOptions">
                                                                                        <div v-if="option.values.length" class="grid-cell">{{ option.name }}</div>
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
                                                                                                    <div class="grid-template-container py-3 px-4">
                                                                                                        <template v-for="(selected, j) in variant.selectedOptions">
                                                                                                            <div class="grid-cell mr-2">
                                                                                                                <input v-model="selected.optionValue.value" :id="`${variant.name}_${i}_${selected.option}_${j}`" type="text" :disabled="true" required="required" placeholder="" class="form-control">
                                                                                                            </div>
                                                                                                        </template>

                                                                                                        <div class="grid-cell mr-2">
                                                                                                            <div class="input--area-group">
                                                                                                                <div class="input--area-addon input--area-prepend">
                                                                                                                    HUF
                                                                                                                </div>
                                                                                                                <input v-model="variant.price.numericValue" :id="`${variant.name}_price_${i}`" type="number" required="required" placeholder="0.00" class="form-control">
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="grid-cell mr-2">
                                                                                                            <input v-model="variant.position" :id="`${variant.name}_quantity_${i}`" type="number" required="required" class="form-control">
                                                                                                        </div>
                                                                                                        <div class="grid-cell mr-2">
                                                                                                            <input v-model="variant.sku" :id="`${variant.name}_sku_${i}`" type="text" required="required" class="form-control">
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
                                                            </div>
                                                        </template>
                                                    </template>
                                                    <template v-else>
                                                        <div class="form-group row" id="---termekAr">
                                                            <div class="col-sm-12">
                                                                <label class="required" for="price_grossPrice">Termék ár</label>
                                                                <div class="input--area-group">
                                                                    <div class="input--area-addon input--area-prepend">
                                                                        HUF
                                                                    </div>
                                                                    <input v-model="product.price.numericValue" type="number" id="price_grossPrice" required="required" placeholder="0.00" class="form-control">
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
                                    <div>
                                        <div v-if="product.images" class="" id="---imagePreview">

                                            <div class="row">
                                                <div class="col-sm-12 mr-auto">

                                                    <div class="row row-narrow">
                                                        <div v-for="item in product.images" class="col-md-4 col-6 col-narrow">
                                                            <div class="product">
                                                                <div class="product-image">
                                                                    <div :style="{ backgroundImage: 'url(' + item.imageUrl + ')' }" style="background: center center no-repeat;background-size: cover; height: 200px;" class="d-flexX w-100">

                                                                    </div>
                                                                    <!--<img :src="item.imageUrl" class="img-thumbnailX img-fluid" style="max-height: 200px;" />-->

                                                                    <!--<a href="/termek/1" class="tile-link product-hover-overlay-link">-->
                                                                    <!--</a>-->
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
                                                <div class="col-sm-12 mr-auto">
                                                    <!--:on-max-files="maxFilesReached"-->
                                                    <vue-clip
                                                            :options="options"

                                                            :on-complete="complete"
                                                            :on-added-file="addedFile"
                                                            class="uploader"
                                                            :key="componentKey"
                                                            ref="vueclip"
                                                            role="button"
                                                            tabindex="0"
                                                    >
                                                        <template v-slot:clip-uploader-action="props">
                                                            <div class="uploader-action" v-bind:class="{dragging: props.dragging}">
                                                                <div class="dz-message">
                                                                    <i class="fas fa-upload"></i>
                                                                    <span class="mb-0 btn-link">Képfeltöltés... </span> <br>
                                                                    <span>vagy húzd be ide a fájlokat a feltöltéshez.</span>
                                                                </div>
                                                            </div>
                                                        </template>

                                                        <template v-slot:clip-uploader-body="props">
                                                            <div class="uploader-files" v-if="uploadedFiles.length">
                                                                <div v-for="file in uploadedFiles" class="uploader-file d-flex justify-content-start align-items-center">
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
                            <div class="card-wrapper">
                                <div class="card-body">
                                    <div class="h5 admin-card-title mb-4">Kategorizálás</div>
                                    <div id="---STATUS ---CATEGORIES ---BADGES">
                                        <div class="form-group row">
                                            <div class="col-sm-12 order-1">
                                                <legend class="col-form-label required">Állapot</legend>
                                            </div>
                                            <div class="col-sm-12 order-3 order-md-2">
                                                <div class="form-row">
                                                    <template v-for="status in statuses">
                                                        <div class="col-12">
                                                            <div class="visual-picker visual-picker-with-checkbox align-top mr-2 mb-2" :class="product.status.id == status.id ? 'visual-picker-checked' : ''">
                                                                <input type="radio" v-model="product.status" :value="status" :id="`status_${status.id}`" required="required">
                                                                <label class="visual-picker-figure required justify-content-start" :for="`status_${status.id}`">
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
                                                    <div class="col-sm-12 text-muted">
                                                        <em>Kifutott</em> állapotban a termék továbbra is látszik a weboldalon, csupán nem lesz neki <em>Kosárba rakom</em> gomb.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

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
                                                        open-direction="auto"
                                                        no-result-label="Upsz! Nincs ilyen kategória..."
                                                >
                                                </multiselect-bellow>
                                            </div>
                                        </div>

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
                                                        open-direction="auto"
                                                        no-result-label="Upsz! Nincs ilyen kategória..."
                                                >
                                                </multiselect-bellow>
                                            </div>
                                        </div>
                                    </div>
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
    import Multiselect from 'vue-multiselect'
    import SaveToolbar from './../_components/save-toolbar/SaveToolbar.vue'
    import MultiselectBellow from "./../_components/MultiselectBellow"
    // import ClassicEditor from '@ckeditor/ckeditor5-build-classic';
    import Trumbowyg from 'vue-trumbowyg';

    const initialData = () => {
        return {
            options: {
                url: '/hu/admin/api/images/product/',
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
    };
    
    export default {
        components: {
            Multiselect,
            SaveToolbar,
            MultiselectBellow,
            // ClassicEditor,
            Trumbowyg,
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
                // editor: ClassicEditor,
                // editorData: '<p>Content of the editor.</p>',
                // editorConfig: {
                //     height: 300,
                // },
                // content: null,
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

                imageIsUploading: false,
                imageIsSaved: false,
                imageUrl: null,
                options: initialData().options,
                uploadedFiles: [],

                componentKey: 0,  // can be somethingElse, whatever
                isVariantsCheckboxOn: this.product.hasVariants,
                productOptions: [],
                // productOptions: this.product.options,
                variants: [],
                // variants: this.product.variants,
                variantOptions: [],
            }
        },
        computed: {
            hasProductOptions: function() {
                if (this.productOptions.length) {
                    for (let o of this.productOptions) {
                        return o.values.length ? true : false;
                    }
                }
                return false;
            },
            productOptionsLength: function() {
                let l = 0;
                for (let o of this.productOptions) {
                    l += o.values.length ? 1 : 0;
                    // l += 1;
                }
                return l;
            },
        },
        watch: {
            product: 'updateForm',
            imageUrl: {
                deep: true,
                handler() {
                    this.imageIsSaved = false
                },
            },
            resetForm: 'resetUploader',
            productOptions: {
                immediate: true,
                deep: true,
                handler() {
                    this.extractOptionListFromVariants();
                }
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
            addTag(value, index) {
                const optionValue = {
                    id: null,
                    value: value,
                    position: this.productOptions[index].values.length + 1,
                };
                this.productOptions[index].values.push(optionValue);
                this.addVariant({
                    option: this.productOptions[index],
                    optionValue: optionValue,
                });
            },
            removeTag(value, index) {
                let selectedOption = {
                    option: this.productOptions[index],
                    optionValue: value,
                };
                this.$nextTick(function () {
                    this.removeVariant(selectedOption);
                });
            },
            isOptionInSelectedOptions(selectedOptions, selectedOption) {
                for (let i=0; i<selectedOptions.length; ++i) {
                    if (selectedOptions[i].option.name === selectedOption.option.name) {
                        return true;
                    }
                }
                return false;
            },
            isOptionValueInSelectedOptions(selectedOptions, selectedOption) {
                for (let i=0; i<selectedOptions.length; ++i) {
                    if (selectedOptions[i].optionValue.value === selectedOption.optionValue.value) {
                        return true;
                    }
                }
                return false;
            },
            generateVariantName(selectedOptions) {
                let name = '';
                for (let s of selectedOptions) { name += name ? ' / ' + s.optionValue.value : s.optionValue.value; }
                return name;
            },
            addVariant (selectedOption) {
                let variants = [];
                if (this.variants.length === 0) {
                    variants.push({
                        name: selectedOption.optionValue.value,
                        selectedOptions: [selectedOption],
                        position: variants.length + 1,
                        price: this.product.price ? { numericValue: this.product.price.numericValue } : { numericValue: 0 },
                    });
                }

                if (this.variants.length) {
                    // let tempVariants = [];
                    // Ha mar leteznek variansok
                    for (let v of this.variants) {
                        // Ha nincs benne az option (pl: Color), akkor kiboviti az uj option-nel es optionValue-val
                        if (!this.isOptionInSelectedOptions(v.selectedOptions, selectedOption)) {
                            // v.selectedOptions.push(Object.create(selectedOption));
                            v.selectedOptions.push(selectedOption);
                            v.name = this.generateVariantName(v.selectedOptions);
                            v.position = !v.position ? this.variants.length+1 : v.position;
                        }
                        // Ha mar benne van az option (pl: Color) akkor az uj optionValue-val krealja az uj variantokat
                        else {
                            if (!this.isOptionValueInSelectedOptions(v.selectedOptions, selectedOption)) {
                                let newVariant = {};
                                newVariant.selectedOptions = [];
                                newVariant.selectedOptions.push.apply(newVariant.selectedOptions, v.selectedOptions);
                                // mivel az uj variant a regi selectedOptions tartalmat kapja meg, igy abbol torolni kell az option jelenlegi erteket
                                const index = newVariant.selectedOptions.findIndex((el) => el.option.name === selectedOption.option.name && el.optionValue.value !== selectedOption.optionValue.value);
                                newVariant.selectedOptions.splice(index, 1, selectedOption);
                                newVariant.name = this.generateVariantName(newVariant.selectedOptions);
                                newVariant.position = null;
                                newVariant.price = {
                                    numericValue: v.price.numericValue,
                                };
                                // Ha meg nincs position akkor, length azaz 0+1, amugy nem modositja
                                // newVariant.position = !newVariant.position ? this.variants.length+1 : newVariant.position;
                                // tempVariants.push(newVariant);

                                if (!this.hasVariant(newVariant)) {
                                    this.variants.push(newVariant);
                                }
                            }
                        }
                    }
                    // this.variants.push.apply(this.variants, tempVariants);
                    // tempVariants.length = 0;
                }
                if (this.variants.length === 0) {
                    this.variants.push.apply(this.variants, variants);
                }
            },
            removeVariant(selectedOption) {
                for (let v of this.variants) {
                    if (this.isOptionInSelectedOptions(v.selectedOptions, selectedOption)) {
                        if (this.isOptionValueInSelectedOptions(v.selectedOptions, selectedOption)) {
                            const index = v.selectedOptions.findIndex((el) => el.optionValue.value === selectedOption.optionValue.value);
                            v.selectedOptions.splice(index, 1);
                            v.name = this.generateVariantName(v.selectedOptions);
                        }
                    }
                }
                while(this.variants.findIndex((el) => el.selectedOptions.length < this.productOptionsLength) !== -1) {
                    this.variants.splice(this.variants.findIndex((el) => el.selectedOptions.length < this.productOptionsLength), 1)
                }
            },
            hasVariant (variant) {
                for (let v of this.variants) {
                    if (v.selectedOptions.length === variant.selectedOptions.length) {
                        let hasVariant = [];
                        for (let i=0; i<v.selectedOptions.length; ++i) {
                            if (v.selectedOptions[i].optionValue.value === variant.selectedOptions[i].optionValue.value ) {
                                hasVariant[i] = true;
                            } else {
                                hasVariant[i] = false;
                            }
                        }
                        if (hasVariant.every((el) => el === true )) {
                            return true;
                        }
                    }
                }
                return false;
            },
            showCategoryName(catObj) {
                return `${catObj.name}`
            },
            showBadgeName(obj) {
                return `${obj.name}`
            },
            submitFile() {
                this.imageIsUploading = true;
                setTimeout(() => this.savedImage(), 1000);
            },
            savedImage() {
                this.imageIsUploading = false;
                this.imageIsSaved = true;
            },
            onSubmit(e) {
                // Array.prototype.push.apply(this.product.variants, this.variants);
                this.product.variants.splice(0, this.product.variants.length, ...this.variants);
                this.removeEmptyOptions();
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
                this.resetUploader();
//                this.imageUrl = this.product.imageUrl;
                let el = this.$refs.form;
                el.scrollIntoView();
            },
            resetUploader() {
                this.$refs.vueclip.removeAllFiles();
                this.uploadedFiles = [];
                this.componentKey += 1;
            },
            complete(file, status, xhr) {
                console.log(file);
                // Adding server id to be used for deleting the file.
                if (status === 'success') {
                    let data = JSON.parse(xhr.response);
                    let image = {};
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
            addedFile(file) {
                this.uploadedFiles.push(file);
            },
            addFirstOption () {
                if (!this.product.hasVariants && !this.productOptions.length) {
                    this.addProductOption();
                }
            },
            addProductOption () {
                if (this.productOptions.length < 3) {
                    let productOption = {
                        name: '',
                        position: null,
                        values: [],
                    };
                    productOption.position = this.productOptions.length + 1;

                    // Es csak most bovitem a productOptions listat, az uj ures option-nal
                    this.productOptions.push(productOption);
                    this.$nextTick(() => {this.$refs.option[this.productOptions.length-1].focus();});
                }
            },
            removeProductOption (index) {
                this.productOptions.splice(index, 1);
            },
            removeEmptyOptions() {
                while(this.productOptions.findIndex((el) => el.values.length === 0) !== -1) {
                    this.productOptions.splice(this.productOptions.findIndex((el) => el.values.length === 0), 1)
                }
            },
            focusProductOption (index) {
                this.$refs.option[index].focus();
            }
        },
        created () {
            Array.prototype.push.apply(this.variants, this.product.variants);
            Array.prototype.push.apply(this.productOptions, this.product.options);
            this.extractOptionListFromVariants();
        },
//        // This is necessary, because of a bug in Dropzone, which ignores options: { uploadMultiple: false, }
       mounted() {
//            this.$refs.vueclip.uploader._uploader.hiddenFileInput.removeAttribute("multiple")
       }
    }
</script>

<style scoped="">
    ul {
        margin-bottom: 0;
    }
</style>