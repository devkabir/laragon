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
        const clearButton = document.getElementById("clear");
        clearButton.addEventListener("click", () => {
            fetch("/api.php?action=logs&clear=true")
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        logContainer.innerHTML = "";
                        alert(data.message);
                    }
                })
                .catch(error => {
                    console.error("Error clearing logs:", error);
                });
        })    
     });
</script>
<div class="p-4 space-y-4">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Server Logs</h1>
        <button type="button" id="clear" class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600 transition ease-in-out duration-200">Clear</button>
    </div>
    <div id="logs">
    </div>
</div>