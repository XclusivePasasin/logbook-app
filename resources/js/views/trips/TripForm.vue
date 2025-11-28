<template>
  <div class="trip-form">
    <n-card :title="isEdit ? 'Editar Viaje' : 'Nuevo Viaje'">
      <n-form
        ref="formRef"
        :model="formValue"
        :rules="rules"
        :label-placement="isMobile ? 'top' : 'left'"
        label-width="160"
        require-mark-placement="right-hanging"
      >
        <n-grid cols="1 s:1 m:2 l:2" responsive="screen" :x-gap="24" :y-gap="12">
          <n-form-item-gi label="Fecha" path="trip_date">
            <n-date-picker
              v-model:formatted-value="formValue.trip_date"
              value-format="yyyy-MM-dd"
              type="date"
              style="width: 100%"
            />
          </n-form-item-gi>
          <n-form-item-gi label="Hora" path="trip_time">
            <n-time-picker
              v-model:formatted-value="formValue.trip_time"
              value-format="HH:mm"
              format="HH:mm"
              style="width: 100%"
            />
          </n-form-item-gi>
          
          <n-form-item-gi label="Conductor" path="driver_id">
            <n-select
              v-model:value="formValue.driver_id"
              :options="driverOptions"
              placeholder="Seleccione conductor"
              filterable
            />
          </n-form-item-gi>
          <n-form-item-gi label="Pasajeros" path="passengers">
            <n-input-number v-model:value="formValue.passengers" min="1" style="width: 100%" />
          </n-form-item-gi>

          <n-form-item-gi label="Origen" path="origin">
            <n-input v-model:value="formValue.origin" placeholder="Lugar de salida" />
          </n-form-item-gi>
          <n-form-item-gi label="Destino" path="destination">
            <n-input v-model:value="formValue.destination" placeholder="Lugar de llegada" />
          </n-form-item-gi>

          <n-form-item-gi label="Método de Pago" path="payment_method">
            <n-select
              v-model:value="formValue.payment_method"
              :options="paymentOptions"
            />
          </n-form-item-gi>
          <n-form-item-gi label="Monto" path="amount">
            <n-input-number
              v-model:value="formValue.amount"
              :precision="2"
              :min="0"
              style="width: 100%"
            >
              <template #prefix>$</template>
            </n-input-number>
          </n-form-item-gi>

          <n-form-item-gi label="Equipo / Carro" path="equipment_number">
            <n-input v-model:value="formValue.equipment_number" placeholder="Número de equipo" />
          </n-form-item-gi>
          <n-form-item-gi label="Tipo de Viaje">
            <n-space>
              <n-checkbox v-model:checked="formValue.is_ida">
                IDA
              </n-checkbox>
              <n-checkbox v-model:checked="formValue.is_vuelta">
                VUELTA
              </n-checkbox>
            </n-space>
          </n-form-item-gi>

          <n-form-item-gi :span="2" label="Notas" path="notes">
            <n-input
              v-model:value="formValue.notes"
              type="textarea"
              placeholder="Notas adicionales"
            />
          </n-form-item-gi>
        </n-grid>

        <n-space justify="end">
          <n-button @click="router.back()">Cancelar</n-button>
          <n-button type="primary" :loading="loading" @click="handleSubmit">
            Guardar
          </n-button>
        </n-space>
      </n-form>
    </n-card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import { useMessage } from 'naive-ui';
import api from '../../api/axios';
import { useBreakpoints, breakpointsTailwind } from '@vueuse/core';

const router = useRouter();
const route = useRoute();
const message = useMessage();
const breakpoints = useBreakpoints(breakpointsTailwind);
const isMobile = breakpoints.smaller('md');

const formRef = ref(null);
const loading = ref(false);
const drivers = ref([]);

const isEdit = computed(() => !!route.params.id);

const formValue = ref({
  trip_date: null,
  trip_time: null,
  passengers: 1,
  origin: '',
  destination: '',
  payment_method: 'E',
  equipment_number: '',
  is_ida: false,
  is_vuelta: false,
  amount: 0,
  driver_id: null,
  notes: '',
});

const rules = {
  trip_date: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
  trip_time: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
  driver_id: { required: true, type: 'number', message: 'Requerido', trigger: ['blur', 'change'] },
  origin: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
  destination: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
  amount: { required: true, type: 'number', message: 'Requerido', trigger: ['blur', 'change'] },
};

const paymentOptions = [
  { label: 'Efectivo', value: 'E' },
  { label: 'Cargo Habitación', value: 'CH' },
  { label: 'Tarjeta', value: 'TJ' },
];

const driverOptions = computed(() => 
  drivers.value.map(d => ({ label: d.name, value: d.id }))
);

async function fetchDrivers() {
  try {
    const response = await api.get('/drivers?active=1');
    drivers.value = response.data;
  } catch (error) {
    message.error('Error al cargar conductores');
  }
}

async function fetchTrip(id) {
  try {
    const response = await api.get(`/trips/${id}`);
    const data = response.data;
    
    // Format date and time for the form
    if (data.trip_date) {
      // Ensure date is in YYYY-MM-DD format
      data.trip_date = data.trip_date.split('T')[0];
    }
    
    if (data.trip_time) {
      // Ensure time is in HH:mm format
      if (data.trip_time.includes(':')) {
        data.trip_time = data.trip_time.substring(0, 5);
      }
    }
    
    // Ensure booleans for checkboxes
    data.is_ida = !!data.is_ida;
    data.is_vuelta = !!data.is_vuelta;
    
    // Ensure number for amount
    data.amount = Number(data.amount);
    
    formValue.value = data;
  } catch (error) {
    message.error('Error al cargar viaje');
    router.push({ name: 'trips.index' });
  }
}

async function handleSubmit() {
  formRef.value?.validate(async (errors) => {
    if (!errors) {
      loading.value = true;
      try {
        if (isEdit.value) {
          await api.put(`/trips/${route.params.id}`, formValue.value);
          message.success('Viaje actualizado');
        } else {
          await api.post('/trips', formValue.value);
          message.success('Viaje creado');
        }
        router.push({ name: 'trips.index' });
      } catch (error) {
        if (error.response?.data?.errors) {
          // Handle validation errors from backend
          message.error('Error de validación');
        } else {
          message.error('Error al guardar');
        }
      } finally {
        loading.value = false;
      }
    }
  });
}

onMounted(async () => {
  await fetchDrivers();
  if (isEdit.value) {
    await fetchTrip(route.params.id);
  } else {
    // Set default date/time
    const now = new Date();
    formValue.value.trip_date = now.toISOString().split('T')[0];
    formValue.value.trip_time = now.toTimeString().slice(0, 5);
  }
});
</script>
