import { createRouter, createWebHistory } from 'vue-router';
import UploadPage from '../views/UploadPage.vue';
import TrackQueryPage from '../views/TrackQueryPage.vue';

const routes = [
    {
        path: '/',
        redirect: '/upload'
    },
    {
        path: '/upload',
        name: 'Upload',
        component: UploadPage
    },
    {
        path: '/query',
        name: 'Query',
        component: TrackQueryPage
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;
