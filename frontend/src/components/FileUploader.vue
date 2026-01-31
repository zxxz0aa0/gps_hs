<script setup>
import { ref } from 'vue';
import api from '../services/api';

const fileInput = ref(null);
const uploadStatus = ref('');
const isUploading = ref(false);

const handleFileUpload = async () => {
    const file = fileInput.value.files[0];
    if (!file) return;

    const formData = new FormData();
    formData.append('file', file); // Make sure backend expects 'file'

    isUploading.value = true;
    uploadStatus.value = 'Uploading...';

    try {
        const response = await api.post('/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        uploadStatus.value = 'Upload successful: ' + (response.data.message || 'Data processed');
    } catch (error) {
        console.error(error);
        uploadStatus.value = 'Upload failed: ' + (error.response?.data?.message || error.message);
    } finally {
        isUploading.value = false;
    }
};
</script>

<template>
    <div class="file-uploader">
        <h3 style="color:gray;">Upload GPS Excel File</h3>
        <div class="upload-area">
            <input type="file" ref="fileInput" accept=".xlsx, .xls" @change="handleFileUpload" :disabled="isUploading" />
        </div>
        <div v-if="uploadStatus" class="status-message" :class="{ error: uploadStatus.includes('failed') }">
            {{ uploadStatus }}
        </div>
    </div>
</template>

<style scoped>
.file-uploader {
    padding: 20px;
    border: 2px dashed #ccc;
    border-radius: 8px;
    text-align: left;
    background-color: #f9f9f9;
}
.upload-area {
    margin: 20px 0;
    display: flex;
    justify-content: flex-start;
}
.status-message {
    margin-top: 10px;
    font-weight: bold;
    color: green;
}
.status-message.error {
    color: red;
}
input[type="file"] {
    color: red;
}
</style>
