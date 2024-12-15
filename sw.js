// sw.js
importScripts('https://www.gstatic.com/firebasejs/11.0.2/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/11.0.2/firebase-messaging-compat.js');

const firebaseConfig = {
    apiKey: "AIzaSyADyldddqqoIBM8Yz21Cmc0gbv2Oevv5CQ",
    authDomain: "testnotification-ebd59.firebaseapp.com",
    projectId: "testnotification-ebd59",
    storageBucket: "testnotification-ebd59.firebasestorage.app",
    messagingSenderId: "787922657275",
    appId: "1:787922657275:web:f0026373ce5e58fda7f55e",
    measurementId: "G-BM2XEVDFWX"
};

// receiving messages in background
const app = firebase.initializeApp(firebaseConfig);
const messaging = firebase.messaging();

// get this type of message in background
messaging.onBackgroundMessage(function (payload) {
    if (!payload.hasOwnProperty('notification')) {
        const notificationTitle = payload.data.title;
        const notificationOptions = {
            body: payload.data.body,
            icon: payload.data.icon,
            image: payload.data.image
        }
        self.registration.showNotification(notificationTitle, notificationOptions);
        self.addEventListener('notificationclick', function (event) {
            const clickedNotification = event.notification
            clickedNotification.close();
            event.waitUntil(
                clients.openWindow(payload.data.click_action)
            )
        })
    }
})
