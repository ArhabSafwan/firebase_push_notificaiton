<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FCM Push Notification</title>
</head>

<body>
    <h1>Firebase Push Notification</h1>

    <form id="notificationForm">
        <label for="message">Enter Message:</label>
        <input type="text" id="message" name="message" required>
        <button type="submit">Send Notification</button>
    </form>

    <script type="module">
        import {
            initializeApp
        } from "https://www.gstatic.com/firebasejs/11.0.2/firebase-app.js";
        import {
            getMessaging,
            getToken,
            onMessage
        } from "https://www.gstatic.com/firebasejs/11.0.2/firebase-messaging.js";

        const firebaseConfig = {
            apiKey: "AIzaSyADyldddqqoIBM8Yz21Cmc0gbv2Oevv5CQ",
            authDomain: "testnotification-ebd59.firebaseapp.com",
            projectId: "testnotification-ebd59",
            storageBucket: "testnotification-ebd59.firebasestorage.app",
            messagingSenderId: "787922657275",
            appId: "1:787922657275:web:f0026373ce5e58fda7f55e",
            measurementId: "G-BM2XEVDFWX"
        };

        const app = initializeApp(firebaseConfig);
        const messaging = getMessaging(app);

        let deviceToken = null;

        // Register Service Worker and Retrieve Token
        navigator.serviceWorker.register("sw.js").then((registration) => {
            getToken(messaging, {
                vapidKey: 'BG-gQ8U9sQfPQo6FlltOyZ-7iH9JiHdGwIc9R9klGJ5niE6ETyHcgbEk2Y832OWwwQXBTkPaSvuL3Rr_39H6Sqs',
                serviceWorkerRegistration: registration
            }).then((currentToken) => {
                if (currentToken) {
                    deviceToken = currentToken;
                    console.log("Token received:", currentToken);
                } else {
                    console.warn("No registration token available.");
                }
            }).catch((err) => {
                console.error("Error retrieving token:", err);
            });
        });

        // Send Notification on Form Submission
        document.getElementById("notificationForm").addEventListener("submit", function(event) {
            event.preventDefault();
            const message = document.getElementById("message").value;

            if (deviceToken) {
                fetch("send_message.php", {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            token: deviceToken,
                            title: "New Message",
                            body: message,
                            click_action: "http://localhost/testNotification/"
                        })
                    }).then(response => response.json())
                    .then(data => console.log("Notification sent:", data))
                    .catch(error => console.error("Error sending notification:", error));
            } else {
                alert("Device token not available!");
            }
        });

        // Handle Foreground Notifications
        onMessage(messaging, (payload) => {
            console.log("Message received in foreground:", payload);
            alert(`Notification: ${payload.notification.title} - ${payload.notification.body}`);
        });
    </script>
</body>

</html>