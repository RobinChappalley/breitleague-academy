import './bootstrap';
import { createApp } from 'vue';

// Import your Vue components here
import ExampleComponent from './components/ExampleComponent.vue';

// Create a new Vue application
const app = createApp({
    // Your root component options go here
});

// Register components
app.component('example-component', ExampleComponent);

// Mount the application
app.mount('#app');
