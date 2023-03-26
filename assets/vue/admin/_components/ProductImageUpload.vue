<template>
    <vue-clip
            :options="options"

            :on-complete="complete"
            :on-added-file="addedFile"
            :onQueueComplete="resetUploader"
            class="uploader"
            :key="componentKey"
            ref="vueclip"
            role="button"
            tabindex="0"
    >
        <template v-slot:clip-uploader-action="props">
            <div class="uploader-action" v-bind:class="{dragging: props.dragging}" tabindexZ="0">
                <div class="dz-message">
                    <div class="d-flex">
                        <i class="fas fa-arrow-circle-up fa-2x"></i>
                    </div>
                    <a class="btn btn-secondary mt-3">Kép hozzáadása</a>
                    <div class="d-flex text-muted mt-2">
                        vagy húzd be ide a fájlt a feltöltéshez
                    </div>
                </div>
            </div>
        </template>

        <template v-slot:clip-uploader-body="props">
            <div class="uploader-files" v-if="uploadedFiles.length">
                <div v-for="file in uploadedFiles" class="uploader-file d-flex justify-content-start align-items-center">
                    <div class="file-avatar mr-2">
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
                            <span class="file-status text-success">{{ file.progress }}%</span> |
                            <span class="file-size">ImageEntityId: {{ file.customAttributes.id }} </span>
                            <span class="file-status text-danger" v-if="file.progress === 0 && file.errorMessage">{{ file.errorMessage }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </vue-clip>
</template>

<script>
    const initialData = () => {
        return {
            options: {
                url: '/hu/admin/api/upload/productImage/',
                headers: {
//                        credentials: 'same-origin',
//                        type: 'multipart/form-data',  // ??
                },
                paramName: 'imageFile',
                maxFiles: {
                    limit: 5,
                    message: 'You can only upload a max of 5 files'
                },
                // uploadMultiple: true
                // The uploadMultiple options works a little different. If set to true multiple files will be sent at once
                // in a single request and also user will be able to select multiple files.
                //
                // If you set it to false the user can still select multiple files, but each file will be sent as a
                // single request. Which means for 5 files 5 HTTP requests will fire.
                acceptedFiles: {
                    extensions: ['image/*'],
                    message: 'You are uploading an invalid file'
                },
            },
        }
    };

    export default {
        props: [
            'images',
        ],
        data: function () {
            return {
                imageIsUploading: false,
                imageIsSaved: false,

                imageUrl: null,
                options: initialData().options,
                uploadedFiles: [],
                errors: [],

                componentKey: 0,  // can be somethingElse, whatever
            }
        },
        watch: {
            imageUrl: {
                handler () { this.imageIsSaved = false },
                deep: true
            },
            resetForm: 'resetUploader',
        },
        methods: {
            // submitFile () {
            //     this.imageIsUploading = true;
            //     setTimeout(() => this.savedImage(), 1000);
            // },
            // savedImage () {
            //     this.imageIsUploading = false;
            //     this.imageIsSaved = true;
            // },
            resetUploader () {
                this.$refs.vueclip.removeAllFiles();
                this.uploadedFiles = [];
                this.componentKey += 1;
            },
            complete (file, status, xhr) {
                // console.log(file);
                // Adding server id to be used for deleting the file.
                if (status === 'success') {
                    let data = JSON.parse(xhr.response);
                    let imageEntity = {};
                    imageEntity = data.images[0];
                    // imageEntity.imageUrl = data.images[0].file;
                    // image.ordering = this.images.length;
                    // this.images.push(image);

                    file.addAttribute('id', imageEntity.id);
                    // console.log(file.customAttributes.id);   ////////////
                    this.$emit('success', imageEntity);
                } else {
                    let json = JSON.parse(xhr.response);
                    // console.log(json.errors)
                    
                    this.errors.push(json.errors);
                    this.$emit('error', json.errors);
                }
            },
            addedFile (file) {
                this.uploadedFiles.push(file);
            },
        },
        
       mounted() {
           // This is necessary for single file upload (which is not this case), because of a bug in Dropzone,
           // which ignores options: { uploadMultiple: false, }
           // this.$refs.vueclip.uploader._uploader.hiddenFileInput.removeAttribute("multiple")
         }
    }
</script>

<style scoped>
</style>