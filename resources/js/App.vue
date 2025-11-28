<template>
  <n-config-provider :theme="theme" :theme-overrides="themeOverrides">
    <n-message-provider>
      <n-dialog-provider>
        <router-view />
      </n-dialog-provider>
    </n-message-provider>
  </n-config-provider>
</template>

<script setup>
import { computed } from 'vue';
import { darkTheme } from 'naive-ui';
import { useThemeStore } from './stores/theme';

const themeStore = useThemeStore();

const theme = computed(() => {
  return themeStore.isDark ? darkTheme : null;
});

// Custom theme overrides for a softer, more pleasant appearance
const themeOverrides = computed(() => {
  if (themeStore.isDark) {
    // Dark mode overrides
    return {
      common: {
        bodyColor: '#1a1a1a',
        cardColor: '#242424',
      }
    };
  } else {
    // Light mode overrides - softer, warmer whites
    return {
      common: {
        bodyColor: '#f5f5f0', // Soft warm off-white
        cardColor: '#fafaf8', // Very light warm white for cards
        tableColor: '#fafaf8',
        modalColor: '#fafaf8',
        popoverColor: '#fafaf8',
        baseColor: '#fafaf8',
      }
    };
  }
});
</script>
