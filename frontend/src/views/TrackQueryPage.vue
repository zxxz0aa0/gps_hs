<script setup>
import { ref } from 'vue';
import QueryForm from '../components/QueryForm.vue';
import TrackMap from '../components/TrackMap.vue';
import api from '../services/api';

const tracks = ref([]);
const isLoading = ref(false);
const error = ref('');

const handleSearch = async (params) => {
    if (!params.license_plate) {
        error.value = 'Please select a vehicle';
        return;
    }

    isLoading.value = true;
    error.value = '';
    tracks.value = [];

    try {
        const response = await api.get('/tracks', {
            params: {
                license_plate: params.license_plate,
                start_date: params.start_date,
                end_date: params.end_date
            }
        });
        
        // Ensure response structure matches what TrackMap expects
        // Expecting response.data to be the array of records or wrapped in data
        const data = response.data.data ? response.data.data : response.data;
        
        if (Array.isArray(data)) {
            tracks.value = data;
        } else {
            console.warn('Unexpected API response format', data);
            tracks.value = [];
        }

        if (tracks.value.length === 0) {
            error.value = 'No records found for this criteria.';
        }

    } catch (err) {
        console.error(err);
        error.value = 'Failed to fetch tracks: ' + (err.response?.data?.message || err.message);
    } finally {
        isLoading.value = false;
    }
};
</script>

<template>
    <div class="page-container">
        <h2>Trace Query</h2>
        
        <QueryForm @search="handleSearch" />
        
        <div v-if="isLoading" class="loading">Loading tracks...</div>
        <div v-if="error" class="error-msg">{{ error }}</div>
        
        <div class="map-container" v-show="!isLoading">
            <TrackMap :tracks="tracks" />
        </div>
    </div>
</template>

<style scoped>
.page-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}
.map-container {
    margin-top: 20px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.loading {
    text-align: center;
    padding: 20px;
    color: #007bff;
}
.error-msg {
    text-align: center;
    padding: 20px;
    color: red;
}
</style>
