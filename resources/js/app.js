import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router';
import App from './components/App.vue';
import { permissionDirective, roleDirective } from './directives/permission';

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);
app.use(router);

// Register directives
app.directive('can', permissionDirective);
app.directive('role', roleDirective);

app.mount('#app');