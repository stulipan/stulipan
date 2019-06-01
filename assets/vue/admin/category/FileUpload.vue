<template>
    <div>
        <div @click="launchFilePicker">
            <slot name="activator"></slot>
        </div>
        
        <input v-on:change="onFileChange" ref="imageFile" type="file" :name="uploadFieldName" id="category_imageFile" placeholder="Képfeltöltés..." class="custom-file-input" style="display: none;">
        
        <!--<div class="input-group">-->
            <!--<div class="custom-file">-->
                <!--<input v-on:change="onFileChange()" ref="imageFile" type="file" :name="uploadFieldName" id="category_imageFile" placeholder="Képfeltöltés..." class="custom-file-input">-->
                <!--<label for="category_imageFile" class="custom-file-label">{{ !resetForm && filename ? filename : 'Képfeltöltés...' }}</label>-->
            <!--</div>-->
            <!--<div v-if="error.length" class="w-100 mt-1">-->
                <!--<span class="text-danger">{{ error }}</span>-->
            <!--</div>-->
            <!--<template v-if="isImageLoading">-->
                <!--<div class="d-flex w-100 mt-2">-->
                <!--<span class="mb-2">-->
                    <!--<i class="fas fa-lg fa-spinner fa-spin mr-2"></i>-->
                    <!--Kép feltöltése...-->
                <!--</span>-->
                <!--</div>-->
            <!--</template>-->
            <!--<div v-if="uploadedImageURL && !resetForm" class="w-100 mt-2">-->
                <!--<img :src="uploadedImageURL" class="img-thumbnail" style="max-height: 40px; width: 160px;" />-->
            <!--</div>-->
        <!--</div>-->
    
    </div>
</template>

<script>
    export default {
        name: 'file-upload',
        props: {
//            value: Number, // Use "value" here to enable compatibility with v-model
            imageURL: String,
            resetForm: Boolean,
            
            value: Object,
        },
        data: function () {
            return {
                isImageLoading: false,
                uploadFieldName: 'imageFile',
                imageFile: '',
                filename: '',
                error: '',
                uploadedImageURL: '',
            }
        },
        watch: {
            imageURL: 'setUploadedImageURL',
        },
        methods: {
            launchFilePicker () {
                this.$refs.imageFile.click();
            },
            onFileChange () {
                this.imageFile = this.$refs.imageFile.files[0];
                this.filename = this.$refs.imageFile.files[0].name;
//                this.submitFile();

                
                
                // ez nem volt itt
                let imageFile = this.$refs.imageFile.files[0];
                let formData = new FormData();
                let imageUrl = URL.createObjectURL(imageFile);
                formData.append('imageFile', imageFile);

                this.$emit('input', {formData, imageUrl})
            },
            submitFile () {
                let formData = new FormData();
                let imageUrl = URL.createObjectURL(this.imageFile);
                formData.append('imageFile', this.imageFile);
                
                this.$emit('input', {formData, imageUrl})

                this.isImageLoading = true;
                this.error = '';

//                this.$http.post('/admin/api/images', formData, {
//                    headers: {
//                        'Content-Type': 'multipart/form-data'
//                    }})
//                    .then(
//                        response => {
//                            console.log(response.body);
//                            this.uploadedImageURL = response.body.file;
//                            this.$emit('input', response.body.id);
//                            this.isImageLoading = false;
//                        },
//                        response => {
//                            if (response.status === 422) {
//                                this.isImageLoading = false;
//                                this.error = response.body.errors.imageFile;
//                            }
//                        })
            },
            setUploadedImageURL () {
                this.uploadedImageURL = this.imageURL;
                this.filename = '';
            },
        }
    }
</script>

<style scoped="">
</style>

