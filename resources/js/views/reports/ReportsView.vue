<template>
  <div class="reports-view">
    <n-card title="Reportes y Estadísticas">
      <n-space vertical size="large">
        <n-form :inline="!isMobile" label-placement="left">
          <n-form-item label="Fecha">
            <n-date-picker
              v-model:formatted-value="selectedDate"
              type="date"
              value-format="yyyy-MM-dd"
              clearable
              @update:formatted-value="handleDateChange"
              style="width: 100%"
            />
          </n-form-item>
          <n-form-item label="Coordinador">
            <n-input
              v-model:value="coordinatorName"
              placeholder="Nombre del coordinador"
              style="width: 200px"
            />
          </n-form-item>
          <n-form-item label="Turno">
            <n-select
              v-model:value="shift"
              :options="shiftOptions"
              placeholder="Seleccionar turno"
              style="width: 150px"
            />
          </n-form-item>
          <n-form-item>
            <n-space>
              <n-button type="primary" @click="fetchReports" :loading="loading">
                Actualizar
              </n-button>
              <n-button type="warning" @click="downloadPDF" :loading="downloadingPDF" :disabled="!canGeneratePDF">
                Generar PDF
              </n-button>
            </n-space>
          </n-form-item>
        </n-form>

        <n-grid cols="1 s:2 m:3" responsive="screen" :x-gap="12" :y-gap="12">
          <n-grid-item>
            <n-statistic label="Total Viajes" :value="stats.total_trips" />
          </n-grid-item>
          <n-grid-item>
            <n-statistic label="Monto Total" :value="stats.total_amount">
              <template #prefix>$</template>
            </n-statistic>
          </n-grid-item>
          <n-grid-item>
            <n-statistic label="Total Pasajeros" :value="stats.total_passengers" />
          </n-grid-item>
        </n-grid>

        <n-divider />

        <n-grid cols="1 m:2" responsive="screen" :x-gap="24" :y-gap="24">
          <n-grid-item>
            <n-card title="Por Método de Pago" size="small">
              <!-- Mobile View -->
              <n-space v-if="isMobile" vertical size="small">
                <n-card v-for="item in stats.by_payment_method" :key="item.payment_method" size="small">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                      <strong>{{ getPaymentLabel(item.payment_method) }}</strong>
                      <div style="color: #999; font-size: 12px;">{{ item.count }} viajes</div>
                    </div>
                    <div style="font-size: 18px; font-weight: bold; color: #18a058;">
                      ${{ Number(item.total).toFixed(2) }}
                    </div>
                  </div>
                </n-card>
              </n-space>
              <!-- Desktop View -->
              <n-data-table
                v-else
                :columns="paymentColumns"
                :data="stats.by_payment_method"
                size="small"
              />
            </n-card>
          </n-grid-item>
          <n-grid-item>
            <n-card title="Por Conductor" size="small">
              <!-- Mobile View -->
              <n-space v-if="isMobile" vertical size="small">
                <n-card v-for="item in stats.by_driver" :key="item.driver?.id" size="small">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <div>
                      <strong>{{ item.driver?.name || 'N/A' }}</strong>
                      <div style="color: #999; font-size: 12px;">{{ item.count }} viajes</div>
                    </div>
                    <div style="font-size: 18px; font-weight: bold; color: #18a058;">
                      ${{ Number(item.total).toFixed(2) }}
                    </div>
                  </div>
                </n-card>
              </n-space>
              <!-- Desktop View -->
              <n-data-table
                v-else
                :columns="driverColumns"
                :data="stats.by_driver"
                size="small"
              />
            </n-card>
          </n-grid-item>
        </n-grid>
      </n-space>
    </n-card>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import { useMessage } from 'naive-ui';
import api from '../../api/axios';
import { format } from 'date-fns';
import { useBreakpoints, breakpointsTailwind } from '@vueuse/core';

const message = useMessage();
const breakpoints = useBreakpoints(breakpointsTailwind);
const isMobile = breakpoints.smaller('lg');

const loading = ref(false);
const downloadingPDF = ref(false);
const selectedDate = ref(format(new Date(), 'yyyy-MM-dd'));
const coordinatorName = ref('');
const shift = ref('Mañana');
const shiftOptions = [
  { label: 'Mañana', value: 'Mañana' },
  { label: 'Tarde', value: 'Tarde' },
  { label: 'Noche', value: 'Noche' }
];
const stats = ref({
  total_trips: 0,
  total_amount: 0,
  total_passengers: 0,
  by_payment_method: [],
  by_driver: [],
});

// Computed property to check if all required fields are filled
const canGeneratePDF = computed(() => {
  return !!(selectedDate.value && coordinatorName.value.trim() && shift.value);
});

function getPaymentLabel(method) {
  const map = { E: 'Efectivo', CH: 'Cargo Hab.', TJ: 'Tarjeta' };
  return map[method] || method;
}

const paymentColumns = [
  { 
    title: 'Método', 
    key: 'payment_method',
    render(row) {
      return getPaymentLabel(row.payment_method);
    }
  },
  { title: 'Viajes', key: 'count' },
  { 
    title: 'Total', 
    key: 'total',
    render(row) {
      return `$${Number(row.total).toFixed(2)}`;
    }
  },
];

const driverColumns = [
  { 
    title: 'Conductor', 
    key: 'driver.name',
    render(row) {
      return row.driver?.name || 'N/A';
    }
  },
  { title: 'Viajes', key: 'count' },
  { 
    title: 'Total', 
    key: 'total',
    render(row) {
      return `$${Number(row.total).toFixed(2)}`;
    }
  },
];

function handleDateChange(value) {
  if (value) {
    fetchReports();
  }
}

async function fetchReports() {
  loading.value = true;
  try {
    const params = {};
    if (selectedDate.value) {
      params.start_date = selectedDate.value;
      params.end_date = selectedDate.value;
    }
    const response = await api.get('/trips-report', { params });
    stats.value = response.data;
  } catch (error) {
    message.error('Error al cargar reportes');
  } finally {
    loading.value = false;
  }
}

async function downloadPDF() {
  downloadingPDF.value = true;
  try {
    const params = {
      date: selectedDate.value || format(new Date(), 'yyyy-MM-dd'),
      coordinator: coordinatorName.value,
      shift: shift.value
    };
    
    const response = await api.get('/trips-report/download', {
      params,
      responseType: 'blob'
    });
    
    // Create blob link to download
    const url = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', `reporte-viajes-${params.date}.pdf`);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);
    
    message.success('PDF generado exitosamente');
  } catch (error) {
    message.error('Error al generar PDF');
  } finally {
    downloadingPDF.value = false;
  }
}

onMounted(() => {
  fetchReports();
});
</script>
