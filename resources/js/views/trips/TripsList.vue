<template>
  <div class="trips-list">
    <n-card title="Bitácora de Viajes">
      <template #header-extra>
        <n-button type="primary" @click="router.push({ name: 'trips.create' })">
          Nuevo Viaje
        </n-button>
      </template>

      <n-space vertical size="large">
        <n-form :inline="!isMobile" :model="filters" label-placement="left">
          <n-form-item label="Filtrar por">
            <n-select
              v-model:value="filterType"
              :options="filterTypeOptions"
              @update:value="handleFilterTypeChange"
              style="width: 150px"
            />
          </n-form-item>
          <n-form-item :label="filterType === 'day' ? 'Día' : 'Mes'">
            <n-date-picker
              v-model:formatted-value="filterDate"
              :type="filterType === 'day' ? 'date' : 'month'"
              :value-format="filterType === 'day' ? 'yyyy-MM-dd' : 'yyyy-MM'"
              clearable
              @update:formatted-value="handleDateChange"
              style="width: 100%"
            />
          </n-form-item>
          <n-form-item label="Buscar">
            <n-input
              v-model:value="filters.search"
              placeholder="Origen o Destino"
              clearable
              @input="fetchTrips"
              style="width: 100%"
            />
          </n-form-item>
        </n-form>

        <div v-if="isMobile">
          <n-grid cols="1" :y-gap="12">
            <n-grid-item v-for="trip in trips" :key="trip.id">
              <n-card size="small">
                <template #header>
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span>{{ format(new Date(trip.trip_date), 'dd/MM/yyyy') }}</span>
                    <n-tag :type="getPaymentType(trip.payment_method)">
                      {{ getPaymentLabel(trip.payment_method) }}
                    </n-tag>
                  </div>
                </template>
                <n-space vertical size="small">
                  <div style="display: flex; justify-content: space-between;">
                    <strong>Hora:</strong>
                    <span>{{ trip.trip_time }}</span>
                  </div>
                  <div style="display: flex; justify-content: space-between;">
                    <strong>Conductor:</strong>
                    <span>{{ trip.driver.name }}</span>
                  </div>
                  <div style="display: flex; justify-content: space-between;">
                    <strong>Ruta:</strong>
                    <span>{{ trip.origin }} -> {{ trip.destination }}</span>
                  </div>
                  <div style="display: flex; justify-content: space-between;">
                    <strong>Monto:</strong>
                    <span style="font-size: 16px; font-weight: bold; color: #18a058;">${{ trip.amount }}</span>
                  </div>
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong>Pasajeros:</strong>
                    <n-tag type="info" size="small">{{ trip.passengers }}</n-tag>
                  </div>
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                    <strong>Tipo:</strong>
                    <n-space size="small">
                      <n-tag v-if="trip.is_ida" type="success" size="small">IDA</n-tag>
                      <n-tag v-if="trip.is_vuelta" type="warning" size="small">VUELTA</n-tag>
                      <span v-if="!trip.is_ida && !trip.is_vuelta" style="color: #999;">-</span>
                    </n-space>
                  </div>
                </n-space>
                <template #action>
                  <n-space justify="end">
                    <n-button size="small" @click="router.push({ name: 'trips.edit', params: { id: trip.id } })">
                      Editar
                    </n-button>
                    <n-button size="small" type="error" @click="handleDelete(trip)">
                      Eliminar
                    </n-button>
                  </n-space>
                </template>
              </n-card>
            </n-grid-item>
          </n-grid>
          <n-pagination
            v-if="pagination.itemCount > 0"
            style="margin-top: 12px; justify-content: center;"
            :item-count="pagination.itemCount"
            :page="pagination.page"
            :page-size="pagination.pageSize"
            @update:page="handlePageChange"
          />
        </div>

        <n-data-table
          v-else
          :columns="columns"
          :data="trips"
          :loading="loading"
          :pagination="pagination"
          @update:page="handlePageChange"
        />
      </n-space>
    </n-card>
  </div>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import { useRouter } from 'vue-router';
import { NButton, NSpace, NTag, useMessage, useDialog, NGrid, NGridItem, NCard, NPagination } from 'naive-ui';
import api from '../../api/axios';
import { format } from 'date-fns';
import { useBreakpoints, breakpointsTailwind } from '@vueuse/core';

const router = useRouter();
const message = useMessage();
const dialog = useDialog();
const breakpoints = useBreakpoints(breakpointsTailwind);

const isMobile = breakpoints.smaller('md');

function getPaymentType(method) {
  return 'info';
}

function getPaymentLabel(method) {
  const map = { E: 'Efectivo', CH: 'Cargo Hab.', TJ: 'Tarjeta' };
  return map[method] || method;
}

const trips = ref([]);
const loading = ref(false);
const filterType = ref('day');
const filterDate = ref(null);
const filters = ref({
  search: '',
  date: null,
  month: null,
});

const filterTypeOptions = [
  { label: 'Por Día', value: 'day' },
  { label: 'Por Mes', value: 'month' },
];

const pagination = ref({
  page: 1,
  pageSize: 15,
  itemCount: 0,
});

const columns = [
  {
    title: 'Fecha',
    key: 'trip_date',
    render(row) {
      return format(new Date(row.trip_date), 'dd/MM/yyyy');
    }
  },
  {
    title: 'Hora',
    key: 'trip_time',
  },
  {
    title: 'Conductor',
    key: 'driver.name',
  },
  {
    title: 'Origen',
    key: 'origin',
  },
  {
    title: 'Destino',
    key: 'destination',
  },
  {
    title: 'Pasajeros',
    key: 'passengers',
  },
  {
    title: 'Pago',
    key: 'payment_method',
    render(row) {
      const map = { E: 'Efectivo', CH: 'Cargo Hab.', TJ: 'Tarjeta' };
      return h(NTag, { type: 'info' }, { default: () => map[row.payment_method] });
    }
  },
  {
    title: 'Monto',
    key: 'amount',
    render(row) {
      return `$${row.amount}`;
    }
  },
  {
    title: 'Acciones',
    key: 'actions',
    render(row) {
      return h(NSpace, null, {
        default: () => [
          h(
            NButton,
            {
              size: 'small',
              onClick: () => router.push({ name: 'trips.edit', params: { id: row.id } })
            },
            { default: () => 'Editar' }
          ),
          h(
            NButton,
            {
              size: 'small',
              type: 'error',
              onClick: () => handleDelete(row)
            },
            { default: () => 'Eliminar' }
          )
        ]
      });
    }
  }
];

function handleFilterTypeChange() {
  filterDate.value = null;
  filters.value.date = null;
  filters.value.month = null;
  fetchTrips();
}

function handleDateChange(value) {
  if (filterType.value === 'day') {
    filters.value.date = value;
    filters.value.month = null;
  } else {
    filters.value.month = value;
    filters.value.date = null;
  }
  fetchTrips();
}

async function fetchTrips() {
  loading.value = true;
  try {
    const params = {
      page: pagination.value.page,
      per_page: pagination.value.pageSize,
      search: filters.value.search,
    };
    
    // Add date or month filter
    if (filters.value.date) {
      params.date = filters.value.date;
    } else if (filters.value.month) {
      params.month = filters.value.month;
    }
    
    const response = await api.get('/trips', { params });
    trips.value = response.data.data;
    pagination.value.itemCount = response.data.total;
    pagination.value.page = response.data.current_page;
  } catch (error) {
    message.error('Error al cargar viajes');
  } finally {
    loading.value = false;
  }
}

function handlePageChange(page) {
  pagination.value.page = page;
  fetchTrips();
}

function handleDelete(row) {
  dialog.warning({
    title: 'Confirmar eliminación',
    content: '¿Estás seguro de que deseas eliminar este viaje?',
    positiveText: 'Eliminar',
    negativeText: 'Cancelar',
    onPositiveClick: async () => {
      try {
        await api.delete(`/trips/${row.id}`);
        message.success('Viaje eliminado');
        fetchTrips();
      } catch (error) {
        message.error('Error al eliminar viaje');
      }
    }
  });
}

onMounted(() => {
  fetchTrips();
});
</script>
