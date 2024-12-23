<script>
     document.addEventListener("DOMContentLoaded", () => {
        const logContainer = document.getElementById("logs");

        fetch("/api.php?action=logs")
            .then(response => response.json())
            .then(logs => {
                const logsArray = logs.error.split("\r\n").filter(log => log !== "");
                logsArray.forEach(log => {
                    const logElement = document.createElement("p");
                    logElement.classList.add("bg-gray-100", "p-4", "mb-2", "rounded-lg");
                    logElement.innerText = log;
                    logContainer.appendChild(logElement);
                });
            })
     });
</script>
<div class="p-4 space-y-4">
    <h1 class="text-3xl font-bold">Server Logs</h1>
    <div id="logs">
    </div>
</div>