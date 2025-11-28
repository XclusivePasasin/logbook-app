<template>
  <div class="drivers-list">
    <n-card title="Conductores">
      <template #header-extra>
        <n-button type="primary" @click="handleCreate">
          Nuevo Conductor
        </n-button>
      </template>

      <n-space vertical size="large">
        <n-input
          v-model:value="search"
          placeholder="Buscar conductor"
          clearable
          @input="fetchDrivers"
          style="width: 100%"
        />

        <!-- Mobile View -->
        <n-grid v-if="isMobile" cols="1" :y-gap="12">
          <n-grid-item v-for="driver in drivers" :key="driver.id">
            <n-card size="small">
              <template #header>
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <strong>{{ driver.name }}</strong>
                  <n-tag :type="driver.is_active ? 'success' : 'error'">
                    {{ driver.is_active ? 'Activo' : 'Inactivo' }}
                  </n-tag>
                </div>
              </template>
              <n-space vertical size="small">
                <div style="display: flex; justify-content: space-between;">
                  <span style="color: #999;">Licencia:</span>
                  <span>{{ driver.license_number || 'N/A' }}</span>
                </div>
                <div style="display: flex; justify-content: space-between;">
                  <span style="color: #999;">Teléfono:</span>
                  <span>{{ driver.phone || 'N/A' }}</span>
                </div>
              </n-space>
              <template #action>
                <n-space justify="end">
                  <n-button size="small" @click="handleEdit(driver)">
                    Editar
                  </n-button>
                  <n-button size="small" type="error" @click="handleDelete(driver)">
                    Eliminar
                  </n-button>
                </n-space>
              </template>
            </n-card>
          </n-grid-item>
        </n-grid>

        <!-- Desktop View -->
        <n-data-table
          v-else
          :columns="columns"
          :data="drivers"
          :loading="loading"
        />
      </n-space>
    </n-card>

    <n-modal v-model:show="showModal" preset="card" :title="isEdit ? 'Editar Conductor' : 'Nuevo Conductor'" style="width: 600px">
      <n-form
        ref="formRef"
        :model="formValue"
        :rules="rules"
        :label-placement="isMobile ? 'top' : 'left'"
        label-width="120"
      >
        <n-form-item label="Nombre" path="name">
          <n-input v-model:value="formValue.name" placeholder="Nombre completo" />
        </n-form-item>
        <n-form-item label="Licencia" path="license_number">
          <n-input v-model:value="formValue.license_number" placeholder="Número de licencia" />
        </n-form-item>
        <n-form-item label="Teléfono" path="phone">
          <n-input v-model:value="formValue.phone" placeholder="Teléfono" />
        </n-form-item>
        <n-form-item label="Estado" path="is_active">
          <n-switch v-model:value="formValue.is_active">
            <template #checked>Activo</template>
            <template #unchecked>Inactivo</template>
          </n-switch>
        </n-form-item>
      </n-form>
      <template #footer>
        <n-space justify="end">
          <n-button @click="showModal = false">Cancelar</n-button>
          <n-button type="primary" :loading="submitting" @click="handleSubmit">
            Guardar
          </n-button>
        </n-space>
      </template>
    </n-modal>
  </div>
</template>

<script setup>
import { ref, onMounted, h } from 'vue';
import { NButton, NSpace, NTag, useMessage, useDialog, NSwitch, NGrid, NGridItem, NCard } from 'naive-ui';
import api from '../../api/axios';
import { useBreakpoints, breakpointsTailwind } from '@vueuse/core';

const message = useMessage();
const dialog = useDialog();
const breakpoints = useBreakpoints(breakpointsTailwind);
const isMobile = breakpoints.smaller('md');

const drivers = ref([]);
const loading = ref(false);
const search = ref('');
const showModal = ref(false);
const submitting = ref(false);
const formRef = ref(null);
const isEdit = ref(false);
const currentId = ref(null);

const formValue = ref({
  name: '',
  license_number: '',
  phone: '',
  is_active: true,
});

const rules = {
  name: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
};

const columns = [
  { title: 'Nombre', key: 'name' },
  { title: 'Licencia', key: 'license_number' },
  { title: 'Teléfono', key: 'phone' },
  {
    title: 'Estado',
    key: 'is_active',
    render(row) {
      return h(
        NTag,
        { type: row.is_active ? 'success' : 'error' },
        { default: () => (row.is_active ? 'Activo' : 'Inactivo') }
      );
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
              onClick: () => handleEdit(row)
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

async function fetchDrivers() {
  loading.value = true;
  try {
    const params = { search: search.value };
    const response = await api.get('/drivers', { params });
    drivers.value = response.data;
  } catch (error) {
    message.error('Error al cargar conductores');
  } finally {
    loading.value = false;
  }
}

function handleCreate() {
  isEdit.value = false;
  currentId.value = null;
  formValue.value = {
    name: '',
    license_number: '',
    phone: '',
    is_active: true,
  };
  showModal.value = true;
}

function handleEdit(row) {
  isEdit.value = true;
  currentId.value = row.id;
  formValue.value = {
    name: row.name,
    license_number: row.license_number,
    phone: row.phone,
    is_active: !!row.is_active,
  };
  showModal.value = true;
}

async function handleSubmit() {
  formRef.value?.validate(async (errors) => {
    if (!errors) {
      submitting.value = true;
      try {
        if (isEdit.value) {
          await api.put(`/drivers/${currentId.value}`, formValue.value);
          message.success('Conductor actualizado');
        } else {
          await api.post('/drivers', formValue.value);
          message.success('Conductor creado');
        }
        showModal.value = false;
        fetchDrivers();
      } catch (error) {
        message.error('Error al guardar');
      } finally {
        submitting.value = false;
      }
    }
  });
}

function handleDelete(row) {
  dialog.warning({
    title: 'Confirmar eliminación',
    content: '¿Estás seguro de que deseas eliminar este conductor?',
    positiveText: 'Eliminar',
    negativeText: 'Cancelar',
    onPositiveClick: async () => {
      try {
        await api.delete(`/drivers/${row.id}`);
        message.success('Conductor eliminado');
        fetchDrivers();
      } catch (error) {
        message.error('Error al eliminar conductor');
      }
    }
  });
}

onMounted(() => {
  fetchDrivers();
});
</script>
