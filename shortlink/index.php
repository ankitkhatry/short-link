<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Link Shortener</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #4a90e2; color: #333; min-height: 100vh; display: flex; justify-content: center; align-items: center; overflow: hidden; }
        .container { background: rgba(255, 255, 255, 0.8); border-radius: 16px; padding: 20px; max-width: 400px; width: 90%; text-align: center; box-shadow: 0 8px 32px rgba(0, 0, 0, 0.15); backdrop-filter: blur(8px); animation: fadeIn 1.2s ease-in-out; }
        .container h1 { font-weight: 600; margin-bottom: 16px; font-size: 2rem; color: #4a90e2; }
        form { display: flex; flex-direction: column; gap: 12px; }
        input[type="url"] { padding: 12px; border-radius: 8px; border: 1px solid #ddd; outline: none; font-size: 1rem; width: 100%; transition: border 0.3s; }
        input[type="url"]:focus { border-color: #4a90e2; }
        button { background: #4a90e2; color: #fff; border: none; padding: 12px; border-radius: 8px; font-size: 1rem; cursor: pointer; transition: transform 0.2s ease, box-shadow 0.2s ease; }
        button:hover { transform: translateY(-3px); box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4); }
        #result { margin-top: 16px; background: rgba(0, 0, 0, 0.05); border-radius: 8px; padding: 12px; font-size: 1rem; overflow-wrap: break-word; display: none; text-align: center; }
        #result a { color: #4a90e2; text-decoration: none; }
        footer { margin-top: 16px; font-size: 0.85rem; color: rgba(0, 0, 0, 0.6); }
        footer a { color: #4a90e2; text-decoration: none; transition: color 0.3s ease; }
        footer a:hover { color: #3e78c1; }
        @keyframes fadeIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        @media (max-width: 600px) { .container { padding: 16px; } .container h1 { font-size: 1.5rem; } input[type="url"], button { font-size: 0.9rem; padding: 10px; } }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔗 Link Shortener</h1>
        <form id="shortenForm">
            <input type="url" id="originalUrl" placeholder="Paste your URL here" required>
            <button type="submit">Shorten URL</button>
        </form>
        <div id="result">
            <span id="shortenedUrl"></span>
            <p>Expires at: <span id="expirationTime"></span></p>
            <button id="copyButton" style="margin-top: 10px;">Copy</button>
        </div>
        <footer>
            Made with ❤️ by <a href="https://www.ankitak.com.np/">Ankit Dai</a>
        </footer>
    </div>

    <script>
        const form = document.getElementById('shortenForm');
        const resultDiv = document.getElementById('result');
        const shortenedUrlSpan = document.getElementById('shortenedUrl');
        const expirationTimeSpan = document.getElementById('expirationTime');
        const copyButton = document.getElementById('copyButton');

        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const url = document.getElementById('originalUrl').value;

            Swal.fire({
                title: 'Processing...',
                text: 'Shortening your URL...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
            });

            try {
                const response = await fetch('shorten.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `url=${encodeURIComponent(url)}`,
                });

                const data = await response.json();
                Swal.close();

                if (data.error) {
                    if (data.error.includes("You can only shorten the same link once every hour")) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Oops!',
                            html: `${data.error}<br><b>Shortened Link:</b> <a href="${data.short_url}" target="_blank">${data.short_url}</a>`,
                        });
                    } else {
                        Swal.fire('Error!', data.error, 'error');
                    }
                } else {
                    shortenedUrlSpan.innerHTML = `<a href="${data.short_url}" target="_blank">${data.short_url}</a>`;
                    startCountdown(60); // Start the countdown timer for 60 minutes
                    resultDiv.style.display = 'block';
                }
            } catch (error) {
                Swal.fire('Error!', 'Failed to connect to the server.', 'error');
            }
        });

        function startCountdown(minutes) {
            let remainingSeconds = minutes * 60;

            const updateTimer = () => {
                const minutes = Math.floor(remainingSeconds / 60);
                const seconds = remainingSeconds % 60;
                expirationTimeSpan.textContent = `${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
                remainingSeconds--;

                if (remainingSeconds < 0) {
                    clearInterval(timerInterval);
                    expirationTimeSpan.textContent = 'Expired!';
                }
            };

            updateTimer(); // Call immediately to set initial time
            const timerInterval = setInterval(updateTimer, 1000);
        }

        copyButton.addEventListener('click', () => {
            const urlToCopy = shortenedUrlSpan.querySelector('a').href;
            navigator.clipboard.writeText(urlToCopy).then(() => {
                Swal.fire('Copied!', 'Shortened URL has been copied to your clipboard.', 'success');
            }).catch(() => {
                Swal.fire('Error!', 'Failed to copy the URL.', 'error');
            });
        });
    </script>
</body>
</html>
