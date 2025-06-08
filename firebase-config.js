// Import the functions you need from the SDKs you need
import { initializeApp } from "firebase/app";
import { getAnalytics } from "firebase/analytics";
// TODO: Add SDKs for Firebase products that you want to use
// https://firebase.google.com/docs/web/setup#available-libraries

// Your web app's Firebase configuration
// For Firebase JS SDK v7.20.0 and later, measurementId is optional
const firebaseConfig = {
  apiKey: "AIzaSyDyai7_-e4gK-V2mimsxRLhoc8QAxmAw2o",
  authDomain: "pos-app-df4d7.firebaseapp.com",
  projectId: "pos-app-df4d7",
  storageBucket: "pos-app-df4d7.firebasestorage.app",
  messagingSenderId: "1049394604355",
  appId: "1:1049394604355:web:e632cde232daa922c8a7f6",
  measurementId: "G-G7EHMBQZCG"
};

// Initialize Firebase
const app = initializeApp(firebaseConfig);
const analytics = getAnalytics(app);