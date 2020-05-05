<template>
    <vue-clip
            :options="options"

            :on-complete="complete"
            :on-added-file="addedFile"
            class="uploader w-100"
            :key="componentKey"
            ref="vueclip"
    >
        <template v-slot:clip-uploader-action="props">
            <div class="uploader-action" v-bind:class="{dragging: props.dragging}">
                <div class="dz-message">
                    <i class="fas fa-upload mb-2"></i>
                    <p class="mb-0">Képfeltöltés... </p>
                </div>
            </div>
        </template>

        <template v-slot:clip-uploader-body="props">
            <div class="uploader-files" v-if="uploadedFiles.length">
                <div v-for="file in uploadedFiles" class="uploader-file d-flex justify-content-start align-items-center mb-2X">
                    <div class="file-avatar mr-3">
                        <img v-bind:src="file.dataUrl" class="img-thumbnail" />
                    </div>
                    <div class="file-details">
                        <div class="file-name">
                            {{ file.name }}
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
                url: '/hu/admin/api/images/product/',
                headers: {
//                        credentials: 'same-origin',
//                        type: 'multipart/form-data',  // ??
                },
                paramName: 'imageFile',
                uploadMultiple: false, // Lásd a mounted() részt, ott van egy kötelező kódrész
                acceptedFiles: {
                    extensions: ['image/*'],
                    message: 'You are uploading an invalid file'
                },
            },
        }
    };
    
    export default {
        props: [
        ],
        data: function () {
            return {
                imageIsUploading: false,
                imageIsSaved: false,

                imageUrl: null,
                options: initialData().options,
                uploadedFiles: [],

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
            submitFile () {
                this.imageIsUploading = true;
                setTimeout(() => this.savedImage(), 1000);
            },
            savedImage () {
                this.imageIsUploading = false;
                this.imageIsSaved = true;
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
            resetUploader () {
                this.$refs.vueclip.removeAllFiles();
                this.uploadedFiles = [];
                this.componentKey += 1;
            },
            complete (file, status, xhr) {
                console.log(file);
                // Adding server id to be used for deleting the file.
                if (status === 'success') {
                    let data = JSON.parse(xhr.response);
                    let image = {};
                    image.image = data.images[0];
                    image.imageUrl = data.images[0].file;

                    // FONTOS!!
                    // A mező id azonosítóját kell az alábbi sorban megadni: 'cms_page_form_imageId'
                    document.getElementsByClassName('V--imageId')[0].value = image.image.id;
                    file.addAttribute('id', image.image.id);

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
        // This is necessary, because of a bug in Dropzone, which ignores options: { uploadMultiple: false, }
       mounted() {
           this.$refs.vueclip.uploader._uploader.hiddenFileInput.removeAttribute("multiple")
         }
    }
</script>

<style scoped>
</style>