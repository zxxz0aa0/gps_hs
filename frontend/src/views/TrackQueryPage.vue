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

const formatDate = (dateStr) => {
    if (!dateStr) return '';
    return dateStr.slice(0, 10);
};

const formatTime = (timeStr) => {
    if (!timeStr) return '';
    return timeStr.slice(0, 5);
};

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
        <div class="header-section">
            <div class="query-card">
                <QueryForm @search="handleSearch" />
            </div>
        </div>

        <div v-if="isLoading" class="state-container loading">
            <div class="spinner"></div>
            <p>正在載入軌跡數據...</p>
        </div>
        
        <div v-else-if="error" class="state-container error">
            <div class="error-icon">⚠️</div>
            <p>{{ error }}</p>
        </div>

        <div class="content-layout" v-show="!isLoading">
            <div class="sidebar-panel">
                <div class="panel-header">
                    <span class="title">軌跡列表</span>
                    <span class="badge" v-if="tracks.length">{{ tracks.length }} Points</span>
                </div>
                
                <div v-if="tracks.length === 0" class="empty-state">
                    Please select query conditions.
                </div>

                <div v-else v-bind="containerProps" class="virtual-list-container custom-scrollbar">
                    <div v-bind="wrapperProps">
                        <div
                            v-for="{ data: track, index } in list"
                            :key="index"
                            class="track-item"
                            :class="{ active: selectedIndex === index }"
                            @click="handlePointClick(track, index)"
                        >
                            <div class="item-icon">📍</div>
                            <div class="item-content">
                                <div class="track-time">{{ formatDate(track.date) }} {{ formatTime(track.time) }}</div>
                                <div class="track-location" :title="track.location">
                                    {{ track.location || '未知地址' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="map-panel">
                <TrackMap ref="mapRef" :tracks="tracks" />
            </div>
        </div>
    </div>
</template>

<style scoped>
/* Page Layout */
.page-container {
    max-width: 100%;
    height: 100%;
    display: flex;
    flex-direction: column;
    background-color: #f0f2f5;
    overflow: hidden; /* Prevent full page scroll */
}

/* Header Section */
.header-section {
    padding: 0px 0px;
    background: #480d0d;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    z-index: 10;
    flex-shrink: 0;
    display: flex;
    align-items: center;
    gap: 24px;
    justify-content: space-between;
}

.page-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #1a1a1a;
    display: flex;
    align-items: center;
    gap: 8px;
    margin: 0;
    white-space: nowrap;
}

/* Content Layout (Main Area) */
.content-layout {
    flex: 1;
    display: flex;
    overflow: hidden; /* Important for inner scrolling */
    position: relative;
}

/* Sidebar Panel */
.sidebar-panel {
    width: 270px;
    background: #ffffff;
    border-right: 1px solid #e0e0e0;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    transition: transform 0.3s ease;
    z-index: 5;
}

.panel-header {
    padding: 16px;
    border-bottom: 1px solid #f0f0f0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #fafafa;
}

.panel-header .title {
    font-weight: 600;
    color: #333;
}

.badge {
    background: #e3f2fd;
    color: #1976d2;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 0.75rem;
    font-weight: 500;
}

/* Virtual List Container */
.virtual-list-container {
    flex: 1;
    overflow-y: auto;
}

/* List Items */
.track-item {
    display: flex;
    gap: 12px;
    padding: 12px 16px;
    border-bottom: 1px solid #f5f5f5;
    cursor: pointer;
    transition: all 0.2s ease;
}

.track-item:hover {
    background-color: #f8f9fa;
}

.track-item.active {
    background-color: #f0f7ff;
    border-left: 4px solid #1890ff;
}

.item-icon {
    font-size: 1.2rem;
    opacity: 0.7;
}

.item-content {
    flex: 1;
    overflow: hidden;
}

.track-time {
    font-size: 0.85rem;
    color: #888;
    margin-bottom: 4px;
    font-variant-numeric: tabular-nums;
}

.track-location {
    font-size: 0.95rem;
    color: #2c3e50;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    font-weight: 500;
}

/* Map Panel */
.map-panel {
    flex: 1;
    background: #eef2f6;
    position: relative;
    /* Ensure map container allows map to fill */
    display: flex;
    flex-direction: column;
}

/* States */
.state-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 40px;
    color: #666;
    height: 100%;
    width: 100%;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #f3f3f3;
    border-top: 3px solid #1890ff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

.error-icon {
    font-size: 2rem;
    margin-bottom: 10px;
}

.error {
    color: #ff4d4f;
}

.empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #999;
}

/* Animations */
@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar {
    width: 6px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 3px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: #999;
}

/* Responsive Design */
@media (max-width: 768px) {
    .header-section {
        flex-direction: column;
        align-items: stretch;
        gap: 12px;
    }
    
    .content-layout {
        flex-direction: column;
    }
    
    .sidebar-panel {
        width: 100%;
        height: 40vh; /* Fixed height for list on mobile */
        border-right: none;
        border-bottom: 1px solid #e0e0e0;
    }
    
    .map-panel {
        height: 60vh;
    }
}
</style>

