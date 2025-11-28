<template>
  <div class="users-list">
    <n-card title="Usuarios">
      <template #header-extra>
        <n-button type="primary" @click="handleCreate">
          Nuevo Usuario
        </n-button>
      </template>

      <!-- Mobile View -->
      <n-grid v-if="isMobile" cols="1" :y-gap="12">
        <n-grid-item v-for="user in users" :key="user.id">
          <n-card size="small">
            <template #header>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <strong>{{ user.name }}</strong>
                <n-tag :type="user.role === 'admin' ? 'warning' : 'default'">
                  {{ user.role.toUpperCase() }}
                </n-tag>
              </div>
            </template>
            <n-space vertical size="small">
              <div style="display: flex; justify-content: space-between;">
                <span style="color: #999;">Email:</span>
                <span>{{ user.email }}</span>
              </div>
              <div style="display: flex; justify-content: space-between;">
                <span style="color: #999;">Estado:</span>
                <n-tag :type="user.is_active ? 'success' : 'error'">
                  {{ user.is_active ? 'Activo' : 'Inactivo' }}
                </n-tag>
              </div>
            </n-space>
            <template #action>
              <n-space justify="end">
                <n-button size="small" @click="handleEdit(user)">
                  Editar
                </n-button>
                <n-button size="small" type="error" @click="handleDelete(user)">
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
        :data="users"
        :loading="loading"
      />
    </n-card>

    <n-modal v-model:show="showModal" preset="card" :title="isEdit ? 'Editar Usuario' : 'Nuevo Usuario'" style="width: 600px">
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
        <n-form-item label="Email" path="email">
          <n-input v-model:value="formValue.email" placeholder="Correo electrónico" />
        </n-form-item>
        <n-form-item label="Contraseña" path="password">
          <n-input
            v-model:value="formValue.password"
            type="password"
            show-password-on="click"
            :placeholder="isEdit ? 'Dejar en blanco para mantener' : 'Contraseña'"
          />
        </n-form-item>
        <n-form-item label="Rol" path="role">
          <n-select
            v-model:value="formValue.role"
            :options="[{ label: 'Admin', value: 'admin' }, { label: 'Usuario', value: 'user' }]"
          />
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

const users = ref([]);
const loading = ref(false);
const showModal = ref(false);
const submitting = ref(false);
const formRef = ref(null);
const isEdit = ref(false);
const currentId = ref(null);

const formValue = ref({
  name: '',
  email: '',
  password: '',
  role: 'user',
  is_active: true,
});

const rules = {
  name: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
  email: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
  role: { required: true, message: 'Requerido', trigger: ['blur', 'change'] },
};

const columns = [
  { title: 'Nombre', key: 'name' },
  { title: 'Email', key: 'email' },
  {
    title: 'Rol',
    key: 'role',
    render(row) {
      return h(
        NTag,
        { type: row.role === 'admin' ? 'warning' : 'default' },
        { default: () => row.role.toUpperCase() }
      );
    }
  },
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

async function fetchUsers() {
  loading.value = true;
  try {
    const response = await api.get('/users');
    users.value = response.data;
  } catch (error) {
    message.error('Error al cargar usuarios');
  } finally {
    loading.value = false;
  }
}

function handleCreate() {
  isEdit.value = false;
  currentId.value = null;
  formValue.value = {
    name: '',
    email: '',
    password: '',
    role: 'user',
    is_active: true,
  };
  showModal.value = true;
}

function handleEdit(row) {
  isEdit.value = true;
  currentId.value = row.id;
  formValue.value = {
    name: row.name,
    email: row.email,
    password: '', // Don't fill password
    role: row.role,
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
          const data = { ...formValue.value };
          if (!data.password) delete data.password;
          await api.put(`/users/${currentId.value}`, data);
          message.success('Usuario actualizado');
        } else {
          await api.post('/users', formValue.value);
          message.success('Usuario creado');
        }
        showModal.value = false;
        fetchUsers();
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
    content: '¿Estás seguro de que deseas eliminar este usuario?',
    positiveText: 'Eliminar',
    negativeText: 'Cancelar',
    onPositiveClick: async () => {
      try {
        await api.delete(`/users/${row.id}`);
        message.success('Usuario eliminado');
        fetchUsers();
      } catch (error) {
        message.error('Error al eliminar usuario');
      }
    }
  });
}

onMounted(() => {
  fetchUsers();
});
</script>
