<script setup>
import { ref } from 'vue';
import { useVirtualList } from '@vueuse/core';
import QueryForm from '../components/QueryForm.vue';
import TrackMap from '../components/TrackMap.vue';
import api from '../services/api';

const tracks = ref([]);
const isLoading = ref(false);
const error = ref('');
const mapRef = ref(null);
const selectedIndex = ref(-1);

const { list, containerProps, wrapperProps } = useVirtualList(tracks, {
    itemHeight: 60
});

const handlePointClick = (track, index) => {
    selectedIndex.value = index;
    mapRef.value?.highlightPoint(track);
};

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
                end_date: params.end_date,
                start_time: params.start_time,
                end_time: params.end_time
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

        <div class="content-layout" v-show="!isLoading && tracks.length > 0">
            <div class="track-list">
                <div class="list-header">
                    <span>軌跡點列表 ({{ tracks.length }})</span>
                </div>
                <div v-bind="containerProps" class="list-container">
                    <div v-bind="wrapperProps">
                        <div
                            v-for="{ data: track, index } in list"
                            :key="index"
                            class="track-item"
                            :class="{ active: selectedIndex === index }"
                            @click="handlePointClick(track, index)"
                        >
                            <div class="track-time">{{ track.date }} {{ track.time }}</div>
                            <div class="track-location">{{ track.location || '未知地址' }}</div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="map-container">
                <TrackMap ref="mapRef" :tracks="tracks" />
            </div>
        </div>
    </div>
</template>

<style scoped>
.page-container {
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}
.content-layout {
    display: flex;
    gap: 20px;
    margin-top: 20px;
}
.track-list {
    width: 320px;
    flex-shrink: 0;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    background: #fff;
    display: flex;
    flex-direction: column;
}
.list-header {
    padding: 12px 16px;
    font-weight: 600;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
    border-radius: 8px 8px 0 0;
}
.list-container {
    height: 500px;
    overflow-y: auto;
}
.track-item {
    padding: 10px 16px;
    border-bottom: 1px solid #eee;
    cursor: pointer;
    transition: background 0.15s;
}
.track-item:hover {
    background: #f5f5f5;
}
.track-item.active {
    background: #e3f2fd;
    border-left: 3px solid #1E90FF;
}
.track-time {
    font-size: 13px;
    color: #666;
    margin-bottom: 4px;
}
.track-location {
    font-size: 14px;
    color: #333;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.map-container {
    flex: 1;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-radius: 8px;
    overflow: hidden;
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
