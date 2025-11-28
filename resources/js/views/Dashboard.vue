<template>
  <div class="dashboard">
    <n-space vertical size="large">
      <n-card title="Resumen General">
        <n-grid cols="1 s:2 m:4" responsive="screen" :x-gap="12" :y-gap="12">
          <n-grid-item>
            <n-card size="small">
              <n-statistic label="Total Viajes" :value="stats.total_trips">
                <template #prefix>
                  <n-icon>
                    <car-icon />
                  </n-icon>
                </template>
              </n-statistic>
            </n-card>
          </n-grid-item>
          <n-grid-item>
            <n-card size="small">
              <n-statistic label="Monto Total" :value="stats.total_amount">
                <template #prefix>$</template>
              </n-statistic>
            </n-card>
          </n-grid-item>
          <n-grid-item>
            <n-card size="small">
              <n-statistic label="Pasajeros" :value="stats.total_passengers">
                <template #prefix>
                  <n-icon>
                    <people-icon />
                  </n-icon>
                </template>
              </n-statistic>
            </n-card>
          </n-grid-item>
        </n-grid>
      </n-card>

      <n-grid cols="1 m:2" responsive="screen" :x-gap="24" :y-gap="24">
        <n-grid-item>
          <n-card title="Viajes por Método de Pago">
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
              :bordered="false"
            />
          </n-card>
        </n-grid-item>
        <n-grid-item>
          <n-card title="Top Conductores">
            <!-- Mobile View -->
            <n-space v-if="isMobile" vertical size="small">
              <n-card v-for="item in stats.by_driver" :key="item.driver?.name" size="small">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <div>
                    <strong>{{ item.driver?.name || 'N/A' }}</strong>
                  </div>
                  <n-tag type="info">{{ item.count }} viajes</n-tag>
                </div>
              </n-card>
            </n-space>
            <!-- Desktop View -->
            <n-data-table
              v-else
              :columns="driverColumns"
              :data="stats.by_driver"
              size="small"
              :bordered="false"
            />
          </n-card>
        </n-grid-item>
      </n-grid>
    </n-space>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useMessage } from 'naive-ui';
import { 
  CarOutline as CarIcon,
  PeopleOutline as PeopleIcon 
} from '@vicons/ionicons5';
import api from '../api/axios';
import { useBreakpoints, breakpointsTailwind } from '@vueuse/core';

const message = useMessage();
const breakpoints = useBreakpoints(breakpointsTailwind);
const isMobile = breakpoints.smaller('md');

const stats = ref({
  total_trips: 0,
  total_amount: 0,
  total_passengers: 0,
  by_payment_method: [],
  by_driver: [],
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
      const map = { E: 'Efectivo', CH: 'Cargo Hab.', TJ: 'Tarjeta' };
      return map[row.payment_method] || row.payment_method;
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
  { title: 'Conductor', key: 'driver.name' },
  { title: 'Viajes', key: 'count' },
];

async function fetchStats() {
  try {
    const response = await api.get('/trips-report');
    stats.value = response.data;
  } catch (error) {
    console.error(error);
    message.error('Error al cargar estadísticas');
  }
}

onMounted(() => {
  fetchStats();
});
</script>

<style scoped>
.dashboard {
  padding: 0;
}
</style>
