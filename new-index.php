<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Inbox</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const mailListContainer = document.getElementById("mail-list");
            const mailTitle = document.getElementById("mail-title");
            const mailHeadersContainer = document.getElementById("mail-headers");
            const mailContentContainer = document.getElementById("mail-content");

            // Fetch the list of emails
            fetch("/api.php?action=mails")
                .then(response => response.json())
                .then(mailFiles => {
                    mailFiles.forEach(file => {
                        const mailItem = document.createElement("div");
                        mailItem.className = "p-4 hover:bg-gray-100 cursor-pointer border-b border-gray-200";
                        mailItem.textContent = file;
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
                        mailTitle.textContent = data.filename;
                        mailHeadersContainer.innerHTML = Object.entries(data.headers)
                            .map(([key, value]) => `<p><strong>${key}:</strong> ${value}</p>`)
                            .join("");
                        mailContentContainer.innerHTML = data.body; // Assuming data.body is HTML
                    })
                    .catch(error => {
                        mailContentContainer.innerHTML = `<p class="text-red-500">Error loading email content: ${error}</p>`;
                    });
            }
        });
    </script>
</head>

<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar: Email List -->
        <div class="w-1/3 bg-white border-r border-gray-200 overflow-y-auto">
            <h1 class="text-lg font-bold p-4 bg-gray-200 border-b border-gray-300">Inbox</h1>
            <div id="mail-list" class="flex flex-col">
                <!-- Email list items will be appended here -->
            </div>
        </div>

        <!-- Main Content: Email Viewer -->
        <div class="w-2/3 flex flex-col">
            <div class="bg-gray-200 p-4 border-b border-gray-300">
                <h2 id="mail-title" class="text-xl font-semibold">Select an email to view</h2>
            </div>
            <div id="mail-body" class="flex flex-col gap-4 p-4">
                <div id="mail-headers" class="bg-white p-4 border border-gray-200 rounded">
                    <p>Select an email to view</p>
                </div>
                <div id="mail-content" class="p-4">
                    <!-- Email content will be displayed here -->
                </div>
            </div>
        </div>
    </div>
</body>

</html>
