import {createRouter, createWebHashHistory} from 'vue-router';

// import your components
import Home from './components/CV/Pages/Home.vue';
import EditCV from './components/CV/Pages/EditCV.vue';
import DownloadCV from './components/CV/Pages/DownloadCV.vue';

const router = createRouter({
  history: createWebHashHistory("/cv-generator"),
  base: '/cv-generator',
  routes: [
    {
      path: '/',
      component: Home
    },
    {
      path: '/edit-cv',
      component: EditCV,
    },
    {
      path: '/download-cv',
      component: DownloadCV
    }
  ]
});

export default router;