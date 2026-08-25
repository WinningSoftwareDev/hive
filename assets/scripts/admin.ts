import '../styles/app.css';
import { createApp } from 'vue';
import { createRouter, createWebHistory, Router } from 'vue-router';
import AdminPanel from './Administration/AdminPanel.vue';
import AdminDashboard from './Administration/Page/AdminDashboard.vue';
import SettingsSettingsIndex from './Administration/Page/Settings/SettingsIndex.vue';
import UserUserIndex from './Administration/Page/User/UserIndex.vue';

const routes = [
    {path: '/admin', component: AdminDashboard, meta: {title: 'Dashboard'}},
    {path: '/admin/users', component: UserUserIndex, meta: {title: 'Users'}},
    {path: '/admin/settings', component: SettingsSettingsIndex, meta: {title: 'Settings'}},
];

const router: Router = createRouter({history: createWebHistory(), routes});
const app = createApp(AdminPanel);

app.use(router);
app.mount('#admin-root');