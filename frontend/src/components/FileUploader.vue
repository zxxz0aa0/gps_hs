<script setup>
import { ref } from 'vue';
import api from '../services/api';

const fileInput = ref(null);
const uploadStatus = ref('');
const isUploading = ref(false);

const handleFileUpload = async () => {
    const files = fileInput.value.files;
    if (!files || files.length === 0) return;

    isUploading.value = true;
    let successCount = 0;
    let failCount = 0;
    const totalFiles = files.length;

    for (let i = 0; i < totalFiles; i++) {
        const file = files[i];
        const formData = new FormData();
        formData.append('file', file);

        uploadStatus.value = `Uploading ${file.name} (${i + 1}/${totalFiles})...`;

        try {
            await api.post('/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data'
                }
            });
            successCount++;
        } catch (error) {
            console.error(`Failed to upload ${file.name}:`, error);
            failCount++;
        }
    }

    isUploading.value = false;
    
    if (failCount === 0) {
        uploadStatus.value = `Successfully uploaded ${successCount} file(s).`;
    } else {
        uploadStatus.value = `Finished. Success: ${successCount}, Failed: ${failCount}.`;
    }
};
</script>

<template>
    <div class="file-uploader">
        <h3 style="color:gray;">Upload GPS Excel Files</h3>
        <div class="upload-area">
            <input type="file" ref="fileInput" accept=".xlsx, .xls" multiple @change="handleFileUpload" :disabled="isUploading" />
        </div>
        <div v-if="uploadStatus" class="status-message" :class="{ error: uploadStatus.includes('Failed') && !uploadStatus.includes('Success') }">
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
