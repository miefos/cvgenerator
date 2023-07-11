import {createRouter, createWebHashHistory} from 'vue-router';

// import your components
import Home from './components/CV/Pages/Home.vue';
import EditCV from './components/CV/Pages/EditCV.vue';

// import the store
import store from './store';
import axios from "axios";

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
      props: (route) => ({ sectionId: route.query.section })
    },
    // add catch-all route to redirect to home page if page not found
    {
      path: '/:catchAll(.*)',
      redirect: '/'
    }
  ]
});

// Add beforeEach navigation guard
router.beforeEach((to, from, next) => {
  // check auth
  if (to.path !== '/') {
    // Make a GET request to the wp/v2/users/me endpoint
    axios.get(store.getters.getGeneralData?.data?.api['check_auth'])
      .then(response => {
        const authBoolean = response.data
        if (!authBoolean) {
          location.reload();
        }
      })
  }

  if (to.path === '/edit-cv') {
    if (!to.query.section) { // set default section if not set
      const firstSectionId = store.getters.getGeneralData?.data?.sections[0]?.id
      const query = { ...to.query, section: firstSectionId }
      router.push({ path: to.path, query })
    } else {
      const sectionId = to.query.section;
      const idx = store.getters.getGeneralData?.data?.sections.findIndex(section => section.id === sectionId)
      if (idx === -1) {
        const firstSectionId = store.getters.getGeneralData?.data?.sections[0]?.id
        const query = { ...to.query, section: firstSectionId }
        router.push({ path: to.path, query })
      }
    }
  }
  store.dispatch('removeResponse');
  next();
});


export default router;