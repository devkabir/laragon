<script>
     document.addEventListener("DOMContentLoaded", () => {
        const logContainer = document.getElementById("logs");

        fetch("/api.php?action=logs")
            .then(response => response.json())
            .then(logs => {
                logContainer.innerHTML = logs.error;
            })
     });
</script>
<div class="w-7/12">
    <h1>Server</h1>
    <pre id="logs"></pre>
</div>