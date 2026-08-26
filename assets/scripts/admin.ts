import '../styles/app.css';
import { createApp } from 'vue';
import { createRouter, createWebHistory, Router } from 'vue-router';
import AdminPanel from './Administration/AdminPanel.vue';
import AdminDashboard from './Administration/Page/AdminDashboard.vue';

const routes = [
  { path: '/admin', component: AdminDashboard, meta: { title: 'Dashboard' } },
];

const router: Router = createRouter({ history: createWebHistory(), routes });
const app = createApp(AdminPanel);

app.use(router);
app.mount('#admin-root');
