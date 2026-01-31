<script setup>
import FileUploader from '../components/FileUploader.vue';
</script>

<template>
    <div class="page-container">
        <h2>Data Upload</h2>
        <p class="description">Upload your GPS Excel files here. The system will parse and store the daily track records.</p>
        <FileUploader />
    </div>
</template>

<style scoped>
.page-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}
.description {
    margin-bottom: 20px;
    color: #666;
}
</style>
