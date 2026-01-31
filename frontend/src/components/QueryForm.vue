<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const emit = defineEmits(['search']);

const vehicles = ref([]);
const licensePlate = ref('');
const startDate = ref('');
const endDate = ref('');
const startTime = ref('');
const endTime = ref('');
const isLoadingVehicles = ref(false);

const fetchVehicles = async () => {
    isLoadingVehicles.value = true;
    try {
        const response = await api.get('/vehicles');
        vehicles.value = response.data;
    } catch (err) {
        console.error('Failed to fetch vehicles:', err);
    } finally {
        isLoadingVehicles.value = false;
    }
};

const onSearch = () => {
    emit('search', {
        license_plate: licensePlate.value,
        start_date: startDate.value,
        end_date: endDate.value,
        start_time: startTime.value,
        end_time: endTime.value
    });
};

onMounted(() => {
    fetchVehicles();
});
</script>

<template>
    <div class="query-form">
        <div class="form-group">
            <label>License Plate:</label>
            <select v-model="licensePlate" :disabled="isLoadingVehicles">
                <option value="">-- Select Vehicle --</option>
                <option v-for="v in vehicles" :key="v.license_plate" :value="v.license_plate">
                    {{ v.license_plate }} ({{ v.driver_name || 'N/A' }})
                </option>
            </select>
        </div>
        <div class="form-group">
            <label>Start Date:</label>
            <input v-model="startDate" type="date" />
        </div>
        <div class="form-group">
            <label>Start Time:</label>
            <input v-model="startTime" type="time" />
        </div>
        <div class="form-group">
            <label>End Date:</label>
            <input v-model="endDate" type="date" />
        </div>
        <div class="form-group">
            <label>End Time:</label>
            <input v-model="endTime" type="time" />
        </div>
        <button @click="onSearch" class="search-btn" :disabled="!licensePlate">Search</button>
    </div>
</template>

<style scoped>
.query-form {
    display: flex;
    gap: 15px;
    align-items: flex-end;
    background: #fff;
    padding: 15px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.form-group {
    display: flex;
    flex-direction: column;
}
label {
    font-size: 0.9em;
    margin-bottom: 5px;
    color: #666;
}
input, select {
    padding: 8px;
    border: 1px solid #ddd;
    border-radius: 4px;
    min-width: 150px;
}
.search-btn {
    padding: 8px 20px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
}
.search-btn:hover:not(:disabled) {
    background-color: #0056b3;
}
.search-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
</style>
