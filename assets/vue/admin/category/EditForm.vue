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
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                    <label class="col-form-label required" for="categoryName">Kategória</label>
                </div>
                <div class="col-sm-12 col-md-8 col-xl-7 mr-auto">
                    <input type="text" v-model="category.name" id="categoryName" required="required" maxlength="100" class="form-control">
                </div>

            </div>
        </div>
        <div v-if="category.imageUrl" class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                </div>
                <div class="col-sm-12 col-md-5 col-xl-4 mr-auto">
                    <div class="w-100">
                        <img :src="category.imageUrl" class="img-thumbnail" style="max-height: 60px; width: 320px;" />
                    </div>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                    <label class="col-form-label required" for="categoryParentCategory">Szülőkategória</label>
                </div>
                <div class="col-sm-12 col-md-5 col-xl-4 mr-auto">
                    <!--Egy select-ben a v-model erteke es az option :value meg kell egyezzen,
                    hogy ki legyen valasztva az opcio a legorduloben-->
                    <select v-model="category.parentCategory" id="categoryParentCategory" required="required" class="custom-select form-control">
                        <option value=""> - nincs - </option>
                        <template v-for="cat in parentCategories" track-by="cat.id">
                            <option :value="cat">{{cat.name}}</option>
                        </template>
                    </select>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                    <label class="form-control-label">Engedélyezve</label>
                </div>
                <div class="col-sm-8 col-lg-5 ">
                    <input v-model="category.enabled" type="checkbox" id="categoryEnabled" required="required" class="form-check-input" data-switch="success">
                    <label class="form-check-label required" for="categoryEnabled" data-on-label="Igen" data-off-label="Nem"></label>
                </div>
            </div>
        </div>
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                    <label class="col-form-label required" for="categorySlug">URL slug</label>
                </div>
                <div class="col-sm-12 col-md-8 col-xl-7 mr-auto">
                    <input v-model="category.slug" type="text" id="categorySlug" required="required" class="form-control">
                </div>
            </div>
        </div>
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                    <label class="col-form-label required" for="categoryDescription">Leírás</label>
                </div>
                <div class="col-sm-12 col-md-8 col-xl-7 mr-auto">
                    <textarea v-model="category.description" id="categoryDescription" required="required" rows="5" class="form-control"></textarea>

                </div>
            </div>
        </div>
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                    <div class="col-form-label">Kép</div>
                </div>
                <div class="col-sm-12 col-md-5 col-xl-4 mr-auto">
                    <vue-clip
                            :options="options"
                            :on-max-files="maxFilesReached"
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
                                <div v-for="file in files" class="uploader-file d-flex justify-content-start align-items-center mb-2X">
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
                    
                    
                    <!--<file-upload-->
                            <!--v-model="imageUrl"-->
                            <!--:resetForm="resetForm"-->
                    <!--&gt;-->
                        <!--<div slot="activator">-->
                            <!--<div v-if="!imageUrl" class="w-100 mt-2">-->
                                <!--<span class="p-2 my-2">Képválasztás</span>-->
                            <!--</div>-->
                            <!--<div v-else class="w-100 mt-2">-->
                                <!--<img :src="imageUrl.imageUrl" class="img-thumbnail" style="max-height: 40px; width: 160px;" />-->
                            <!--</div>-->
                        <!--</div>-->
                    <!--</file-upload>-->
                    <!--<div v-if="imageUrl && isImageSaved == false">-->
                        <!--<button @click.prevent="submitFile" :isImageUploading="isImageUploading" class="btn btn-outline-secondary" >Mentés</button>-->
                    <!--</div>-->
                </div>
            </div>
        </div>
        <div class="mb-2">
            <div class="form-group row">
                <div class="col-sm-12 col-md-2">
                </div>
                <div class="col-md-12 col-lg-7 mr-auto">
                    <button @click.prevent="onSubmit" type="submit" class="btn btn-primary" formnovalidate="">{{ category.name ? 'Mentés' : 'Hozzáadás' }}</button>
                    <a href="#" v-if="category.name" @click.prevent="onCancelClicked" class="btn btn-outline-secondary">Mégse</a>
                </div>
            </div>
        </div>
    </form>
</template>

<script>
    import FileUpload from './FileUpload'

    const initialData = () => {
        return {
            options: {
                url: '/hu/admin/api/images/category/',
                headers: {
//                        credentials: 'same-origin',
//                        type: 'multipart/form-data',  // ??
                },
                paramName: 'imageFile',
                uploadMultiple: false,
                parallelUploads: 1,
//                maxFiles: {
//                    limit: 2,
//                    message: 'You can only upload a max of 5 files'
//                },
                acceptedFiles: {
                    extensions: ['image/*'],
                    message: 'You are uploading an invalid file'
                },
            },
        }
    };
    
    export default {
        components: {
            FileUpload: FileUpload,
        },
        props: [
            'category',
            'parentCategories',
            'errors',
            'resetForm',
        ],
        data: function () {
            return {
                localErrors: [],

                isImageUploading: false,
                isImageSaved: false,
                
                imageUrl: null,
                options: initialData().options,
                files: [],
                componentKey: 0,  // can be somethingElse, whatever
            }
        },
        watch: {
            category: 'updateForm',
            imageUrl: {
                handler: function () {
                    this.isImageSaved = false
                },
                deep: true
            },
            resetForm: 'resetUploader',
        },
        methods: {
            submitFile () {
                this.isImageUploading = true;
                setTimeout(() => this.savedImage(), 1000)
            },
            savedImage () {
                this.isImageUploading = false;
                this.isImageSaved = true
            },
            onSubmit (e) {
                if (this.validatedForm(e)) {
                    this.$emit('submit', this.category);
                }
            },
            onCancelClicked () {
                this.$emit('click', this.category)
            },
            validatedForm (e) {
                this.localErrors = [];
                if (!this.category.name) {
                    this.localErrors.push('Add meg a kategória nevét.');
                }
                if (!this.category.slug) {
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
                this.imageUrl = this.category.imageUrl;
                let el = this.$refs.form;
                el.scrollIntoView();
            },
            resetUploader () {
                this.$refs.vueclip.removeAllFiles();
                this.files = [];
                this.componentKey += 1;
            },
            maxFilesReached (file) {
                console.log(file.errorMessage);
            },
            
            complete (file, status, xhr) {
                // Adding server id to be used for deleting the file.
                if (status === 'success') {
                    // xhr.response is a json string which needs to be decoded
                    let data = JSON.parse(xhr.response);
//                    console.log(data);
                    for (let item of Object.values(data.images)) {
                        file.addAttribute('id', item.id);
                        this.category.image = item;
                        this.category.imageUrl = item.file;
                    }
                    //                    this.imageFile = item.id;
                } else {
                    let item = JSON.parse(xhr.response);
                    this.errors.push(item.errors.imageFile);
                    console.log(item.errors.imageFile)
                }
            },
            addedFile (file) {
                this.files.push(file);
            },
        },
        // This is necessary, because of a bug in Dropzone, which ignores options: { uploadMultiple: false, }
        mounted() {
            this.$refs.vueclip.uploader._uploader.hiddenFileInput.removeAttribute("multiple")
        }
    }
</script>

<style scoped="">
    ul {
        margin-bottom: 0;
    }

</style>