<script setup>
import { computed } from 'vue'

const props = defineProps({
  isOpen: Boolean
})

const emit = defineEmits(['close'])

const sidebarClass = computed(() => {
  return props.isOpen ? 'translate-x-0' : '-translate-x-full'
})
</script>

<template>
  <div>
    <!-- Backdrop for Mobile -->
    <div 
        class="backdrop" 
        :class="{ 'visible': isOpen }" 
        @click="$emit('close')"
    ></div>

    <!-- Sidebar -->
    <aside class="sidebar" :class="sidebarClass">
      <div class="sidebar-header">
        <div class="logo">
           <img src="/src/assets/logo.webp" alt="Logo" class="logo-img" onerror="this.style.display='none'"> 
           <span>Sutan Raya</span>
        </div>
        <button class="close-btn md:hidden" @click="$emit('close')">
          <i class="bi bi-x-lg"></i>
        </button>
      </div>

      <nav class="sidebar-nav">
        <a href="#" class="nav-item active">
          <i class="bi bi-grid-3x3-gap-fill"></i>
          <span>Lihat Booking</span>
        </a>
      </nav>
      
      <div class="sidebar-footer">
          <span class="app-name">Travel</span>
      </div>
    </aside>
  </div>
</template>

<style scoped>
/* Backdrop */
.backdrop {
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.5);
  z-index: 40;
  opacity: 0;
  pointer-events: none;
  transition: opacity 0.3s;
}

.backdrop.visible {
  opacity: 1;
  pointer-events: auto;
}

/* Sidebar */
.sidebar {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  width: 280px;
  background: var(--card-bg); /* Use variable */
  z-index: 50;
  box-shadow: 4px 0 10px rgba(0,0,0,0.05);
  transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  display: flex;
  flex-direction: column;
}

/* Mobile: Hidden by default (handled by generic class logic or media query) */
/* Actually, the transform logic handles it. By default generic styles apply. */

@media (min-width: 768px) {
  .sidebar {
    transform: translateX(0) !important; /* Always visible on desktop */
    position: fixed;
    z-index: 10;
    box-shadow: 1px 0 0 var(--border-color);
  }
  
  .backdrop {
      display: none; /* No backdrop on desktop */
  }
}

.sidebar-header {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1.5rem;
  border-bottom: 1px solid var(--border-color);
}

.logo {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  font-weight: 800;
  font-size: 1.125rem;
  color: var(--color-primary);
}

.logo-img {
    height: 32px;
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.25rem;
  color: #64748b;
  cursor: pointer;
}

.sidebar-nav {
  flex: 1;
  padding: 1.5rem 1rem;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.nav-item {
  display: flex;
  align-items: center;
  gap: 0.75rem;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  text-decoration: none;
  color: #64748b;
  font-weight: 500;
  transition: all 0.2s;
}

.nav-item:hover {
  background: #f1f5f9;
  color: var(--color-primary);
}

.nav-item.active {
  background: #eff6ff;
  color: #3b82f6;
}

.sidebar-footer {
    padding: 1.5rem;
    border-top: 1px solid var(--border-color);
    text-align: center;
}

.app-name {
    font-size: 0.875rem;
    font-weight: 600;
    color: var(--color-primary);
}
</style>
