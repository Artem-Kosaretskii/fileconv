import { createRouter, createWebHistory } from 'vue-router';
import Index from './components/Index.vue';
import FileUpload from './components/FileUpload.vue';
import Users from './components/Users.vue';

const routes = [
  {
    path: '/',
    component: Index
  },
  {
    path: '/upload',
    component: FileUpload
  },
  {
    path: '/users',
    component: Users
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
});

export default router;