<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Salamtak - Login</title>

<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


<style>
* { font-family: 'Segoe UI', sans-serif; }

/* ===== Background ===== */
body {
    background:
        linear-gradient(rgba(0,0,0,0.45), rgba(0,0,0,0.45)),
        url('image/home.jpeg') no-repeat center center fixed;
    background-size: cover;
}

/* ===== Navbar same as home ===== */
nav.navbar {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    z-index: 1000;
    background-color: rgba(0, 0, 0, 0.3);
}

.navbar-brand {
    color: white !important;
    font-weight: 700;
    font-size: 22px;
}

.navbar .nav-link {
    color: white !important;
    font-weight: 500;
    margin-right: 20px;
}

nav .navbar-nav .nav-link:hover,
nav .navbar-brand:hover {
    transform: scale(1.08);
    color: white !important;
    transition: 0.3s;
}

/* ===== Center Login Card ===== */
.page-wrapper{
    min-height: 100vh;
    display: flex;
    justify-content: center;
    align-items: center;
    padding-top: 90px;
}

.login-card {
    background: transparent;
    padding: 30px 25px;
    border-radius: 15px;
    border: 2px solid rgba(255, 255, 255, 0.2);
    width: 400px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    text-align: center;
    color: #fff;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    backdrop-filter: blur(3px);
}

.login-card:hover {
    transform: translateY(-5px);
}

.login-card .logo img {
    width: 80px;
    margin-bottom: 15px;
}

.login-card h2 {
    font-weight: 700;
    margin-bottom: 20px;
    color: #fbfcff;
}

.form-control {
    border-radius: 10px;
    margin-bottom: 15px;
    padding: 12px;
}

.btn-login, .btn-signup {
    width: 100%;
    padding: 12px;
    border-radius: 25px;
    font-weight: 500;
    border: none;
    margin-top: 10px;
    transition: 0.3s;
}

.btn-login {
    background: #1e2a44;
    color: white;
}

.btn-login:hover {
    background: #2e3f66;
}

.btn-signup {
    background: white;
    color: #1e2a44;
    border: 2px solid #1e2a44;
}

.btn-signup:hover {
    background: #1e2a44;
    color: white;
}

.error-msg {
    color: red;
    margin-bottom: 10px;
    display: none;
}

</style>
</head>

<body>

<!-- ===== NAVBAR ===== -->
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand fw-bold" href="home.php">
            <img src="image/logof.png" width="60" class="me-2">
            Salamtak
        </a>

        <div class="collapse navbar-collapse justify-content-center">
            <ul class="navbar-nav">
                <li class="nav-item"><a class="nav-link" href="about.php">About</a></li>
                <li class="nav-item"><a class="nav-link" href="features.php">Features</a></li>
                <li class="nav-item"><a class="nav-link" href="contact.php">Contact</a></li>
                <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                <li class="nav-item"><a class="nav-link" href="report.php">Report</a></li>
            </ul>
        </div>
    </div>
</nav>

<!-- ===== LOGIN FORM ===== -->
<div class="page-wrapper">
    <div class="login-card">
        <div class="logo">
            <img src="image/logof.png">
        </div>

        <h2>Salamtak</h2>

        <div id="error-msg" class="error-msg"></div>

        <form id="login-form">
            <input type="number" id="nationalId" class="form-control" placeholder="National ID" required>
            <input type="password" id="pass" class="form-control" placeholder="Password" required>

            <button type="submit" class="btn-login">Login</button>
        </form>

        <button class="btn-signup" onclick="window.location.href='signup.php'">
            Sign Up
        </button>
    </div>
</div>

<script type="module">
import { initializeApp } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-app.js";
import { getAuth, signInWithEmailAndPassword } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-auth.js";
import { getFirestore, doc, getDoc } from "https://www.gstatic.com/firebasejs/10.12.0/firebase-firestore.js";

const firebaseConfig = {
    apiKey: "AIzaSyDY9lX8swlfKx3umnW57O5DA2Ka1Pdc0Fk",
    authDomain: "salmtak-6fffe.firebaseapp.com",
    projectId: "salmtak-6fffe",
    storageBucket: "salmtak-6fffe.firebasestorage.app",
    messagingSenderId: "1048763383483",
    appId: "1:1048763383483:web:f9a6140078484b5552f39e"
};

const app = initializeApp(firebaseConfig);
const auth = getAuth(app);
const db = getFirestore(app);

document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();

    const errorDiv = document.getElementById('error-msg');
    errorDiv.style.display = 'none';

    const nationalId = document.getElementById('nationalId').value.trim();
    const pass = document.getElementById('pass').value;

    const fakeEmail = nationalId + '@salamtak.com';

    try {
        const userCredential = await signInWithEmailAndPassword(auth, fakeEmail, pass);
        const uid = userCredential.user.uid;

        const userDoc = await getDoc(doc(db, "users", uid));

        if (userDoc.exists()) {
            const data = userDoc.data();

            sessionStorage.setItem('loggedin', 'true');
            sessionStorage.setItem('uid', uid);
            sessionStorage.setItem('nationalId', data.nationalId || '');
            sessionStorage.setItem('name', data.name || '');
            sessionStorage.setItem('address', data.address || '');
            sessionStorage.setItem('phone', data.phone || '');
        }

        window.location.href = 'home.php';

    } catch (err) {
        errorDiv.style.display = 'block';
        errorDiv.innerText = 'Login failed. Check your National ID or password.';
    }
});
</script>

</body>
</html>
