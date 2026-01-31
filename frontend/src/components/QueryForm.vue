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
            <label>車牌號碼</label>
            <select v-model="licensePlate" :disabled="isLoadingVehicles">
                <option value="">-- 選擇車輛 --</option>
                <option v-for="v in vehicles" :key="v.license_plate" :value="v.license_plate">
                    {{ v.license_plate }} ({{ v.driver_name || 'N/A' }})
                </option>
            </select>
        </div>
        <div class="form-group-pair">
            <label>開始時間</label>
            <div class="input-pair">
                <input v-model="startDate" type="date" />
                <input v-model="startTime" type="time" />
            </div>
        </div>
        <div class="form-group-pair">
            <label>結束時間</label>
            <div class="input-pair">
                <input v-model="endDate" type="date" />
                <input v-model="endTime" type="time" />
            </div>
        </div>
        <button @click="onSearch" class="search-btn" :disabled="!licensePlate">查詢</button>
    </div>
</template>

<style scoped>
.query-form {
    display: flex;
    gap: 20px;
    align-items: flex-end;
    background: #fff;
    padding: 16px 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    margin-bottom: 20px;
    flex-wrap: wrap;
}
.form-group,
.form-group-pair {
    display: flex;
    flex-direction: column;
}
.input-pair {
    display: flex;
    gap: 8px;
}
.input-pair input[type="date"] {
    min-width: 140px;
}
.input-pair input[type="time"] {
    min-width: 100px;
}
label {
    font-size: 0.85em;
    margin-bottom: 6px;
    color: #555;
    font-weight: 500;
}
input, select {
    padding: 8px 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}
select {
    min-width: 180px;
}
.search-btn {
    padding: 8px 24px;
    background-color: #007bff;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-weight: 500;
}
.search-btn:hover:not(:disabled) {
    background-color: #0056b3;
}
.search-btn:disabled {
    background-color: #ccc;
    cursor: not-allowed;
}
</style>
