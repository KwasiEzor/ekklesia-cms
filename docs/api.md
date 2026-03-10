---
layout: false
pageClass: api-page
---

<div id="redoc-container"></div>
<script src="https://cdn.redoc.ly/redoc/latest/bundles/redoc.standalone.js"></script>
<script>
  Redoc.init('/api.json', {
    scrollYOffset: 50,
    theme: {
      colors: {
        primary: { main: '#1A3A5C' }
      },
      typography: {
        fontSize: '15px',
        fontFamily: '-apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Fira Sans", "Droid Sans", "Helvetica Neue", sans-serif',
      }
    }
  }, document.getElementById('redoc-container'));
</script>

<style>
  /* Ensure the container takes up the full width/height of the page */
  body { margin: 0; padding: 0; }
  .api-page { max-width: 100% !important; padding: 0 !important; }
  #redoc-container { height: 100vh; }
  
  /* Hide standard VitePress Nav on this specific raw page for a cleaner API look */
  .VPNav { display: none !important; }
</style>
