# Vue.js Setup in Laravel

This document provides instructions for working with Vue.js in the Laravel application.

## Overview

The Laravel application has been configured to use Vue.js for frontend development. The setup uses:

- Vue.js 3.4.0
- Vite as the build tool
- Laravel's Blade templates for the initial HTML structure

## Directory Structure

Vue.js components should be placed in the following directory:

```
/resources/js/components/
```

An example component has been created at:

```
/resources/js/components/ExampleComponent.vue
```

## Creating Vue Components

Vue components are created as `.vue` files with the following structure:

```vue
<template>
  <!-- Your HTML template goes here -->
</template>

<script>
export default {
  // Component options go here
  data() {
    return {
      // Component data goes here
    }
  },
  methods: {
    // Component methods go here
  }
}
</script>

<style scoped>
/* Component styles go here */
</style>
```

## Registering Components

To use a component in your application, you need to register it in the `resources/js/app.js` file:

```javascript
// Import your Vue components
import YourComponent from './components/YourComponent.vue';

// Register components
app.component('your-component', YourComponent);
```

## Using Components in Blade Templates

Once a component is registered, you can use it in your Blade templates:

```html
<div id="app">
  <your-component></your-component>
</div>
```

Make sure the component is used within an element with the ID `app`, as this is where Vue.js mounts the application.

## Development Workflow

1. Create or modify Vue components in the `/resources/js/components/` directory
2. Register the components in `resources/js/app.js`
3. Use the components in your Blade templates
4. Run the development server to see your changes

### Running the Development Server

To start the Vite development server:

```bash
docker-compose exec app npm run dev
```

This will start the Vite development server with hot module replacement, so your changes will be reflected in the browser without refreshing.

### Building for Production

To build the assets for production:

```bash
docker-compose exec app npm run build
```

This will compile and minify your JavaScript and CSS files for production.

## Accessing the Application

The Vue.js application is accessible at:

```
http://localhost:8000
```

The original Laravel welcome page is still accessible at:

```
http://localhost:8000/welcome
```

## Additional Resources

- [Vue.js Documentation](https://vuejs.org/guide/introduction.html)
- [Vite Documentation](https://vitejs.dev/guide/)
- [Laravel Documentation](https://laravel.com/docs)
