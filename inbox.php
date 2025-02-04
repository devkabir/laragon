<script>
    document.addEventListener("DOMContentLoaded", () => {
        const mailListContainer = document.getElementById("mail-list");
        const mailTitle = document.getElementById("mail-title");
        const mailHeadersContainer = document.getElementById("mail-headers");
        const mailContentContainer = document.getElementById("mail-content");
        const clearButton = document.getElementById("clear");
        function getUTCDateFromFilename(filename) {
            // Extract the date and time parts using a regular expression
            const match = filename.match(/-(\d{8})-(\d{6})\.\d+/);
            if (!match) return null; // Return null if the format is incorrect

            const [, dateStr, timeStr] = match;

            // Construct a date string in ISO 8601 format
            const isoString = `${dateStr.slice(0, 4)}-${dateStr.slice(4, 6)}-${dateStr.slice(6, 8)}T${timeStr.slice(0, 2)}:${timeStr.slice(2, 4)}:${timeStr.slice(4, 6)}Z`;

            // Create a Date object
            const date = new Date(isoString);

            // Return the UTC string representation
            return date.toUTCString();
        }
        // Fetch the list of emails
        fetch("/api.php?action=mails")
            .then(response => response.json())
            .then(mailFiles => {
                mailFiles.forEach(file => {
                    console.log(file);

                    const mailItem = document.createElement("div");
                    mailItem.className = "p-3 bg-white hover:bg-indigo-100 cursor-pointer rounded-lg shadow-md border border-gray-200 transition ease-in-out duration-200 mb-3";
                    mailItem.textContent = getUTCDateFromFilename(file);
                    mailItem.addEventListener("click", () => fetchMailContent(file));
                    mailListContainer.appendChild(mailItem);
                });
            })
            .catch(error => {
                mailListContainer.innerHTML = `<p class="text-red-500">Error loading emails: ${error}</p>`;
            });

        // Fetch the content of a specific email
        function fetchMailContent(fileName) {
            fetch(`/api.php?action=mails&file=${encodeURIComponent(fileName)}`)
                .then(response => response.json())
                .then(data => {
                    mailTitle.textContent = getUTCDateFromFilename(data.filename);
                    clearButton.dataset.filename = data.filename;
                    mailHeadersContainer.innerHTML = Object.entries(data.headers)
                        .map(([key, value]) => `<p><strong>${key}:</strong> ${value}</p>`)
                        .join("");
                    if (data.filename.endsWith('.eml')) {
                        mailContentContainer.innerHTML = data.body; // Assuming data.body is HTML
                    } else {
                        mailContentContainer.innerHTML = '<pre>' + data.body + '</pre>';
                    }
                })
                .catch(error => {
                    mailContentContainer.innerHTML = `<p class="text-red-500">Error loading email content: ${error}</p>`;
                });
        }

        clearButton.addEventListener("click", () => {
            const filename = clearButton.dataset.filename;
            fetch("/api.php?action=mails&file=" + encodeURIComponent(filename) + "&clear=true")
                .then(response => response.json())
                .then(data => {
                    if (data.message) {
                        location.reload();
                    }
                })
                .catch(error => {
                    console.error("Error clearing logs:", error);
                });
        })
    });
</script>
<div class="flex h-screen">
    <!-- Sidebar: Email List -->
    <div class="w-64 bg-gray-50 border-r border-gray-200 overflow-y-auto">
        <h1 class="text-lg font-bold p-4 bg-indigo-200 text-indigo-900 border-b border-gray-300">Inbox</h1>
        <div id="mail-list" class="flex flex-col p-4">
            <!-- Email list items will be appended here -->
        </div>
    </div>

    <!-- Main Content: Email Viewer -->
    <div class="flex-1">
        <div class="flex px-4 justify-between items-center bg-indigo-200">
            <h1 id="mail-title" class="text-lg font-bold  py-4 text-indigo-900 border-b border-gray-300">Select an email
                to view</h1>
            <button type="button" id="clear"
                class="px-4 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600 transition ease-in-out duration-200">Delete</button>
        </div>
        <div id="mail-body" class="flex flex-col gap-4 p-4">
            <div id="mail-headers" class="bg-white p-6 shadow-md border border-gray-200 rounded-lg">
                <p class="text-gray-500">Select an email to view</p>
            </div>
            <div id="mail-content" class="p-6 text-gray-700">
                <!-- Email content will be displayed here -->
            </div>
        </div>
    </div>
</div>