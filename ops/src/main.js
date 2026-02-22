import { createApp } from 'vue'
import './style.css'
import App from './App.vue'
import router from './router'
import Swal from 'sweetalert2'

import axios from 'axios'

window.Swal = Swal; // Make available globally like in legacy

// Set Axios Base URL based on Vite config
axios.defaults.baseURL = import.meta.env.BASE_URL;

const app = createApp(App)
app.use(router)
app.mount('#app')
